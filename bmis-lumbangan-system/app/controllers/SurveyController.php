<?php
// app/controllers/SurveyController.php
// Controller: validation, session checks and orchestration. Calls SurveyModel for DB SQL.
// This class performs no top-level action dispatch; call its methods from your router.

// Note: This file defines the SurveyController class and its methods only.
// Any routing/dispatching for `action=` requests is handled centrally by
// `public/index.php` (the front controller). Removing file-level side-effects
// prevents this controller from intercepting AJAX requests when included.

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/SurveyModel.php';
require_once __DIR__ . '/../helpers/session_helper.php';

class SurveyController
{
    protected $db;
    protected $model;

    public function __construct($db = null)
    {
        // Accept external PDO or Database instance for easier testing
        if ($db instanceof PDO) {
            $this->db = $db;
        } elseif ($db && method_exists($db, 'getConnection')) {
            $this->db = $db->getConnection();
        } else {
            $database = new Database();
            $this->db = $database->getConnection();
        }

        $this->model = new SurveyModel($this->db);
    }

    protected function jsonResponse($arr, $status = 200){
        // ensure the HTTP status is set for the client
        http_response_code($status);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($arr);
        exit;
    }

    protected function requireLoggedIn(){
        if (!isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function wizard_personal(){
        $view = __DIR__ . '/../views/Survey/wizard_personal.php';
        include $view;
    }
    public function wizard_vitals(){
        $view = __DIR__ . '/../views/Survey/wizard_vitals.php';
        include $view;
    }
    public function wizard_family_history(){
        $view = __DIR__ . '/../views/Survey/wizard_family_history.php';
        include $view;
    }
    public function wizard_family() {
        $view = __DIR__ . '/../views/Survey/wizard_family.php';
        include $view;
    }
    public function wizard_lifestyle() {
        $view = __DIR__ . '/../views/Survey/wizard_lifestyle.php';
        include $view;
    }
    public function wizard_angina() {
        $view = __DIR__ . '/../views/Survey/wizard_angina.php';
        include $view;
    }
    public function wizard_diabetes() {
        $view = __DIR__ . '/../views/Survey/wizard_diabetes.php';
        include $view;
    }
    public function wizard_household() {
        $view = __DIR__ . '/../views/Survey/wizard_household.php';
        include $view;
    }

    public function create_assessment_action()
    {
        $this->requireLoggedIn();
        $person_id = $_SESSION['person_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$person_id) $this->jsonResponse(['success' => false, 'message' => 'Person not found in session'], 400);

        $row = $this->model->getRecentAssessmentForPerson($person_id);
        if ($row && !empty($row['id'])) {
            $cvd_id = (int)$row['id'];
        } else {
            $cvd_id = $this->model->createAssessment($person_id);
        }
        $this->jsonResponse(['success' => true, 'cvd_id' => $cvd_id]);
    }

    /**
     * Save personal info (handles saving person fields, contact mobile to users, and vitals)
     */
    public function save_personal_action()
    {
        ob_start();
        try {
            $this->requireLoggedIn();
            $person_id = $_SESSION['person_id'] ?? null;
            $user_id = $_SESSION['user_id'] ?? null;
            if (!$person_id) $this->jsonResponse(['success' => false, 'message' => 'Person not found in session'], 400);

            $cvd_id = $this->ensureAssessment($person_id, $user_id);

            // collect personal fields
            $personalFields = [
                'first_name', 'middle_name', 'last_name', 'suffix', 'sex', 'birthdate', 'is_head',
                'marital_status', 'blood_type', 'disability', 
                'religion', 'occupation', 'highest_educ_attainment'
            ];
            $save = [];
            foreach ($personalFields as $f) {
                if (isset($_POST[$f])) $save[$f] = $_POST[$f] !== '' ? $_POST[$f] : null;
            }
            if (!empty($save)) $this->model->updatePersonFields($person_id, $save);

            // contact no to users
            if (isset($_POST['contact_no'])) {
                $contact = $_POST['contact_no'] !== '' ? $_POST['contact_no'] : null;
                if ($user_id) $this->model->updateUserMobileByUserId($user_id, $contact);
                else $this->model->updateUserMobileByPersonId($person_id, $contact);
            }

            // Vitals: merge posted values with existing row so we do NOT null unspecified columns
            $vitalsFields = ['height_cm','weight_kg','bmi','waist_circumference_cm','bp_systolic','bp_diastolic','pulse','temperature_c','respiratory_rate'];
            $existing = $this->model->getVitalsByCvdId($cvd_id);

            $merged = [];
            foreach ($vitalsFields as $vf) {
                if (isset($_POST[$vf]) && $_POST[$vf] !== '') {
                    $merged[$vf] = $_POST[$vf];
                } elseif ($existing && array_key_exists($vf, $existing)) {
                    $merged[$vf] = $existing[$vf];
                } else {
                    $merged[$vf] = null;
                }
            }

            // Only insert/update if at least one non-empty value present OR existing row exists
            $anyNonNull = false;
            foreach ($merged as $v) {
                if ($v !== null && $v !== '') { $anyNonNull = true; break; }
            }

            if ($existing) {
                $this->model->updateVitalsByCvdId($cvd_id, $merged);
            } elseif ($anyNonNull) {
                $this->model->insertVitals($cvd_id, $merged);
            }

            $capt = ob_get_clean();
            // inside save_vitals_action() after $capt = ob_get_clean();
            $resp = ['success' => true, 'cvd_id' => $cvd_id, 'message' => 'Saved successfully'];
            if ($capt !== '') $resp['debug_output'] = substr($capt, 0, 20000);
            $this->jsonResponse($resp);
        } catch (\Throwable $ex) {
            $capt = ob_get_clean();
            $debug = [
                'exception' => $ex->getMessage(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine()
            ];
            if ($capt !== '') $debug['captured_output'] = substr($capt, 0, 20000);
            $this->jsonResponse(['success' => false, 'message' => 'Server error', 'debug' => $debug], 500);
        }
    }

    public function save_vitals_action()
    {
        ob_start();
        try {
            $this->requireLoggedIn();
            $person_id = $_SESSION['person_id'] ?? null;
            $user_id = $_SESSION['user_id'] ?? null;
            if (!$person_id) $this->jsonResponse(['success' => false, 'message' => 'Person not in session'], 400);

            $cvd_id = $this->ensureAssessment($person_id, $user_id);

            $fields = ['height_cm','weight_kg','bmi','waist_circumference_cm','bp_systolic','bp_diastolic','pulse','temperature_c','respiratory_rate'];

            // Fetch existing row to preserve values when not provided by POST
            $existing = $this->model->getVitalsByCvdId($cvd_id);

            $merged = [];
            foreach ($fields as $f) {
                if (isset($_POST[$f]) && $_POST[$f] !== '') {
                    $merged[$f] = $_POST[$f];
                } elseif ($existing && array_key_exists($f, $existing)) {
                    $merged[$f] = $existing[$f];
                } else {
                    $merged[$f] = null;
                }
            }

            // Only insert/update if there's some real data to store or if a row already exists
            $anyNonNull = false;
            foreach ($merged as $v) {
                if ($v !== null && $v !== '') { $anyNonNull = true; break; }
            }

            if ($existing) {
                $this->model->updateVitalsByCvdId($cvd_id, $merged);
            } elseif ($anyNonNull) {
                $this->model->insertVitals($cvd_id, $merged);
            }

            $capt = ob_get_clean();
            $resp = ['success' => true, 'cvd_id' => $cvd_id, 'message' => 'Saved successfully'];
            if ($capt !== '') $resp['debug_output'] = substr($capt, 0, 20000);
            $this->jsonResponse($resp);
        } catch (\Throwable $ex) {
            $capt = ob_get_clean();
            $debug = [
                'exception' => $ex->getMessage(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine()
            ];
            if ($capt !== '') $debug['captured_output'] = substr($capt, 0, 20000);
            $this->jsonResponse(['success' => false, 'message' => 'Server error', 'debug' => $debug], 500);
        }
    }


    /**
     * Save angina action
     */
    public function save_angina_action()
    {
        $this->requireLoggedIn();
        $person_id = $_SESSION['person_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$person_id) $this->jsonResponse(['success' => false, 'message' => 'Person not in session'], 400);

        $cvd_id = $this->ensureAssessment($person_id, $user_id);
        $qfields = ['q1_chest_discomfort','q2_pain_location_left_arm_neck_back','q3_pain_on_exertion','q4_pain_relieved_by_rest_or_nitro','q5_pain_lasting_10min_plus','q6_pain_front_of_chest_half_hour','screen_positive','needs_doctor_referral'];
        $vals = [];
        foreach ($qfields as $q) $vals[$q] = isset($_POST[$q]) ? (int)$_POST[$q] : 0;
        $this->model->upsertAnginaStroke($cvd_id, [
            ':q1' => $vals['q1_chest_discomfort'],
            ':q2' => $vals['q2_pain_location_left_arm_neck_back'],
            ':q3' => $vals['q3_pain_on_exertion'],
            ':q4' => $vals['q4_pain_relieved_by_rest_or_nitro'],
            ':q5' => $vals['q5_pain_lasting_10min_plus'],
            ':q6' => $vals['q6_pain_front_of_chest_half_hour'],
            ':sp' => $vals['screen_positive'],
            ':ndr' => $vals['needs_doctor_referral']
        ]);
        $this->jsonResponse(['success'=>true,'cvd_id'=>$cvd_id, 'message' => 'Saved Successfully']);
    }

    /**
     * Save diabetes action
     */
    public function save_diabetes_action()
    {
        $this->requireLoggedIn();
        $person_id = $_SESSION['person_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$person_id) $this->jsonResponse(['success' => false, 'message' => 'Person not in session'], 400);

        $cvd_id = $this->ensureAssessment($person_id, $user_id);

        $boolFields = ['known_diabetes','on_medications','family_history','polyuria','polydipsia','polyphagia','weight_loss','urine_ketone','urine_protein','screen_positive'];
        $vals = [];
        foreach ($boolFields as $f) $vals[":$f"] = isset($_POST[$f]) ? (int)$_POST[$f] : null;

        $vals[':rbs_mg_dl'] = $_POST['rbs_mg_dl'] ?? null;
        $vals[':fbs_mg_dl'] = $_POST['fbs_mg_dl'] ?? null;
        $vals[':hba1c_percent'] = $_POST['hba1c_percent'] ?? null;

        $this->model->upsertDiabetesScreening($cvd_id, array_merge([
            ':known_diabetes' => $vals[':known_diabetes'],
            ':on_medications' => $vals[':on_medications'],
            ':family_history' => $vals[':family_history'],
            ':polyuria' => $vals[':polyuria'],
            ':polydipsia' => $vals[':polydipsia'],
            ':polyphagia' => $vals[':polyphagia'],
            ':weight_loss' => $vals[':weight_loss'],
            ':urine_ketone' => $vals[':urine_ketone'],
            ':urine_protein' => $vals[':urine_protein'],
            ':screen_positive' => $vals[':screen_positive'],
        ], [
            ':rbs_mg_dl' => $vals[':rbs_mg_dl'],
            ':fbs_mg_dl' => $vals[':fbs_mg_dl'],
            ':hba1c_percent' => $vals[':hba1c_percent']
        ]));

        $this->jsonResponse(['success'=>true,'cvd_id'=>$cvd_id, 'message' => 'Saved Successfully']);
    }

    /**
     * Save family history
     */
    public function save_family_history_action()
    {
        $this->requireLoggedIn();
        $person_id = $_SESSION['person_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$person_id) $this->jsonResponse(['success' => false, 'message' => 'Person not in session'], 400);

        $cvd_id = $this->ensureAssessment($person_id, $user_id);
        $fields = ['hypertension','stroke','heart_attack','asthma','diabetes','cancer','kidney_disease'];
        $vals = [];
        foreach ($fields as $f) $vals[$f] = isset($_POST[$f]) ? 1 : 0;
        $recorded_at = date('Y-m-d');

        $this->model->upsertHealthFamilyHistory($person_id, $vals, $recorded_at);
        $this->jsonResponse(['success'=>true,'cvd_id'=>$cvd_id]);
    }

    /**
     * Save lifestyle
     */
    public function save_lifestyle_action()
    {
        $this->requireLoggedIn();
        $person_id = $_SESSION['person_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$person_id) $this->jsonResponse(['success' => false, 'message' => 'Person not in session'], 400);

        $cvd_id = $this->ensureAssessment($person_id, $user_id);
        $fields = ['smoking_status','smoking_comments','alcohol_use','excessive_alcohol','alcohol_notes','eats_processed_weekly','fruits_3_servings_daily','vegetables_3_servings_daily','exercise_days_per_week','exercise_minutes_per_day','exercise_intensity'];
        $vals = [];
        foreach ($fields as $f) $vals[":$f"] = $_POST[$f] ?? null;

        $this->model->upsertLifestyle($cvd_id, [
            ':smoking_status'=>$vals[':smoking_status'],
            ':smoking_comments'=>$vals[':smoking_comments'],
            ':alcohol_use'=>$vals[':alcohol_use'],
            ':excessive_alcohol'=>$vals[':excessive_alcohol'],
            ':alcohol_notes'=>$vals[':alcohol_notes'],
            ':eats_processed_weekly'=>$vals[':eats_processed_weekly'],
            ':fruits_3_servings_daily'=>$vals[':fruits_3_servings_daily'],
            ':vegetables_3_servings_daily'=>$vals[':vegetables_3_servings_daily'],
            ':exercise_days_per_week'=>$vals[':exercise_days_per_week'],
            ':exercise_minutes_per_day'=>$vals[':exercise_minutes_per_day'],
            ':exercise_intensity'=>$vals[':exercise_intensity']
        ]);

        $this->jsonResponse(['success'=>true,'cvd_id'=>$cvd_id]);
    }

    /**
     * Search persons (AJAX)
     */
    public function search_persons_action()
    {
        $this->requireLoggedIn();
        $q = trim($_GET['q'] ?? '');
        if ($q === '') $this->jsonResponse(['success' => true, 'data' => []]);
        $rows = $this->model->searchPersons($q, 30);
        $out = [];
        foreach ($rows as $r) {
            $full = trim(($r['first_name'] ?? '') . ' ' . ($r['middle_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
            $meta = $r['birthdate'] ? date('M d, Y', strtotime($r['birthdate'])) : '';
            $out[] = ['id' => (int)$r['id'], 'full_name' => $full, 'meta' => $meta];
        }
        $this->jsonResponse(['success' => true, 'data' => $out]);
    }

    public function add_family_member_action()
    {
        $this->requireLoggedIn();
        $person_id = $_SESSION['person_id'] ?? null;
        if (!$person_id) $this->jsonResponse(['success' => false, 'message' => 'Not logged in'], 401);

        $member_person_id = $_POST['member_person_id'] ?? null;
        $relationship = $_POST['relationship'] ?? null;

        if (!$member_person_id || !$relationship) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing parameters'], 400);
        }

        $ok = $this->model->syncRelationship((int)$person_id, (int)$member_person_id, $relationship);
        if ($ok) {
            $this->jsonResponse(['success' => true, 'message' => 'Family member added']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to add family member'], 500);
        }
    }

    public function remove_family_member_action()
    {
        $this->requireLoggedIn();
        $person_id = $_SESSION['person_id'] ?? null;
        if (!$person_id) $this->jsonResponse(['success' => false, 'message' => 'Not logged in'], 401);

        $member_person_id = $_POST['member_person_id'] ?? null;
        if (!$member_person_id) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing member ID'], 400);
        }

        $ok = $this->model->deletePersonRelationshipPair((int)$person_id, (int)$member_person_id);
        if ($ok) {
            $this->jsonResponse(['success' => true, 'message' => 'Family member removed']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to remove family member'], 500);
        }
    }

    public function save_family_action()
    {
        $this->requireLoggedIn();
        $person_id = $_SESSION['person_id'] ?? null;
        if (!$person_id) $this->jsonResponse(['success' => false, 'message' => 'Not logged in'], 401);

        $family_members_json = $_POST['family_members'] ?? '[]';
        $members = json_decode($family_members_json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($members)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid family members data'], 400);
            return;
        }

        $existing = $this->model->getPersonRelationships($person_id);
        $posted_ids = array_map(function ($m) {
            return (int)$m['id'];
        }, $members);

        // Removals
        foreach ($existing as $rel_id => $rel_type) {
            if (!in_array($rel_id, $posted_ids)) {
                $this->model->deletePersonRelationshipPair((int)$person_id, (int)$rel_id);
            }
        }

        // Additions/Updates
        foreach ($members as $m) {
            $rel_id = (int)$m['id'];
            $existing_rel_type = $existing[$rel_id] ?? null;
            $rel_type = $m['relationship'] ?? 'other';
            // Sync only if it's a new relationship or the type has changed
            if ($existing_rel_type === null || $existing_rel_type !== $rel_type)
            if ($rel_id === $person_id) continue;
            $this->model->syncRelationship($person_id, $rel_id, $rel_type);
        }

        $this->jsonResponse(['success' => true, 'message' => 'Family members updated']);
    }

    /**
     * Get person relationships (AJAX)
     */
    public function get_person_relationships_action()
    {
        $this->requireLoggedIn();
        $for = isset($_GET['person_id']) && is_numeric($_GET['person_id']) ? (int)$_GET['person_id'] : ($_SESSION['person_id'] ?? null);
        if (!$for) $this->jsonResponse(['success' => false, 'message' => 'person_id missing'], 400);

        $debug = isset($_GET['debug']) && in_array($_GET['debug'], ['1','true','yes'], true);
        $rowsRes = $this->model->getRelationshipsForPerson($for, $debug);
        if ($debug && is_array($rowsRes) && array_key_exists('rows', $rowsRes)) {
            $rows = $rowsRes['rows'];
            $rel_sql_info = $rowsRes['sqls'] ?? null;
        } else {
            $rows = $rowsRes;
            $rel_sql_info = null;
        }
        $data = [];
        $relatedIds = [];
        foreach ($rows as $r) {
            $full = trim(($r['first_name'] ?? '') . ' ' . ($r['middle_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
            $relatedIds[] = (int)$r['id'];
            $data[] = [
                'id' => (int)$r['id'],
                'full_name' => $full,
                'relationship_type' => $r['relationship_type'],
                'family_id' => $r['family_id'],
                'is_inverse' => (int)$r['is_inverse'],
                'sex' => $r['sex'] ?? null
            ];
        }

        $edges = [];
        $allIds = $relatedIds;
        if (!in_array($for, $allIds)) $allIds[] = $for;
        if (count($allIds) > 0) {
            $edgesRes = $this->model->getEdgesForNodeList($allIds, $debug);
            if ($debug && is_array($edgesRes) && array_key_exists('edges', $edgesRes)) {
                $edges = $edgesRes['edges'];
                $edges_sql_info = ['sql' => $edgesRes['sql'], 'params' => $edgesRes['params']];
            } else {
                $edges = $edgesRes;
                $edges_sql_info = null;
            }
            // Normalize types and ints
            foreach ($edges as &$e) {
                $e['person_id'] = (int)$e['person_id'];
                $e['related_person_id'] = (int)$e['related_person_id'];
            }
        } else {
            $edges_sql_info = null;
        }

        $resp = ['success' => true, 'data' => $data, 'edges' => $edges];
        if ($debug) {
            $resp['debug_sql'] = ['relationships' => $rel_sql_info, 'edges' => $edges_sql_info];
        }
        $this->jsonResponse($resp);
    }

    /**
     * Get next household number
     */
    public function next_household_no_action()
    {
        $this->requireLoggedIn();
        $purok_id = isset($_GET['purok_id']) && is_numeric($_GET['purok_id']) ? (int)$_GET['purok_id'] : null;
        $code = isset($_GET['code']) ? trim($_GET['code']) : null;
        if (!$purok_id && !$code) $this->jsonResponse(['success' => false, 'message' => 'purok_id or code required'], 400);
        $res = $this->model->nextHouseholdNoByPurok($purok_id, $code);
        $this->jsonResponse($res);
    }

    /**
     * Save household and family info (final step)
     */
    public function save_household_action()
    {
        $this->requireLoggedIn();
        $person_id = $_SESSION['person_id'] ?? null;
        if (!$person_id) $this->jsonResponse(['success' => false, 'message' => 'Person not in session'], 400);

        $use_head_address = isset($_POST['use_head_address']) ? (bool)(int)$_POST['use_head_address'] : true;

        // --- Step 1: Find or Create Household and Family ---
        $family_id = null;
        $household_id = null;

        // If user is part of a family and chooses to use the head's address, use existing family/household.
        // Otherwise (if they are head, not in a family, or choose 'No'), create new ones.
        if ($use_head_address) {
            $family_id = $this->model->getFamilyIdForPerson($person_id);
            if ($family_id) {
                $household_id = $this->model->getHouseholdIdForFamily($family_id);
            }
        }

        // If no household exists, create a minimal one first
        $createdNewHousehold = false;
        $createdNewFamily = false;
        if (!$household_id) {
            $household_id = $this->model->createMinimalHousehold();
            $createdNewHousehold = true;
        }

        // If no family exists, create one and link it to the person and household
        if (!$family_id) {
            $family_id = $this->model->insertFamily(['household_id' => $household_id]);
            $createdNewFamily = true;
        }

        // --- Step 2: Collect and Save Household Data ---
        $household_fields = [
            'purok_id', 'household_no', 'home_ownership', 'home_ownership_other',
            'construction_material', 'construction_material_other', 'lighting_facility',
            'lighting_facility_other', 'water_level', 'water_source', 'water_storage',
            'drinking_water_other_source', 'garbage_container', 'garbage_segregated',
            'garbage_disposal_method', 'garbage_disposal_other', 'toilet_type', 'toilet_type_other'
        ];
        $household_data = [];
        foreach ($household_fields as $f) {
            $household_data[$f] = $_POST[$f] ?? null;
        }
        // Construct the 'address' field from parts
        $address_parts = [
            $_POST['address_house_no'] ?? '',
            $_POST['address_street'] ?? '',
            $_POST['address_sitio_subdivision'] ?? '',
            $_POST['address_building'] ?? ''
        ];
        $household_data['address'] = implode(', ', array_filter($address_parts));

        $this->model->updateHousehold($household_id, $household_data);

        // --- Step 3: Collect and Save Family Data ---
        $family_fields = [
            'family_number', 'residency_status', 'length_of_residency_months', 'email'
        ];
        $family_data = [];
        foreach ($family_fields as $f) {
            $family_data[$f] = $_POST[$f] ?? null;
        }
        $family_data['survey_date'] = date('Y-m-d');

        $this->model->updateFamily($family_id, $family_data);

        // If we created a new family/household for this person (they chose a new address),
        // ensure the persons table reflects this: set their family_id, household_id and mark them head.
        if ($createdNewFamily || $createdNewHousehold) {
            try {
                $this->model->setPersonFamily($person_id, $family_id);
                $this->model->setPersonHousehold($person_id, $household_id);
                $this->model->setPersonIsHead($person_id, true);
                $this->model->setFamilyHead($family_id, $person_id);
            } catch (\Throwable $ex) {
                // Non-fatal: the survey succeeded; person update can be retried via admin tools.
            }
        }

        $this->jsonResponse([
            'success' => true,
            'message' => 'Survey completed successfully!'
        ]);
    }

    /**
     * Small helper to ensure an assessment exists; returns cvd id
     */
    protected function ensureAssessment($person_id, $user_id = null)
    {
        $row = $this->model->getRecentAssessmentForPerson($person_id);
        if ($row && !empty($row['id'])) return (int)$row['id'];
        return $this->model->createAssessment($person_id);
    }
}