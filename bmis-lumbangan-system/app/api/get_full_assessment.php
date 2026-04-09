<?php
// app/api/get_full_assessment.php
// Lightweight endpoint returning full assessment payload expected by the frontend
require_once __DIR__ . '/../config/Database.php';
// session helper is optional for auth checks; include to start session
@require_once __DIR__ . '/../helpers/session_helper.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $cvd_id = isset($_GET['cvd_id']) ? (int)$_GET['cvd_id'] : 0;
    if (!$cvd_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'cvd_id required']);
        exit;
    }

    // Initialize DB and fetch assessment first (we need assessment->person_id for family lookup)
    $db = (new Database())->getConnection();
    if (!$db) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }

    // Fetch assessment
    $stmt = $db->prepare('SELECT * FROM cvd_ncd_risk_assessments WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $cvd_id]);
    $assessment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$assessment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Assessment not found']);
        exit;
    }

    // Family / relationships list (compose from person_relationships -> persons)
    // `family_members` will hold the list of related persons. Keep `family_info` for the families table row.
    $family_members = [];
    $debugMode = !empty($_GET['debug']);
    $logFile = null;
    if ($debugMode) {
        $tmp = sys_get_temp_dir();
        $logFile = $tmp . DIRECTORY_SEPARATOR . 'get_full_assessment_debug_' . $cvd_id . '.log';
        file_put_contents($logFile, "--- get_full_assessment debug " . date('c') . "\n", FILE_APPEND);
        file_put_contents($logFile, "assessment: " . print_r($assessment, true) . "\n", FILE_APPEND);
    }

    if (!empty($assessment['person_id'])) {
        try {
            $relSql = "
                (SELECT pr.related_person_id AS id, pr.relationship_type, p.first_name, p.middle_name, p.last_name, p.birthdate, p.sex
                 FROM person_relationships pr JOIN persons p ON p.id = pr.related_person_id
                 WHERE pr.person_id = :person_id)
                UNION
                (SELECT gp.related_person_id AS id, 'grandparent' AS relationship_type, p_gp.first_name, p_gp.middle_name, p_gp.last_name, p_gp.birthdate, p_gp.sex
                 FROM person_relationships p_rel
                 JOIN person_relationships gp ON p_rel.related_person_id = gp.person_id AND gp.relationship_type = 'parent'
                 JOIN persons p_gp ON p_gp.id = gp.related_person_id
                 WHERE p_rel.person_id = :person_id AND p_rel.relationship_type = 'parent')
                UNION
                (SELECT s_rel.person_id AS id, 'sibling' AS relationship_type, p_s.first_name, p_s.middle_name, p_s.last_name, p_s.birthdate, p_s.sex
                 FROM person_relationships p_rel
                 JOIN person_relationships s_rel ON p_rel.related_person_id = s_rel.related_person_id AND s_rel.relationship_type = 'child'
                 JOIN persons p_s ON p_s.id = s_rel.person_id
                 WHERE p_rel.person_id = :person_id AND p_rel.relationship_type = 'parent' AND s_rel.person_id != :person_id)
                UNION
                (SELECT gc_rel.person_id AS id, 'grandchild' AS relationship_type, p_gc.first_name, p_gc.middle_name, p_gc.last_name, p_gc.birthdate, p_gc.sex
                 FROM person_relationships c_rel
                 JOIN person_relationships gc_rel ON c_rel.related_person_id = gc_rel.related_person_id AND gc_rel.relationship_type = 'child'
                 JOIN persons p_gc ON p_gc.id = gc_rel.person_id
                 WHERE c_rel.person_id = :person_id AND c_rel.relationship_type = 'child')
            ";
            $rstmt = $db->prepare($relSql);
            $rstmt->execute([':person_id' => (int)$assessment['person_id']]);
            $rows = $rstmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if ($debugMode && $logFile) file_put_contents($logFile, "family rows count: " . count($rows) . "\n" . print_r($rows, true) . "\n", FILE_APPEND);

            // Normalize and dedupe similar to survey_data_helper
            $family_members = [];
            $seen_ids = [];
            $relationship_priority = [
                'spouse' => 1, 'parent' => 2, 'child' => 3, 'sibling' => 4,
                'grandparent' => 5, 'grandchild' => 6, 'other' => 7
            ];

            foreach ($rows as $r) {
                $id = (int)$r['id'];
                if (in_array($id, $seen_ids)) {
                    // attempt to upgrade relationship_type if higher priority
                    foreach ($family_members as $i => $fm) {
                        if ($fm['id'] === $id) {
                            $current_priority = $relationship_priority[$r['relationship_type']] ?? 99;
                            $existing_priority = $relationship_priority[$family_members[$i]['relationship_type']] ?? 99;
                            if ($current_priority < $existing_priority) {
                                $family_members[$i]['relationship_type'] = $r['relationship_type'];
                            }
                            break;
                        }
                    }
                    continue;
                }
                $seen_ids[] = $id;
                $r['full_name'] = trim(($r['first_name'] ?? '') . ' ' . ($r['middle_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
                $family_members[] = $r;
            }

            // sort
            usort($family_members, function($a, $b) use ($relationship_priority) {
                $prio_a = $relationship_priority[$a['relationship_type']] ?? 99;
                $prio_b = $relationship_priority[$b['relationship_type']] ?? 99;
                if ($prio_a != $prio_b) return $prio_a <=> $prio_b;
                $name_a = ($a['last_name'] ?? '') . ($a['first_name'] ?? '');
                $name_b = ($b['last_name'] ?? '') . ($b['first_name'] ?? '');
                return strcasecmp($name_a, $name_b);
            });

            $family_members = $family_members;
        } catch (Exception $e) {
            $family_members = [];
            if ($debugMode && $logFile) file_put_contents($logFile, "family SQL error: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }

    // Fetch assessment
    $stmt = $db->prepare('SELECT * FROM cvd_ncd_risk_assessments WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $cvd_id]);
    $assessment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$assessment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Assessment not found']);
        exit;
    }

    // Fetch person
    $person = [];
    if (!empty($assessment['person_id'])) {
        $ps = $db->prepare('SELECT * FROM persons WHERE id = :id LIMIT 1');
        $ps->execute([':id' => (int)$assessment['person_id']]);
        $person = $ps->fetch(PDO::FETCH_ASSOC) ?: [];
        // attempt to fetch user's mobile/contact from users table
        $us = $db->prepare('SELECT mobile FROM users WHERE person_id = :pid LIMIT 1');
        $us->execute([':pid' => (int)$assessment['person_id']]);
        $urow = $us->fetch(PDO::FETCH_ASSOC);
        if ($urow && isset($urow['mobile'])) $person['contact_no'] = $urow['mobile'];
    }

    // Vitals
    $vitals = [];
    $vs = $db->prepare('SELECT * FROM vitals WHERE cvd_id = :id LIMIT 1');
    $vs->execute([':id' => $cvd_id]);
    $vitals = $vs->fetch(PDO::FETCH_ASSOC) ?: [];

    // Merge key vitals into person for admin personal view convenience
    if (!empty($person) && !empty($vitals)) {
        $mergeKeys = ['height_cm','weight_kg','waist_circumference_cm','bmi'];
        foreach ($mergeKeys as $k) {
            if (array_key_exists($k, $vitals) && ($vitals[$k] !== null)) {
                // don't override existing person field if set
                if (!isset($person[$k]) || $person[$k] === null || $person[$k] === '') {
                    $person[$k] = $vitals[$k];
                }
            }
        }
    }

    // Lifestyle
    $lifestyle = [];
    $ls = $db->prepare('SELECT * FROM lifestyle_risk WHERE cvd_id = :id LIMIT 1');
    $ls->execute([':id' => $cvd_id]);
    $lifestyle = $ls->fetch(PDO::FETCH_ASSOC) ?: [];

    // Angina / stroke screening
    $angina = [];
    $as = $db->prepare('SELECT * FROM angina_stroke_screening WHERE cvd_id = :id LIMIT 1');
    $as->execute([':id' => $cvd_id]);
    $angina = $as->fetch(PDO::FETCH_ASSOC) ?: [];

    // Diabetes
    $diabetes = [];
    $ds = $db->prepare('SELECT * FROM diabetes_screening WHERE cvd_id = :id LIMIT 1');
    $ds->execute([':id' => $cvd_id]);
    $diabetes = $ds->fetch(PDO::FETCH_ASSOC) ?: [];

    // Family history (by person)
    $family_history = [];
    if (!empty($assessment['person_id'])) {
        $fh = $db->prepare('SELECT hypertension, stroke, heart_attack, asthma, diabetes, cancer, kidney_disease FROM health_family_history WHERE person_id = :pid LIMIT 1');
        $fh->execute([':pid' => (int)$assessment['person_id']]);
        $family_history = $fh->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    // Household / family basic info (attempt to fetch from persons.family_id -> families -> households)
    $household = [];
    $family_info = [];
    if (!empty($person['family_id'])) {
        $fstmt = $db->prepare('SELECT * FROM families WHERE id = :id LIMIT 1');
        $fstmt->execute([':id' => (int)$person['family_id']]);
        $family_info = $fstmt->fetch(PDO::FETCH_ASSOC) ?: [];
        if (!empty($family_info['household_id'])) {
            $hstmt = $db->prepare('SELECT * FROM households WHERE id = :id LIMIT 1');
            $hstmt->execute([':id' => (int)$family_info['household_id']]);
            $household = $hstmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } else {
            // include family-level fields when household missing
            $household = $family_info;
        }
    }

    echo json_encode([
        'success' => true,
        'assessment' => $assessment,
        'person' => $person,
        'vitals' => $vitals,
        'lifestyle' => $lifestyle,
        'angina' => $angina,
        'diabetes' => $diabetes,
        'household' => $household,
        'family_history' => $family_history,
            // `family` is the members list (array); `family_info` contains the families table row when available
            'family' => $family_members,
            'family_info' => $family_info
    ]);
    if ($debugMode && $logFile) {
        // append note with log path so the client can find it when debugging locally
        file_put_contents($logFile, "Response includes family count: " . count($family_members) . "\n", FILE_APPEND);
    }
    exit;

} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $ex->getMessage()]);
    exit;
}

