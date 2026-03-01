<?php
// app/api/update_assessment.php
// Accepts JSON payload from the frontend and updates assessment/person/vitals/lifestyle/diabetes/family_history

// Prevent PHP notices/warnings from being emitted as HTML which would break the JSON API response.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Buffer any unexpected output so we can return a clean JSON error response.
if (!ob_get_level()) ob_start();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/AdminSurveyModel.php';
@require_once __DIR__ . '/../helpers/session_helper.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB connection failed']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    if (!is_array($payload)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
        exit;
    }

    // Debug mode: if payload includes debug=true, write helpful debug info to temp log
    $debugMode = !empty($payload['debug']);
    $logFile = null;
    if ($debugMode) {
        $tmp = sys_get_temp_dir();
        $logFile = $tmp . DIRECTORY_SEPARATOR . 'update_assessment_debug_' . (isset($payload['cvd_id']) ? (int)$payload['cvd_id'] : time()) . '.log';
        file_put_contents($logFile, "--- update_assessment debug start " . date('c') . "\n", FILE_APPEND);
        file_put_contents($logFile, "Raw payload: " . print_r($payload, true) . "\n", FILE_APPEND);
    }

    $cvd_id = isset($payload['cvd_id']) ? (int)$payload['cvd_id'] : 0;
    if (!$cvd_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'cvd_id required']);
        exit;
    }

    $model = new AdminSurveyModel($db);

    // Fetch assessment row to get person_id when needed
    $stmt = $db->prepare('SELECT * FROM cvd_ncd_risk_assessments WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $cvd_id]);
    $assessment = $stmt->fetch(PDO::FETCH_ASSOC);
    $person_id = $assessment['person_id'] ?? ($payload['person_id'] ?? null);

    // Perform all writes inside a transaction so person/users/vitals stay in sync
    $db->beginTransaction();
    try {
            // --- Personal fields (use controller's save_personal_action logic adapted for JSON payload) ---
            // Check authentication similar to SurveyController::requireLoggedIn
            if (!function_exists('isLoggedIn')) {
                // session helper should already be included earlier via @require_once
                throw new Exception('Session helper not available');
            }

            $user_id = $_SESSION['user_id'] ?? ($payload['user_id'] ?? null);

            if (!empty($payload['personal']) && $person_id) {
                $personal = $payload['personal'];

                // This list mirrors SurveyController::save_personal_action
                $personalFields = [
                    'first_name', 'middle_name', 'last_name', 'suffix', 'sex', 'birthdate', 'is_head',
                    'marital_status', 'blood_type', 'disability', 'religion', 'occupation', 'highest_educ_attainment'
                ];

                $save = [];
                foreach ($personalFields as $f) {
                    if (array_key_exists($f, $personal)) {
                        $save[$f] = ($personal[$f] === '') ? null : $personal[$f];
                    }
                }
                if (!empty($save)) {
                    $res = $model->updatePersonFields((int)$person_id, $save);
                    if ($res === false) throw new Exception('Failed updating person fields');
                }

                // contact no to users (respecting the same behavior as controller)
                if (array_key_exists('contact_no', $personal)) {
                    $contact = ($personal['contact_no'] === '') ? null : $personal['contact_no'];
                    if ($user_id) {
                        $ok_u = $model->updateUserMobileByUserId((int)$user_id, $contact);
                    } else {
                        $ok_u = $model->updateUserMobileByPersonId((int)$person_id, $contact);
                    }
                    if ($ok_u === false) throw new Exception('Failed updating user mobile');
                }
            }

    // --- Vitals ---
    // Accept vitals payload under 'vitals' or (as a fallback) under other sections like 'personal'
    $vitalsPayload = [];
    if (!empty($payload['vitals']) && is_array($payload['vitals'])) {
        $vitalsPayload = $payload['vitals'];
    }
    // fallback: some clients may include vitals keys in personal section
    if (empty($vitalsPayload) && !empty($payload['personal']) && is_array($payload['personal'])) {
        $possible = $payload['personal'];
        $fallbackKeys = ['height_cm','weight_kg','bmi','waist_circumference_cm','bp_systolic','bp_diastolic','pulse','temperature_c','respiratory_rate'];
        foreach ($fallbackKeys as $k) {
            if (array_key_exists($k, $possible)) $vitalsPayload[$k] = $possible[$k];
        }
    }

        if (!empty($vitalsPayload) && is_array($vitalsPayload)) {
        $vfields = ['height_cm','weight_kg','bmi','waist_circumference_cm','bp_systolic','bp_diastolic','pulse','temperature_c','respiratory_rate'];
        $existing = $model->getVitalsByCvdId($cvd_id);
        $merged = [];
        $any = false;
        foreach ($vfields as $vf) {
            if (array_key_exists($vf, $vitalsPayload) && $vitalsPayload[$vf] !== '') {
                $merged[$vf] = $vitalsPayload[$vf];
                $any = true;
            } elseif ($existing && array_key_exists($vf, $existing)) {
                $merged[$vf] = $existing[$vf];
            } else {
                $merged[$vf] = null;
            }
        }

            // If a vitals row exists, always update it with merged values. Otherwise insert when at least one value provided.
            if ($debugMode && $logFile) file_put_contents($logFile, "Vitals payload merged: " . print_r($merged, true) . "\nExisting vitals: " . print_r($existing, true) . "\n", FILE_APPEND);
            if ($existing) {
                $okv = $model->updateVitalsByCvdId($cvd_id, $merged);
                if ($debugMode && $logFile) file_put_contents($logFile, "updateVitalsByCvdId returned: " . var_export($okv, true) . "\n", FILE_APPEND);
                if ($okv === false) throw new Exception('Failed updating vitals');
            } elseif ($any) {
                $insId = $model->insertVitals($cvd_id, $merged);
                if ($debugMode && $logFile) file_put_contents($logFile, "insertVitals returned id: " . var_export($insId, true) . "\n", FILE_APPEND);
                if ($insId === false || $insId === 0) throw new Exception('Failed inserting vitals');
            }
        }

        // --- Lifestyle ---
        if (!empty($payload['lifestyle'])) {
            $ls = $payload['lifestyle'];
            $keys = ['smoking_status','smoking_comments','alcohol_use','excessive_alcohol','alcohol_notes','eats_processed_weekly','fruits_3_servings_daily','vegetables_3_servings_daily','exercise_days_per_week','exercise_minutes_per_day','exercise_intensity'];
            $vals = [];
            foreach ($keys as $k) {
                $vals[':'.$k] = array_key_exists($k, $ls) ? $ls[$k] : null;
            }
            if ($debugMode && $logFile) file_put_contents($logFile, "Lifestyle keys prepared: " . print_r($vals, true) . "\n", FILE_APPEND);
            $ok3 = $model->upsertLifestyle($cvd_id, $vals);
            if ($debugMode && $logFile) file_put_contents($logFile, "upsertLifestyle returned: " . var_export($ok3, true) . "\n", FILE_APPEND);
            if ($ok3 === false) {
                if ($debugMode && $logFile) file_put_contents($logFile, "PDO error info: " . print_r($db->errorInfo(), true) . "\n", FILE_APPEND);
                throw new Exception('Failed upserting lifestyle');
            }
        }
        // --- Diabetes ---
        if (!empty($payload['diabetes'])) {
            $d = $payload['diabetes'];
            $boolFields = ['known_diabetes','on_medications','family_history','polyuria','polydipsia','polyphagia','weight_loss','urine_ketone','urine_protein','screen_positive'];
            $vals = [];
            foreach ($boolFields as $f) {
                $vals[':'.$f] = isset($d[$f]) ? (int)$d[$f] : null;
            }
            $vals[':rbs_mg_dl'] = $d['rbs_mg_dl'] ?? null;
            $vals[':fbs_mg_dl'] = $d['fbs_mg_dl'] ?? null;
            $vals[':hba1c_percent'] = $d['hba1c_percent'] ?? null;
            $ok4 = $model->upsertDiabetesScreening($cvd_id, $vals);
            if ($ok4 === false) throw new Exception('Failed upserting diabetes');
        }

        // --- Family history ---
        if (!empty($payload['family_history']) && $person_id) {
            $fh = $payload['family_history'];
            $fields = ['hypertension','stroke','heart_attack','asthma','diabetes','cancer','kidney_disease'];
            $vals = [];
            foreach ($fields as $f) {
                $vals[$f] = isset($fh[$f]) && ($fh[$f] === '1' || $fh[$f] === 1 || $fh[$f] === true || $fh[$f] === 'true') ? 1 : 0;
            }
            $recorded_at = date('Y-m-d');
            $ok5 = $model->upsertHealthFamilyHistory((int)$person_id, $vals, $recorded_at);
            if ($ok5 === false) throw new Exception('Failed upserting family history');
        }

        // --- Assessment review / approval ---
        if (!empty($payload['assessment'])) {
            $a = $payload['assessment'];
            $isApproved = null;
            if (array_key_exists('is_approved', $a)) {
                $isApproved = ($a['is_approved'] === '1' || $a['is_approved'] === 1 || $a['is_approved'] === true || $a['is_approved'] === 'true') ? 1 : 0;
            }
            $reviewNotes = array_key_exists('review_notes', $a) ? ($a['review_notes'] === '' ? null : $a['review_notes']) : null;

            // update cvd_ncd_risk_assessments row
            if ($isApproved !== null || $reviewNotes !== null) {
                // set approved_by_official_id when approving; if un-approving set to NULL
                $approvedBy = ($isApproved === 1) ? ($user_id ?? null) : null;
                $sql = 'UPDATE cvd_ncd_risk_assessments SET review_notes = :review_notes, is_approved = :is_approved, approved_by_official_id = :approved_by, approved_at = ' . ($isApproved === 1 ? 'NOW()' : 'NULL') . ' WHERE id = :cvd_id';
                $stmt = $db->prepare($sql);
                $okA = $stmt->execute([':review_notes' => $reviewNotes, ':is_approved' => $isApproved, ':approved_by' => $approvedBy, ':cvd_id' => $cvd_id]);
                if ($debugMode && $logFile) file_put_contents($logFile, "Assessment update execute returned: " . var_export($okA, true) . "\n", FILE_APPEND);
                if ($okA === false) {
                    if ($debugMode && $logFile) file_put_contents($logFile, "PDO error info (assessment update): " . print_r($db->errorInfo(), true) . "\n", FILE_APPEND);
                    throw new Exception('Failed updating assessment approval');
                }
            }
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Updated']);
        exit;

    } catch (Exception $e) {
            // rollback and return error
            try { $db->rollBack(); } catch (Throwable $tt) { /* ignore rollback errors */ }
            $capt = '';
            if (ob_get_level()) $capt = ob_get_clean();
            if ($debugMode && $logFile) file_put_contents($logFile, "Captured output before exception:\n" . $capt . "\n", FILE_APPEND);
            http_response_code(500);
            $resp = ['success' => false, 'error' => $e->getMessage()];
            if ($debugMode && $capt !== '') $resp['raw_output'] = substr($capt, 0, 20000);
            echo json_encode($resp);
            exit;
        }

} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $ex->getMessage()]);
    exit;
}
