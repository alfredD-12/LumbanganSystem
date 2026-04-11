<?php
/**
 * Face Duplicate Check API
 * POST /api/check_face_duplicate.php
 * Body (JSON): { "face_embedding": [128 floats] }
 *
 * Three-zone dynamic threshold:
 *
 *  0.0 ─[HARD_CEILING 0.28]──[dynamic]──[HARD_FLOOR 0.50]─ 1.0+
 *       always duplicate     stats      always new person
 *
 * Grey zone (0.28–0.50): dynamic = mean - (Z_SCORE * stddev)
 * Only a statistical outlier below the cluster is flagged as duplicate,
 * preventing a tight fixed threshold from blocking genuinely new people.
 */
ob_start();                   // prevent PHP notices from corrupting JSON
ini_set('display_errors', 0);
error_reporting(0);

require_once dirname(__DIR__) . '/app/config/config.php';

function jsonExit(array $arr): void {
    ob_end_clean();
    echo json_encode($arr);
    exit;
}

function apiCsrfTokenFromRequest(): string {
    if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        return (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
    }

    if (!empty($_POST['csrf_token'])) {
        return (string) $_POST['csrf_token'];
    }

    return '';
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonExit(['success' => false, 'message' => 'Method not allowed']);
}

$csrfToken = apiCsrfTokenFromRequest();
if (!csrf_validate($csrfToken)) {
    http_response_code(403);
    jsonExit(['success' => false, 'message' => 'Invalid or missing CSRF token']);
}

// ── Threshold config ─────────────────────────────────────────────────────────
define('HARD_CEILING', 0.28); // always duplicate below this
define('HARD_FLOOR',   0.50); // always new person above this
define('MIN_SAMPLES',  3);    // min stored faces needed for statistics
define('Z_SCORE',      2.0);  // stddev multiplier for dynamic threshold

// ── Input ───────────────────────────────────────────────────────────────────
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['face_embedding'])) {
    jsonExit(['success' => false, 'message' => 'Missing face_embedding']);
}

$newEmbedding = $input['face_embedding'];
if (!is_array($newEmbedding) || count($newEmbedding) !== 128) {
    jsonExit(['success' => false,
              'message' => 'Invalid embedding (expected 128 floats, got ' .
                            count((array)$newEmbedding) . ')']);
}

// ── DB ──────────────────────────────────────────────────────────────────────
require_once dirname(__DIR__) . '/app/config/Database.php';

try {
    $database = new Database();
    $db       = $database->getConnection();

    $stmt = $db->prepare("
        SELECT id, face_embedding
        FROM   users
        WHERE  face_embedding IS NOT NULL
          AND  face_embedding != ''
    ");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('[FaceDupCheck] DB error: ' . $e->getMessage());
    jsonExit(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

// ── Compute distances ───────────────────────────────────────────────────────
$allDistances  = [];
$closestDist   = PHP_FLOAT_MAX;
$closestUserId = null;
$debugList     = [];

foreach ($rows as $row) {
    $stored = json_decode($row['face_embedding'], true);
    if (!is_array($stored) || count($stored) !== 128) continue;

    $dist           = euclideanDistance($newEmbedding, $stored);
    $allDistances[] = $dist;

    if (count($debugList) < 30) {
        $debugList[] = ['user_id' => (int)$row['id'], 'distance' => round($dist, 4)];
    }

    if ($dist < $closestDist) {
        $closestDist   = $dist;
        $closestUserId = (int)$row['id'];
    }
}

// ── Dynamic threshold ───────────────────────────────────────────────────────
$dynamicThreshold = HARD_FLOOR;
$method           = 'hard_floor_fallback';

if (count($allDistances) >= MIN_SAMPLES) {
    $mean   = array_sum($allDistances) / count($allDistances);
    $sq     = array_map(fn($d) => ($d - $mean) ** 2, $allDistances);
    $stddev = sqrt(array_sum($sq) / count($sq));
    $dynamic          = $mean - (Z_SCORE * $stddev);
    $dynamicThreshold = max(0.20, min(HARD_FLOOR, $dynamic));
    $method           = 'statistical';
    error_log("[FaceDupCheck] stats: n=" . count($allDistances) .
              " mean={$mean} stddev={$stddev} dyn_thresh={$dynamicThreshold}");
}

// ── Three-zone decision ────────────────────────────────────────────────────
$isDuplicate = false;
$reason      = 'no_stored_faces';

if ($closestDist !== PHP_FLOAT_MAX) {
    if ($closestDist <= HARD_CEILING) {
        $isDuplicate = true;
        $reason      = 'hard_ceiling';
    } elseif ($closestDist >= HARD_FLOOR) {
        $isDuplicate = false;
        $reason      = 'hard_floor';
    } else {
        $isDuplicate = ($closestDist < $dynamicThreshold);
        $reason      = 'dynamic_' . $method;
    }
}

error_log('[FaceDupCheck] ' . ($isDuplicate ? 'DUPLICATE' : 'NEW') .
          " user#{$closestUserId} dist={$closestDist}" .
          " thresh={$dynamicThreshold} reason={$reason}");

jsonExit([
    'success'           => true,
    'duplicate'         => $isDuplicate,
    'reason'            => $reason,
    'closest_dist'      => $closestDist < PHP_FLOAT_MAX ? round($closestDist, 4) : null,
    'closest_user'      => $closestUserId,
    'dynamic_threshold' => round($dynamicThreshold, 4),
    'hard_ceiling'      => HARD_CEILING,
    'hard_floor'        => HARD_FLOOR,
    'total_compared'    => count($allDistances),
    'distances'         => $debugList,
    'message'           => $isDuplicate
        ? 'A matching face was found in the system.'
        : 'No duplicate face found.',
]);

function euclideanDistance(array $a, array $b): float {
    $sum = 0.0;
    for ($i = 0; $i < 128; $i++) {
        $diff = (float)$a[$i] - (float)$b[$i];
        $sum += $diff * $diff;
    }
    return sqrt($sum);
}
