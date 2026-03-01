<?php
// app/controllers/AdminSurveyController.php
// AdminSurveyController: handles admin survey save flows (renamed from PersonalController logic).
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/AdminSurveyModel.php';
require_once __DIR__ . '/../helpers/session_helper.php';

class AdminSurveyController
{
    protected $db;
    protected $model;

    public function __construct($db = null)
    {
        if ($db instanceof PDO) {
            $this->db = $db;
        } elseif ($db && method_exists($db, 'getConnection')) {
            $this->db = $db->getConnection();
        } else {
            $database = new Database();
            $this->db = $database->getConnection();
        }
        $this->model = new AdminSurveyModel($this->db);
    }

    protected function jsonResponse($arr, $status = 200)
    {
        http_response_code($status);
        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($arr);
        exit;
    }

    protected function requireLoggedIn()
    {
        if (!isLoggedIn()) $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    // Save changes from JSON payload. Accepts same semantics as SurveyController::save_personal_action
    public function save_changes_action()
    {
        ob_start();
        try {
            $this->requireLoggedIn();
            $person_id = $_SESSION['person_id'] ?? null;
            $user_id = $_SESSION['user_id'] ?? null;
            if (!$person_id) $this->jsonResponse(['success' => false, 'message' => 'Person not found in session'], 400);

            // read JSON payload
            $raw = file_get_contents('php://input');
            $payload = json_decode($raw, true);
            if (!is_array($payload)) $this->jsonResponse(['success' => false, 'message' => 'Invalid JSON payload'], 400);

            $cvd_id = (int)($payload['cvd_id'] ?? 0);
            if (!$cvd_id) {
                $stmt = $this->db->prepare('SELECT id FROM cvd_ncd_risk_assessments WHERE person_id = :pid ORDER BY id DESC LIMIT 1');
                $stmt->execute([':pid' => (int)$person_id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['id'])) $cvd_id = (int)$row['id'];
            }

            // Debug file when requested
            $debugMode = !empty($payload['debug']);
            $logFile = null;
            if ($debugMode) {
                $tmp = sys_get_temp_dir();
                $logFile = $tmp . DIRECTORY_SEPARATOR . 'save_personal_debug_' . ($cvd_id ?: time()) . '.log';
                file_put_contents($logFile, "--- save_personal debug start " . date('c') . "\n", FILE_APPEND);
                file_put_contents($logFile, "Raw payload: " . print_r($payload, true) . "\n", FILE_APPEND);
            }

            // perform writes inside a transaction
            $this->db->beginTransaction();

            // Personal fields
            if (!empty($payload['personal']) && $person_id) {
                $personal = $payload['personal'];
                $personalFields = [
                    'first_name','middle_name','last_name','suffix','sex','birthdate','is_head',
                    'marital_status','blood_type','disability','religion','occupation','highest_educ_attainment'
                ];
                $save = [];
                foreach ($personalFields as $f) {
                    if (array_key_exists($f, $personal)) $save[$f] = ($personal[$f] === '') ? null : $personal[$f];
                }
                if (!empty($save)) {
                    $ok = $this->model->updatePersonFields((int)$person_id, $save);
                    if ($debugMode && $logFile) file_put_contents($logFile, "updatePersonFields result: " . var_export($ok, true) . "\n", FILE_APPEND);
                    if ($ok === false) throw new Exception('Failed updating person fields');
                }

                if (array_key_exists('contact_no', $personal)) {
                    $contact = ($personal['contact_no'] === '') ? null : $personal['contact_no'];
                    if ($user_id) $ok2 = $this->model->updateUserMobileByUserId((int)$user_id, $contact);
                    else $ok2 = $this->model->updateUserMobileByPersonId((int)$person_id, $contact);
                    if ($debugMode && $logFile) file_put_contents($logFile, "updateUserMobile result: " . var_export($ok2, true) . "\n", FILE_APPEND);
                    if ($ok2 === false) throw new Exception('Failed updating user mobile');
                }
            }

            // Vitals: accept under payload.vitals or fallback into payload.personal keys
            $vitalsPayload = [];
            if (!empty($payload['vitals']) && is_array($payload['vitals'])) $vitalsPayload = $payload['vitals'];
            if (empty($vitalsPayload) && !empty($payload['personal']) && is_array($payload['personal'])) {
                $possible = $payload['personal'];
                $fallbackKeys = ['height_cm','weight_kg','bmi','waist_circumference_cm','bp_systolic','bp_diastolic','pulse','temperature_c','respiratory_rate'];
                foreach ($fallbackKeys as $k) if (array_key_exists($k, $possible)) $vitalsPayload[$k] = $possible[$k];
            }

            if (!empty($vitalsPayload) && is_array($vitalsPayload) && $cvd_id) {
                $vfields = ['height_cm','weight_kg','bmi','waist_circumference_cm','bp_systolic','bp_diastolic','pulse','temperature_c','respiratory_rate'];
                $existing = $this->model->getVitalsByCvdId($cvd_id);
                $merged = [];
                $any = false;
                foreach ($vfields as $vf) {
                    if (array_key_exists($vf, $vitalsPayload) && $vitalsPayload[$vf] !== '') { $merged[$vf] = $vitalsPayload[$vf]; $any = true; }
                    elseif ($existing && array_key_exists($vf, $existing)) { $merged[$vf] = $existing[$vf]; }
                    else { $merged[$vf] = null; }
                }
                if ($debugMode && $logFile) file_put_contents($logFile, "Vitals merged: " . print_r($merged, true) . "\nExisting vitals: " . print_r($existing, true) . "\n", FILE_APPEND);

                if ($existing) {
                    $okv = $this->model->updateVitalsByCvdId($cvd_id, $merged);
                    if ($debugMode && $logFile) file_put_contents($logFile, "updateVitalsByCvdId returned: " . var_export($okv, true) . "\n", FILE_APPEND);
                    if ($okv === false) throw new Exception('Failed updating vitals');
                } elseif ($any) {
                    $ins = $this->model->insertVitals($cvd_id, $merged);
                    if ($debugMode && $logFile) file_put_contents($logFile, "insertVitals returned id: " . var_export($ins, true) . "\n", FILE_APPEND);
                    if ($ins === false || $ins === 0) throw new Exception('Failed inserting vitals');
                }
            }

            $this->db->commit();
            $capt = ob_get_clean();
            $resp = ['success' => true, 'cvd_id' => $cvd_id, 'message' => 'Saved successfully'];
            if ($capt !== '') $resp['debug_output'] = substr($capt, 0, 20000);
            $this->jsonResponse($resp);

        } catch (Throwable $ex) {
            try { $this->db->rollBack(); } catch (Throwable $t) { /* ignore */ }
            $capt = ob_get_clean();
            $debug = ['exception' => $ex->getMessage(), 'file' => $ex->getFile(), 'line' => $ex->getLine()];
            if ($capt !== '') $debug['captured_output'] = substr($capt, 0, 20000);
            $this->jsonResponse(['success' => false, 'message' => 'Server error', 'debug' => $debug], 500);
        }
    }
}
