<?php
// Minimal SurveyController to save survey head and sections
require_once dirname(__DIR__,1) . '/config/Database.php';
require_once dirname(__DIR__,1) . '/helpers/session_helper.php';

// Allow only logged-in users
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = (new Database())->getConnection();
$action = $_GET['action'] ?? '';
// Normalize action: allow hyphens and some legacy names (backwards compatible)
$action = str_replace('-', '_', $action);
if ($action === 'save_person') $action = 'save_personal';
$person_id = $_SESSION['person_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$person_id) {
    echo json_encode(['success' => false, 'message' => 'Person not found in session']);
    exit;
}

function jsonResponse($arr) { header('Content-Type: application/json'); echo json_encode($arr); exit; }

// Ensure a head assessment exists for this person within last 30 days or create new
function ensureAssessment(PDO $db, $person_id, $user_id) {
    // check existing within 30 days
    $stmt = $db->prepare('SELECT id FROM cvd_ncd_risk_assessments WHERE person_id = :person_id AND survey_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY survey_date DESC LIMIT 1');
    $stmt->execute([':person_id' => $person_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return $row['id'];

    // create new head record with answered_at = NOW()
    $insert = $db->prepare('INSERT INTO cvd_ncd_risk_assessments (person_id, survey_date, answered_at) VALUES (:person_id, CURDATE(), NOW())');
    $insert->execute([':person_id' => $person_id]);
    return $db->lastInsertId();
}

try {
    switch ($action) {
        case 'create_assessment':
            $cvd_id = ensureAssessment($db, $person_id, $user_id);
            jsonResponse(['success' => true, 'cvd_id' => $cvd_id]);
            break;

        case 'save_personal':
            // Update persons table with submitted personal info
            $cvd_id = ensureAssessment($db, $person_id, $user_id);
            
            // Collect all personal fields that might be submitted
            $personalFields = [
                'first_name', 'middle_name', 'last_name', 'suffix', 'sex', 'birthdate',
                'marital_status', 'family_position', 'blood_type', 'disability', 
                'religion', 'occupation', 'highest_educ_attainment'
            ];
            
            // Build dynamic update query
            $setParts = [];
            $params = [':person_id' => $person_id];
            
            foreach ($personalFields as $field) {
                if (isset($_POST[$field])) {
                    $setParts[] = "$field = :$field";
                    $params[":$field"] = $_POST[$field] ?: null;
                }
            }
            
            if (count($setParts) > 0) {
                $sql = 'UPDATE persons SET ' . implode(', ', $setParts) . ', updated_at = NOW() WHERE id = :person_id';
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
            }

            // If the personal form included contact number, save it to the users table (mobile)
            // The `persons` table schema does not include a contact_no column, so we persist
            // this value on the `users.mobile` column which is associated with the person.
            if (isset($_POST['contact_no'])) {
                $contact_no_val = $_POST['contact_no'] ?: null;
                try {
                    if ($user_id) {
                        $u = $db->prepare('UPDATE users SET mobile = :mobile, updated_at = NOW() WHERE id = :uid');
                        $u->execute([':mobile' => $contact_no_val, ':uid' => $user_id]);
                    } else {
                        $u = $db->prepare('UPDATE users SET mobile = :mobile, updated_at = NOW() WHERE person_id = :person_id');
                        $u->execute([':mobile' => $contact_no_val, ':person_id' => $person_id]);
                    }
                } catch (Exception $e) {
                    // non-fatal: log silently and continue
                }
            }

            // Additionally, if the personal form included basic biometrics (height/weight/waist),
            // persist them into the vitals table so data entered on the Personal page is not lost.
            $vitalsFields = ['height_cm','weight_kg','waist_circumference_cm'];
            $hasVitals = false;
            $vdata = [];
            foreach ($vitalsFields as $vf) {
                if (isset($_POST[$vf]) && $_POST[$vf] !== '') {
                    $hasVitals = true;
                    $vdata[$vf] = $_POST[$vf];
                } else {
                    $vdata[$vf] = null;
                }
            }

            if ($hasVitals) {
                // Upsert vitals for this assessment (cvd_id)
                $exists = $db->prepare('SELECT id FROM vitals WHERE cvd_id = :cvd_id LIMIT 1');
                $exists->execute([':cvd_id' => $cvd_id]);
                if ($exists->fetch()) {
                    $update = $db->prepare('UPDATE vitals SET height_cm=:height_cm, weight_kg=:weight_kg, waist_circumference_cm=:waist_circumference_cm WHERE cvd_id=:cvd_id');
                    $update->execute([
                        ':height_cm'=>$vdata['height_cm'], ':weight_kg'=>$vdata['weight_kg'], ':waist_circumference_cm'=>$vdata['waist_circumference_cm'], ':cvd_id'=>$cvd_id
                    ]);
                } else {
                    $ins = $db->prepare('INSERT INTO vitals (cvd_id, height_cm, weight_kg, waist_circumference_cm) VALUES (:cvd_id, :height_cm, :weight_kg, :waist_circumference_cm)');
                    $ins->execute([':cvd_id'=>$cvd_id, ':height_cm'=>$vdata['height_cm'], ':weight_kg'=>$vdata['weight_kg'], ':waist_circumference_cm'=>$vdata['waist_circumference_cm']]);
                }
            }

            jsonResponse(['success' => true, 'cvd_id' => $cvd_id, 'message' => 'Personal information saved']);
            break;

        case 'save_vitals':
            $cvd_id = ensureAssessment($db, $person_id, $user_id);
            $fields = ['height_cm','weight_kg','bmi','waist_circumference_cm','bp_systolic','bp_diastolic','pulse','temperature_c','respiratory_rate'];
            $data = [];
            foreach ($fields as $f) { $data[$f] = $_POST[$f] ?? null; }

            // Insert or update vitals (unique cvd_id)
            $exists = $db->prepare('SELECT id FROM vitals WHERE cvd_id = :cvd_id LIMIT 1');
            $exists->execute([':cvd_id' => $cvd_id]);
            if ($exists->fetch()) {
                // Update only the vitals columns. Don't modify or reference created_at here
                // (some environments may lack an updatable created_at column).
                $update = $db->prepare('UPDATE vitals SET height_cm=:height_cm, weight_kg=:weight_kg, bmi=:bmi, waist_circumference_cm=:waist_circumference_cm, bp_systolic=:bp_systolic, bp_diastolic=:bp_diastolic, pulse=:pulse, temperature_c=:temperature_c, respiratory_rate=:respiratory_rate WHERE cvd_id=:cvd_id');
                $update->execute([
                    ':height_cm'=>$data['height_cm'], ':weight_kg'=>$data['weight_kg'], ':bmi'=>$data['bmi'], ':waist_circumference_cm'=>$data['waist_circumference_cm'], ':bp_systolic'=>$data['bp_systolic'], ':bp_diastolic'=>$data['bp_diastolic'], ':pulse'=>$data['pulse'], ':temperature_c'=>$data['temperature_c'], ':respiratory_rate'=>$data['respiratory_rate'], ':cvd_id'=>$cvd_id
                ]);
            } else {
                // Current database schema for `vitals` does not include a created_at column
                // so insert only the defined columns.
                $ins = $db->prepare('INSERT INTO vitals (cvd_id, height_cm, weight_kg, bmi, waist_circumference_cm, bp_systolic, bp_diastolic, pulse, temperature_c, respiratory_rate) VALUES (:cvd_id, :height_cm, :weight_kg, :bmi, :waist_circumference_cm, :bp_systolic, :bp_diastolic, :pulse, :temperature_c, :respiratory_rate)');
                $ins->execute([':cvd_id'=>$cvd_id, ':height_cm'=>$data['height_cm'], ':weight_kg'=>$data['weight_kg'], ':bmi'=>$data['bmi'], ':waist_circumference_cm'=>$data['waist_circumference_cm'], ':bp_systolic'=>$data['bp_systolic'], ':bp_diastolic'=>$data['bp_diastolic'], ':pulse'=>$data['pulse'], ':temperature_c'=>$data['temperature_c'], ':respiratory_rate'=>$data['respiratory_rate']]);
            }
            jsonResponse(['success'=>true, 'cvd_id'=>$cvd_id]);
            break;

        case 'save_angina':
            $cvd_id = ensureAssessment($db, $person_id, $user_id);
            $qfields = ['q1_chest_discomfort','q2_pain_location_left_arm_neck_back','q3_pain_on_exertion','q4_pain_relieved_by_rest_or_nitro','q5_pain_lasting_10min_plus','q6_pain_front_of_chest_half_hour','screen_positive','needs_doctor_referral'];
            $vals = [];
            // Properly interpret posted radio values ('1' or '0'). Using isset() alone
            // treats '0' as present and would incorrectly mark it as true. Cast to int
            // from the posted value (or default 0) to get reliable 1/0.
            foreach ($qfields as $q) { $vals[$q] = isset($_POST[$q]) ? (int)$_POST[$q] : 0; }
            $exists = $db->prepare('SELECT id FROM angina_stroke_screening WHERE cvd_id = :cvd_id LIMIT 1');
            $exists->execute([':cvd_id'=>$cvd_id]);
            if ($exists->fetch()) {
                $update = $db->prepare('UPDATE angina_stroke_screening SET q1_chest_discomfort=:q1, q2_pain_location_left_arm_neck_back=:q2, q3_pain_on_exertion=:q3, q4_pain_relieved_by_rest_or_nitro=:q4, q5_pain_lasting_10min_plus=:q5, q6_pain_front_of_chest_half_hour=:q6, screen_positive=:sp, needs_doctor_referral=:ndr WHERE cvd_id=:cvd_id');
                $update->execute([':q1'=>$vals['q1_chest_discomfort'],':q2'=>$vals['q2_pain_location_left_arm_neck_back'],':q3'=>$vals['q3_pain_on_exertion'],':q4'=>$vals['q4_pain_relieved_by_rest_or_nitro'],':q5'=>$vals['q5_pain_lasting_10min_plus'],':q6'=>$vals['q6_pain_front_of_chest_half_hour'],':sp'=>$vals['screen_positive'],':ndr'=>$vals['needs_doctor_referral'],':cvd_id'=>$cvd_id]);
            } else {
                $ins = $db->prepare('INSERT INTO angina_stroke_screening (cvd_id, q1_chest_discomfort, q2_pain_location_left_arm_neck_back, q3_pain_on_exertion, q4_pain_relieved_by_rest_or_nitro, q5_pain_lasting_10min_plus, q6_pain_front_of_chest_half_hour, screen_positive, needs_doctor_referral, created_at) VALUES (:cvd_id,:q1,:q2,:q3,:q4,:q5,:q6,:sp,:ndr,NOW())');
                $ins->execute([':cvd_id'=>$cvd_id,':q1'=>$vals['q1_chest_discomfort'],':q2'=>$vals['q2_pain_location_left_arm_neck_back'],':q3'=>$vals['q3_pain_on_exertion'],':q4'=>$vals['q4_pain_relieved_by_rest_or_nitro'],':q5'=>$vals['q5_pain_lasting_10min_plus'],':q6'=>$vals['q6_pain_front_of_chest_half_hour'],':sp'=>$vals['screen_positive'],':ndr'=>$vals['needs_doctor_referral']]);
            }
            jsonResponse(['success'=>true,'cvd_id'=>$cvd_id]);
            break;

        case 'save_diabetes':
            $cvd_id = ensureAssessment($db, $person_id, $user_id);

            // Parse posted values robustly: radio inputs may post '0' or '1', so cast when present.
            $boolFields = ['known_diabetes','on_medications','family_history','polyuria','polydipsia','polyphagia','weight_loss','urine_ketone','urine_protein','screen_positive'];
            $vals = [];
            foreach ($boolFields as $f) {
                if (isset($_POST[$f])) {
                    $vals[$f] = (int)$_POST[$f];
                } else {
                    $vals[$f] = null;
                }
            }

            // Numeric fields
            $vals['rbs_mg_dl'] = $_POST['rbs_mg_dl'] ?? null;
            $vals['fbs_mg_dl'] = $_POST['fbs_mg_dl'] ?? null;
            $vals['hba1c_percent'] = $_POST['hba1c_percent'] ?? null;

            // Append a new diabetes screening record instead of updating the existing one.
            // This preserves history for the assessment. If you prefer deduplication, we can
            // change this behavior later.
            // Use INSERT ... ON DUPLICATE KEY UPDATE to avoid unique-key errors
            $ins = $db->prepare('INSERT INTO diabetes_screening (cvd_id, known_diabetes, on_medications, family_history, polyuria, polydipsia, polyphagia, weight_loss, rbs_mg_dl, fbs_mg_dl, hba1c_percent, urine_ketone, urine_protein, screen_positive, created_at) VALUES (:cvd_id,:known_diabetes,:on_medications,:family_history,:polyuria,:polydipsia,:polyphagia,:weight_loss,:rbs_mg_dl,:fbs_mg_dl,:hba1c_percent,:urine_ketone,:urine_protein,:screen_positive,NOW()) ON DUPLICATE KEY UPDATE known_diabetes=VALUES(known_diabetes), on_medications=VALUES(on_medications), family_history=VALUES(family_history), polyuria=VALUES(polyuria), polydipsia=VALUES(polydipsia), polyphagia=VALUES(polyphagia), weight_loss=VALUES(weight_loss), rbs_mg_dl=VALUES(rbs_mg_dl), fbs_mg_dl=VALUES(fbs_mg_dl), hba1c_percent=VALUES(hba1c_percent), urine_ketone=VALUES(urine_ketone), urine_protein=VALUES(urine_protein), screen_positive=VALUES(screen_positive), created_at=VALUES(created_at)');
            $ins->execute([
                ':cvd_id' => $cvd_id,
                ':known_diabetes' => $vals['known_diabetes'],
                ':on_medications' => $vals['on_medications'],
                ':family_history' => $vals['family_history'],
                ':polyuria' => $vals['polyuria'],
                ':polydipsia' => $vals['polydipsia'],
                ':polyphagia' => $vals['polyphagia'],
                ':weight_loss' => $vals['weight_loss'],
                ':rbs_mg_dl' => $vals['rbs_mg_dl'],
                ':fbs_mg_dl' => $vals['fbs_mg_dl'],
                ':hba1c_percent' => $vals['hba1c_percent'],
                ':urine_ketone' => $vals['urine_ketone'],
                ':urine_protein' => $vals['urine_protein'],
                ':screen_positive' => $vals['screen_positive']
            ]);

            jsonResponse(['success'=>true,'cvd_id'=>$cvd_id]);
            break;

        case 'save_family_history':
            $cvd_id = ensureAssessment($db, $person_id, $user_id);
            $fields = ['hypertension','stroke','heart_attack','asthma','diabetes','cancer','kidney_disease'];
            $recorded_at = date('Y-m-d');
            $vals=[]; foreach($fields as $f) { $vals[$f] = isset($_POST[$f]) ? 1 : 0; }

            // insert or update health_family_history unique (person_id, recorded_at)
            $ins = $db->prepare('INSERT INTO health_family_history (person_id, hypertension, stroke, heart_attack, asthma, diabetes, cancer, kidney_disease, recorded_at) VALUES (:person_id,:hypertension,:stroke,:heart_attack,:asthma,:diabetes,:cancer,:kidney_disease,:recorded_at) ON DUPLICATE KEY UPDATE hypertension=VALUES(hypertension), stroke=VALUES(stroke), heart_attack=VALUES(heart_attack), asthma=VALUES(asthma), diabetes=VALUES(diabetes), cancer=VALUES(cancer), kidney_disease=VALUES(kidney_disease)');
            $ins->execute([':person_id'=>$person_id,':hypertension'=>$vals['hypertension'],':stroke'=>$vals['stroke'],':heart_attack'=>$vals['heart_attack'],':asthma'=>$vals['asthma'],':diabetes'=>$vals['diabetes'],':cancer'=>$vals['cancer'],':kidney_disease'=>$vals['kidney_disease'],':recorded_at'=>$recorded_at]);
            jsonResponse(['success'=>true,'cvd_id'=>$cvd_id]);
            break;

        case 'save_family':
            $cvd_id = ensureAssessment($db, $person_id, $user_id);
            // families table expects household_id - we will attempt to find or create a household for this person
            $household_id = null;
            // try to get household via persons.family_id -> families.household_id
            $p = $db->prepare('SELECT family_id FROM persons WHERE id = :person_id');
            $p->execute([':person_id'=>$person_id]);
            $pf = $p->fetch();
            if ($pf && $pf['family_id']) {
                $f = $db->prepare('SELECT household_id FROM families WHERE id = :family_id');
                $f->execute([':family_id'=>$pf['family_id']]);
                $fr = $f->fetch();
                if ($fr) $household_id = $fr['household_id'];
            }
            // fallback: use first household
            if (!$household_id) {
                $h = $db->query('SELECT id FROM households LIMIT 1')->fetch();
                $household_id = $h['id'] ?? null;
            }

            $family_number = $_POST['family_number'] ?? null;
            $residency_status = $_POST['residency_status'] ?? null;
            $length_of_residency_months = $_POST['length_of_residency_months'] ?? null;
            $email = $_POST['email'] ?? null;
            $survey_date = date('Y-m-d');

            // Insert family record without head_person_id (DB trigger requires head_person_id be set after insert)
            try {
                $db->beginTransaction();
                $ins = $db->prepare('INSERT INTO families (household_id, family_number, residency_status, length_of_residency_months, email, survey_date, created_at) VALUES (:household_id,:family_number,:residency_status,:length_of_residency_months,:email,:survey_date,NOW())');
                $ins->execute([':household_id'=>$household_id,':family_number'=>$family_number,':residency_status'=>$residency_status,':length_of_residency_months'=>$length_of_residency_months,':email'=>$email,':survey_date'=>$survey_date]);
                $family_id = $db->lastInsertId();

                // Associate person -> family (set persons.family_id) so the subsequent UPDATE to families.head_person_id passes the trigger check
                $updPerson = $db->prepare('UPDATE persons SET family_id = :family_id, updated_at = NOW() WHERE id = :person_id');
                $updPerson->execute([':family_id'=>$family_id, ':person_id'=>$person_id]);

                // Now safely set head_person_id on the families row
                $updFamily = $db->prepare('UPDATE families SET head_person_id = :person_id WHERE id = :family_id');
                $updFamily->execute([':person_id'=>$person_id, ':family_id'=>$family_id]);

                $db->commit();
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                throw $e;
            }

            jsonResponse(['success'=>true,'cvd_id'=>$cvd_id]);
            break;

        case 'save_household':
            $cvd_id = ensureAssessment($db, $person_id, $user_id);
            // Save basic household info; if household exists update it, else create new and associate family
            $p = $db->prepare('SELECT family_id FROM persons WHERE id = :person_id');
            $p->execute([':person_id'=>$person_id]);
            $pf = $p->fetch();
            // Build combined address from the separate address fields submitted by the form
            $house_no = trim($_POST['address_house_no'] ?? '');
            $street = trim($_POST['address_street'] ?? '');
            $subdivision = trim($_POST['address_sitio_subdivision'] ?? '');
            $building = trim($_POST['address_building'] ?? '');
            $addressParts = [];
            if ($house_no !== '') $addressParts[] = $house_no;
            if ($street !== '') $addressParts[] = $street;
            $subParts = [];
            if ($subdivision !== '') $subParts[] = $subdivision;
            if ($building !== '') $subParts[] = $building;
            if (count($subParts) > 0) $addressParts[] = implode(' / ', $subParts);
            $combinedAddress = count($addressParts) > 0 ? implode(', ', $addressParts) : null;

            // Normalize incoming purok selection: the view now posts purok_id (numeric). If legacy 'purok_sitio' is present, try to map it.
            $purok_id = null;
            if (isset($_POST['purok_id']) && $_POST['purok_id'] !== '') {
                $purok_id = (int)$_POST['purok_id'];
            } elseif (isset($_POST['purok_sitio']) && $_POST['purok_sitio'] !== '') {
                // legacy: try to map code to id by looking up puroks.name LIKE value or code...
                $code = trim($_POST['purok_sitio']);
                // Attempt to find by name or by containing code in parentheses (best-effort)
                $stmtp = $db->prepare('SELECT id FROM puroks WHERE name = :name LIMIT 1');
                $stmtp->execute([':name' => $code]);
                $r = $stmtp->fetch(PDO::FETCH_ASSOC);
                if ($r) $purok_id = (int)$r['id'];
            }

            $household_fields = ['purok_id','household_no','address','latitude','longitude','home_ownership','home_ownership_other','construction_material','construction_material_other','lighting_facility','lighting_facility_other','water_level','water_source','water_storage','drinking_water_other_source','garbage_container','garbage_segregated','garbage_disposal_method','garbage_disposal_other','toilet_type','toilet_type_other'];
            $vals = [];
            foreach ($household_fields as $hf) {
                // address and purok_id are assembled from separate fields
                if ($hf === 'address') {
                    $vals['address'] = $combinedAddress;
                    continue;
                }
                if ($hf === 'purok_id') {
                    $vals['purok_id'] = $purok_id;
                    continue;
                }
                $vals[$hf] = $_POST[$hf] ?? null;
            }

            if ($pf && $pf['family_id']) {
                // find household id from families
                $f = $db->prepare('SELECT household_id FROM families WHERE id = :family_id');
                $f->execute([':family_id'=>$pf['family_id']]);
                $fr = $f->fetch();
                if ($fr && $fr['household_id']) {
                    $update_sql = 'UPDATE households SET address=:address, household_no=:household_no, purok_id=:purok_id, latitude=:latitude, longitude=:longitude, home_ownership=:home_ownership, home_ownership_other=:home_ownership_other, construction_material=:construction_material, construction_material_other=:construction_material_other, lighting_facility=:lighting_facility, lighting_facility_other=:lighting_facility_other, water_level=:water_level, water_source=:water_source, water_storage=:water_storage, drinking_water_other_source=:drinking_water_other_source, garbage_container=:garbage_container, garbage_segregated=:garbage_segregated, garbage_disposal_method=:garbage_disposal_method, garbage_disposal_other=:garbage_disposal_other, toilet_type=:toilet_type, toilet_type_other=:toilet_type_other, updated_at=NOW() WHERE id=:hid';
                    $stmt = $db->prepare($update_sql);
                    $stmt->execute(array_merge([
                        ':address'=>$vals['address'], ':household_no'=>$vals['household_no'], ':purok_id'=>$vals['purok_id'], ':latitude'=>$vals['latitude'], ':longitude'=>$vals['longitude'], ':home_ownership'=>$vals['home_ownership'], ':home_ownership_other'=>$vals['home_ownership_other'], ':construction_material'=>$vals['construction_material'], ':construction_material_other'=>$vals['construction_material_other'], ':lighting_facility'=>$vals['lighting_facility'], ':lighting_facility_other'=>$vals['lighting_facility_other'], ':water_level'=>$vals['water_level'], ':water_source'=>$vals['water_source'], ':water_storage'=>$vals['water_storage'], ':drinking_water_other_source'=>$vals['drinking_water_other_source'], ':garbage_container'=>$vals['garbage_container'], ':garbage_segregated'=>$vals['garbage_segregated'], ':garbage_disposal_method'=>$vals['garbage_disposal_method'], ':garbage_disposal_other'=>$vals['garbage_disposal_other'], ':toilet_type'=>$vals['toilet_type'], ':toilet_type_other'=>$vals['toilet_type_other']
                    ], [':hid'=>$fr['household_id']]));
                    jsonResponse(['success'=>true,'cvd_id'=>$cvd_id]);
                }
            }

            // otherwise create a new household
            $ins_sql = 'INSERT INTO households (purok_id, household_no, address, latitude, longitude, home_ownership, home_ownership_other, construction_material, construction_material_other, lighting_facility, lighting_facility_other, water_level, water_source, water_storage, drinking_water_other_source, garbage_container, garbage_segregated, garbage_disposal_method, garbage_disposal_other, toilet_type, toilet_type_other, created_at) VALUES (:purok_id,:household_no,:address,:latitude,:longitude,:home_ownership,:home_ownership_other,:construction_material,:construction_material_other,:lighting_facility,:lighting_facility_other,:water_level,:water_source,:water_storage,:drinking_water_other_source,:garbage_container,:garbage_segregated,:garbage_disposal_method,:garbage_disposal_other,:toilet_type,:toilet_type_other,NOW())';
            $stmt = $db->prepare($ins_sql);
            $stmt->execute([
                ':purok_id'=>$vals['purok_id'], ':household_no'=>$vals['household_no'], ':address'=>$vals['address'], ':latitude'=>$vals['latitude'], ':longitude'=>$vals['longitude'], ':home_ownership'=>$vals['home_ownership'], ':home_ownership_other'=>$vals['home_ownership_other'], ':construction_material'=>$vals['construction_material'], ':construction_material_other'=>$vals['construction_material_other'], ':lighting_facility'=>$vals['lighting_facility'], ':lighting_facility_other'=>$vals['lighting_facility_other'], ':water_level'=>$vals['water_level'], ':water_source'=>$vals['water_source'], ':water_storage'=>$vals['water_storage'], ':drinking_water_other_source'=>$vals['drinking_water_other_source'], ':garbage_container'=>$vals['garbage_container'], ':garbage_segregated'=>$vals['garbage_segregated'], ':garbage_disposal_method'=>$vals['garbage_disposal_method'], ':garbage_disposal_other'=>$vals['garbage_disposal_other'], ':toilet_type'=>$vals['toilet_type'], ':toilet_type_other'=>$vals['toilet_type_other']
            ]);
            $new_hid = $db->lastInsertId();
            // Create a family record linking the person to this household. Follow trigger rules: insert without head_person_id,
            // then set persons.family_id and update families.head_person_id.
            try {
                $db->beginTransaction();
                $insf = $db->prepare('INSERT INTO families (household_id, family_number, created_at) VALUES (:hid, NULL, NOW())');
                $insf->execute([':hid'=>$new_hid]);
                $new_fid = $db->lastInsertId();

                // Associate person with this new family
                $updPerson = $db->prepare('UPDATE persons SET family_id = :family_id, updated_at = NOW() WHERE id = :person_id');
                $updPerson->execute([':family_id'=>$new_fid, ':person_id'=>$person_id]);

                // Now set the family's head_person_id
                $updFamily = $db->prepare('UPDATE families SET head_person_id = :person_id WHERE id = :family_id');
                $updFamily->execute([':person_id'=>$person_id, ':family_id'=>$new_fid]);

                $db->commit();
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                throw $e;
            }

            jsonResponse(['success'=>true,'cvd_id'=>$cvd_id]);
            break;

        case 'save_lifestyle':
            $cvd_id = ensureAssessment($db, $person_id, $user_id);
            $fields = ['smoking_status','smoking_comments','alcohol_use','excessive_alcohol','alcohol_notes','eats_processed_weekly','fruits_3_servings_daily','vegetables_3_servings_daily','exercise_days_per_week','exercise_minutes_per_day','exercise_intensity'];
            $vals = [];
            foreach ($fields as $f) $vals[$f] = $_POST[$f] ?? null;
            $exists = $db->prepare('SELECT id FROM lifestyle_risk WHERE cvd_id = :cvd_id LIMIT 1');
            $exists->execute([':cvd_id'=>$cvd_id]);
            if ($exists->fetch()) {
                $update = $db->prepare('UPDATE lifestyle_risk SET smoking_status=:smoking_status, smoking_comments=:smoking_comments, alcohol_use=:alcohol_use, excessive_alcohol=:excessive_alcohol, alcohol_notes=:alcohol_notes, eats_processed_weekly=:eats_processed_weekly, fruits_3_servings_daily=:fruits_3_servings_daily, vegetables_3_servings_daily=:vegetables_3_servings_daily, exercise_days_per_week=:exercise_days_per_week, exercise_minutes_per_day=:exercise_minutes_per_day, exercise_intensity=:exercise_intensity WHERE cvd_id=:cvd_id');
                $update->execute([
                    ':smoking_status'=>$vals['smoking_status'],':smoking_comments'=>$vals['smoking_comments'],':alcohol_use'=>$vals['alcohol_use'],':excessive_alcohol'=>$vals['excessive_alcohol'],':alcohol_notes'=>$vals['alcohol_notes'],':eats_processed_weekly'=>$vals['eats_processed_weekly'],':fruits_3_servings_daily'=>$vals['fruits_3_servings_daily'],':vegetables_3_servings_daily'=>$vals['vegetables_3_servings_daily'],':exercise_days_per_week'=>$vals['exercise_days_per_week'],':exercise_minutes_per_day'=>$vals['exercise_minutes_per_day'],':exercise_intensity'=>$vals['exercise_intensity'],':cvd_id'=>$cvd_id
                ]);
            } else {
                $ins = $db->prepare('INSERT INTO lifestyle_risk (cvd_id, smoking_status, smoking_comments, alcohol_use, excessive_alcohol, alcohol_notes, eats_processed_weekly, fruits_3_servings_daily, vegetables_3_servings_daily, exercise_days_per_week, exercise_minutes_per_day, exercise_intensity) VALUES (:cvd_id,:smoking_status,:smoking_comments,:alcohol_use,:excessive_alcohol,:alcohol_notes,:eats_processed_weekly,:fruits_3_servings_daily,:vegetables_3_servings_daily,:exercise_days_per_week,:exercise_minutes_per_day,:exercise_intensity)');
                $ins->execute([':cvd_id'=>$cvd_id,':smoking_status'=>$vals['smoking_status'],':smoking_comments'=>$vals['smoking_comments'],':alcohol_use'=>$vals['alcohol_use'],':excessive_alcohol'=>$vals['excessive_alcohol'],':alcohol_notes'=>$vals['alcohol_notes'],':eats_processed_weekly'=>$vals['eats_processed_weekly'],':fruits_3_servings_daily'=>$vals['fruits_3_servings_daily'],':vegetables_3_servings_daily'=>$vals['vegetables_3_servings_daily'],':exercise_days_per_week'=>$vals['exercise_days_per_week'],':exercise_minutes_per_day'=>$vals['exercise_minutes_per_day'],':exercise_intensity'=>$vals['exercise_intensity']]);
            }
            jsonResponse(['success'=>true,'cvd_id'=>$cvd_id]);
            break;

        default:
            jsonResponse(['success'=>false,'message'=>'Unknown action']);
    }
} catch (PDOException $ex) {
    jsonResponse(['success'=>false,'message'=>'Database error: '.$ex->getMessage()]);
}
