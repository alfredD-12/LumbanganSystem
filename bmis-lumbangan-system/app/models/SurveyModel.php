<?php
// app/models/SurveyModel.php
// All database SQL lives here. Uses a PDO connection supplied at construction.

class SurveyModel
{
    /** @var PDO */
    protected $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /* --- Assessment helpers --- */

    public function getRecentAssessmentForPerson(int $person_id)
    {
        $stmt = $this->db->prepare('
            SELECT id FROM cvd_ncd_risk_assessments 
            WHERE person_id = :person_id 
              AND YEAR(answered_at) = YEAR(CURDATE())
              AND MONTH(answered_at) = MONTH(CURDATE())
            ORDER BY answered_at DESC LIMIT 1');
        $stmt->bindValue(':person_id', $person_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createAssessment(int $person_id)
    {
        $stmt = $this->db->prepare('INSERT INTO cvd_ncd_risk_assessments (person_id, survey_date, answered_at) VALUES (:person_id, CURDATE(), NOW())');
        $stmt->execute([':person_id' => $person_id]);
        return (int)$this->db->lastInsertId();
    }

    /* --- Households --- */

    public function findAnyHousehold()
    {
        return $this->db->query('SELECT id FROM households LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    }

    public function createMinimalHousehold()
    {
        $ins = $this->db->prepare('INSERT INTO households (purok_id, household_no, address, created_at) VALUES (NULL, NULL, NULL, NOW())');
        $ins->execute();
        return (int)$this->db->lastInsertId();
    }

    public function updateHousehold(int $household_id, array $data)
    {
        if (empty($data)) return false;
        $setParts = [];
        $params = [':household_id' => $household_id];
        foreach ($data as $k => $v) {
            // Protect column names
            $setParts[] = "`$k` = :$k";
            $params[":$k"] = $v;
        }
        $sql = 'UPDATE households SET ' . implode(', ', $setParts) . ' WHERE id = :household_id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /* --- Persons / Users updates --- */

    public function updatePersonFields(int $person_id, array $fields)
    {
        if (empty($fields)) return false;
        $setParts = [];
        $params = [':person_id' => $person_id];
        // Define fields that should NOT be updated by this generic function
        $protectedFields = ['family_id', 'household_id'];

        foreach ($fields as $k => $v) {
            // Skip protected fields to prevent accidental updates
            if (in_array($k, $protectedFields)) continue;

            // Protect column names - assume caller passed valid column names
            $setParts[] = "`$k` = :$k";
            $params[":$k"] = ($v === '') ? null : $v;
        }
        // removed updated_at to avoid SQL error when column is missing
        $sql = 'UPDATE persons SET ' . implode(', ', $setParts) . ' WHERE id = :person_id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateUserMobileByUserId(int $user_id, $mobile)
    {
        // removed updated_at to avoid SQL error when column is missing
        $stmt = $this->db->prepare('UPDATE users SET mobile = :mobile WHERE id = :uid');
        return $stmt->execute([':mobile' => $mobile, ':uid' => $user_id]);
    }
    public function updateUserMobileByPersonId(int $person_id, $mobile)
    {
        // removed updated_at to avoid SQL error when column is missing
        $stmt = $this->db->prepare('UPDATE users SET mobile = :mobile WHERE person_id = :person_id');
        return $stmt->execute([':mobile' => $mobile, ':person_id' => $person_id]);
    }

    /* --- Vitals --- */

    /**
     * Return the full vitals row for a given cvd_id (or false if none).
     * Changed to return the full row so callers can merge values without unintentionally nulling columns.
     */
    public function getVitalsByCvdId(int $cvd_id)
    {
        $stmt = $this->db->prepare('SELECT * FROM vitals WHERE cvd_id = :cvd_id LIMIT 1');
        $stmt->execute([':cvd_id' => $cvd_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertVitals(int $cvd_id, array $data)
    {
        $ins = $this->db->prepare('INSERT INTO vitals (cvd_id, height_cm, weight_kg, bmi, waist_circumference_cm, bp_systolic, bp_diastolic, pulse, temperature_c, respiratory_rate) VALUES (:cvd_id, :height_cm, :weight_kg, :bmi, :waist_circumference_cm, :bp_systolic, :bp_diastolic, :pulse, :temperature_c, :respiratory_rate)');
        $params = [
            ':cvd_id' => $cvd_id,
            ':height_cm' => $data['height_cm'] ?? null,
            ':weight_kg' => $data['weight_kg'] ?? null,
            ':bmi' => $data['bmi'] ?? null,
            ':waist_circumference_cm' => $data['waist_circumference_cm'] ?? null,
            ':bp_systolic' => $data['bp_systolic'] ?? null,
            ':bp_diastolic' => $data['bp_diastolic'] ?? null,
            ':pulse' => $data['pulse'] ?? null,
            ':temperature_c' => $data['temperature_c'] ?? null,
            ':respiratory_rate' => $data['respiratory_rate'] ?? null,
        ];
        $ins->execute($params);
        return $this->db->lastInsertId();
    }

    public function updateVitalsByCvdId(int $cvd_id, array $data)
    {
        // removed updated_at assignment to avoid unknown column errors
        $sql = 'UPDATE vitals SET height_cm = :height_cm, weight_kg = :weight_kg, bmi = :bmi, waist_circumference_cm = :waist_circumference_cm, bp_systolic = :bp_systolic, bp_diastolic = :bp_diastolic, pulse = :pulse, temperature_c = :temperature_c, respiratory_rate = :respiratory_rate WHERE cvd_id = :cvd_id';
        $stmt = $this->db->prepare($sql);
        $params = [
            ':height_cm' => $data['height_cm'] ?? null,
            ':weight_kg' => $data['weight_kg'] ?? null,
            ':bmi' => $data['bmi'] ?? null,
            ':waist_circumference_cm' => $data['waist_circumference_cm'] ?? null,
            ':bp_systolic' => $data['bp_systolic'] ?? null,
            ':bp_diastolic' => $data['bp_diastolic'] ?? null,
            ':pulse' => $data['pulse'] ?? null,
            ':temperature_c' => $data['temperature_c'] ?? null,
            ':respiratory_rate' => $data['respiratory_rate'] ?? null,
            ':cvd_id' => $cvd_id,
        ];
        return $stmt->execute($params);
    }

    /* --- Angina / Stroke --- */

    public function upsertAnginaStroke(int $cvd_id, array $vals)
    {
        $exists = $this->db->prepare('SELECT id FROM angina_stroke_screening WHERE cvd_id = :cvd_id LIMIT 1');
        $exists->execute([':cvd_id' => $cvd_id]);
        if ($exists->fetch()) {
            $sql = 'UPDATE angina_stroke_screening SET q1_chest_discomfort=:q1, q2_pain_location_left_arm_neck_back=:q2, q3_pain_on_exertion=:q3, q4_pain_relieved_by_rest_or_nitro=:q4, q5_pain_lasting_10min_plus=:q5, q6_pain_front_of_chest_half_hour=:q6, screen_positive=:sp, needs_doctor_referral=:ndr WHERE cvd_id=:cvd_id';
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(array_merge($vals, [':cvd_id' => $cvd_id]));
        } else {
            $ins = $this->db->prepare('INSERT INTO angina_stroke_screening (cvd_id, q1_chest_discomfort, q2_pain_location_left_arm_neck_back, q3_pain_on_exertion, q4_pain_relieved_by_rest_or_nitro, q5_pain_lasting_10min_plus, q6_pain_front_of_chest_half_hour, screen_positive, needs_doctor_referral, created_at) VALUES (:cvd_id,:q1,:q2,:q3,:q4,:q5,:q6,:sp,:ndr,NOW())');
            return $ins->execute(array_merge($vals, [':cvd_id' => $cvd_id]));
        }
    }

    /* --- Diabetes --- */

    public function upsertDiabetesScreening(int $cvd_id, array $vals)
    {
        $sql = 'INSERT INTO diabetes_screening (cvd_id, known_diabetes, on_medications, family_history, polyuria, polydipsia, polyphagia, weight_loss, rbs_mg_dl, fbs_mg_dl, hba1c_percent, urine_ketone, urine_protein, screen_positive, created_at)
            VALUES (:cvd_id,:known_diabetes,:on_medications,:family_history,:polyuria,:polydipsia,:polyphagia,:weight_loss,:rbs_mg_dl,:fbs_mg_dl,:hba1c_percent,:urine_ketone,:urine_protein,:screen_positive,NOW())
            ON DUPLICATE KEY UPDATE known_diabetes=VALUES(known_diabetes), on_medications=VALUES(on_medications), family_history=VALUES(family_history), polyuria=VALUES(polyuria), polydipsia=VALUES(polydipsia), polyphagia=VALUES(polyphagia), weight_loss=VALUES(weight_loss), rbs_mg_dl=VALUES(rbs_mg_dl), fbs_mg_dl=VALUES(fbs_mg_dl), hba1c_percent=VALUES(hba1c_percent), urine_ketone=VALUES(urine_ketone), urine_protein=VALUES(urine_protein), screen_positive=VALUES(screen_positive), created_at=VALUES(created_at)';
        $stmt = $this->db->prepare($sql);
        $params = array_merge([':cvd_id' => $cvd_id], $vals);
        return $stmt->execute($params);
    }

    /* --- Family history --- */

public function upsertHealthFamilyHistory(int $person_id, array $vals, string $recorded_at)
{
    // Check if a record already exists for this person
    $check = $this->db->prepare('SELECT id FROM health_family_history WHERE person_id = :person_id LIMIT 1');
    $check->execute([':person_id' => $person_id]);
    $existingId = $check->fetchColumn();

    if ($existingId) {
        // Update existing row
        $sql = 'UPDATE health_family_history
                SET hypertension = :hypertension,
                    stroke = :stroke,
                    heart_attack = :heart_attack,
                    asthma = :asthma,
                    diabetes = :diabetes,
                    cancer = :cancer,
                    kidney_disease = :kidney_disease,
                    recorded_at = :recorded_at
                WHERE person_id = :person_id';
        $stmt = $this->db->prepare($sql);
        $params = [
            ':hypertension'   => $vals['hypertension'],
            ':stroke'         => $vals['stroke'],
            ':heart_attack'   => $vals['heart_attack'],
            ':asthma'         => $vals['asthma'],
            ':diabetes'       => $vals['diabetes'],
            ':cancer'         => $vals['cancer'],
            ':kidney_disease' => $vals['kidney_disease'],
            ':recorded_at'    => $recorded_at,
            ':person_id'      => $person_id
        ];
        return $stmt->execute($params);
    } else {
        // Insert new row
        $sql = 'INSERT INTO health_family_history (person_id, hypertension, stroke, heart_attack, asthma, diabetes, cancer, kidney_disease, recorded_at)
                VALUES (:person_id, :hypertension, :stroke, :heart_attack, :asthma, :diabetes, :cancer, :kidney_disease, :recorded_at)';
        $stmt = $this->db->prepare($sql);
        $params = [
            ':person_id'      => $person_id,
            ':hypertension'   => $vals['hypertension'],
            ':stroke'         => $vals['stroke'],
            ':heart_attack'   => $vals['heart_attack'],
            ':asthma'         => $vals['asthma'],
            ':diabetes'       => $vals['diabetes'],
            ':cancer'         => $vals['cancer'],
            ':kidney_disease' => $vals['kidney_disease'],
            ':recorded_at'    => $recorded_at
        ];
        return $stmt->execute($params);
    }
} 

    /* --- Lifestyle --- */

public function upsertLifestyle(int $cvd_id, array $vals)
{
    $sql = 'INSERT INTO lifestyle_risk (cvd_id, smoking_status, smoking_comments, alcohol_use, excessive_alcohol, alcohol_notes, eats_processed_weekly, fruits_3_servings_daily, vegetables_3_servings_daily, exercise_days_per_week, exercise_minutes_per_day, exercise_intensity)
            VALUES (:cvd_id, :smoking_status, :smoking_comments, :alcohol_use, :excessive_alcohol, :alcohol_notes, :eats_processed_weekly, :fruits_3_servings_daily, :vegetables_3_servings_daily, :exercise_days_per_week, :exercise_minutes_per_day, :exercise_intensity)
            ON DUPLICATE KEY UPDATE smoking_status=VALUES(smoking_status), smoking_comments=VALUES(smoking_comments), alcohol_use=VALUES(alcohol_use), excessive_alcohol=VALUES(excessive_alcohol), alcohol_notes=VALUES(alcohol_notes), eats_processed_weekly=VALUES(eats_processed_weekly), fruits_3_servings_daily=VALUES(fruits_3_servings_daily), vegetables_3_servings_daily=VALUES(vegetables_3_servings_daily), exercise_days_per_week=VALUES(exercise_days_per_week), exercise_minutes_per_day=VALUES(exercise_minutes_per_day), exercise_intensity=VALUES(exercise_intensity)';
    $stmt = $this->db->prepare($sql);
    return $stmt->execute(array_merge([':cvd_id' => $cvd_id], $vals));
}

    /* --- Family / relationships sync --- */

    public function getFamilyIdForPerson(int $person_id)
    {
        $stmt = $this->db->prepare('SELECT family_id FROM persons WHERE id = :pid');
        $stmt->execute([':pid' => $person_id]);
        return $stmt->fetchColumn();
    }

    public function getHouseholdIdForFamily(int $family_id)
    {
        $stmt = $this->db->prepare('SELECT household_id FROM families WHERE id = :fid');
        $stmt->execute([':fid' => $family_id]);
        return $stmt->fetchColumn();
    }

    public function updateFamily(int $family_id, array $data)
    {
        // removed updated_at to avoid SQL error when column is missing
        $sql = 'UPDATE families SET family_number=:family_number, residency_status=:residency_status, length_of_residency_months=:length_of_residency_months, email=:email, survey_date=:survey_date WHERE id=:id';
        $stmt = $this->db->prepare($sql);
        $params = [
            ':family_number' => $data['family_number'],
            ':residency_status' => $data['residency_status'],
            ':length_of_residency_months' => $data['length_of_residency_months'],
            ':email' => $data['email'],
            ':survey_date' => $data['survey_date'],
            ':id' => $family_id
        ];
        return $stmt->execute($params);
    }

    public function insertFamily(array $data)
    {
        $ins = $this->db->prepare('INSERT INTO families (household_id, family_number, residency_status, length_of_residency_months, email, survey_date, created_at) VALUES (:household_id, :family_number, :residency_status, :length_of_residency_months, :email, :survey_date, NOW())');
        $ins->execute([
            ':household_id' => $data['household_id'],
            ':family_number' => $data['family_number'],
            ':residency_status' => $data['residency_status'],
            ':length_of_residency_months' => $data['length_of_residency_months'],
            ':email' => $data['email'],
            ':survey_date' => $data['survey_date']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function setPersonFamily(int $person_id, int $family_id)
    {
        // removed updated_at to avoid SQL error when column is missing
        $stmt = $this->db->prepare('UPDATE persons SET family_id = :family_id WHERE id = :person_id');
        return $stmt->execute([':family_id' => $family_id, ':person_id' => $person_id]);
    }

    /**
     * Set a person's household_id column.
     */
    public function setPersonHousehold(int $person_id, ?int $household_id)
    {
        $stmt = $this->db->prepare('UPDATE persons SET household_id = :household_id WHERE id = :person_id');
        return $stmt->execute([':household_id' => $household_id, ':person_id' => $person_id]);
    }

    public function setPersonIsHead(int $person_id, bool $is_head)
    {
        $stmt = $this->db->prepare('UPDATE persons SET is_head = :is_head WHERE id = :person_id');
        return $stmt->execute([':is_head' => $is_head ? 1 : 0, ':person_id' => $person_id]);
    }

    public function setFamilyHead(int $family_id, int $person_id)
    {
        $stmt = $this->db->prepare('UPDATE families SET head_person_id = :person_id WHERE id = :family_id');
        return $stmt->execute([':person_id' => $person_id, ':family_id' => $family_id]);
    }

    protected function getInverseRelationship(string $relationship): ?string
    {
        $map = [
            'spouse'  => 'spouse',
            'child'   => 'parent',
            'parent'  => 'child',
            'other'   => 'other',
            'sibling' => 'sibling' // Keep for inference logic, though not a direct type
        ];
        return $map[strtolower($relationship)] ?? null;
    }

    public function syncRelationship(int $person_id, int $related_id, string $relationship)
    {
        $family_id = $this->getFamilyIdForPerson($person_id);
        if (!$family_id) {
            $family_id = $this->getFamilyIdForPerson($related_id);
        }
        $family_id = $family_id ? (int)$family_id : null;

        $this->upsertPersonRelationship($person_id, $related_id, $relationship, $family_id, false);

        $inverse_rel = $this->getInverseRelationship($relationship);
        if ($inverse_rel) {
            $this->upsertPersonRelationship($related_id, $person_id, $inverse_rel, $family_id, true);
        }

        // If a family_id was determined, ensure the persons table records it as well
        if ($family_id) {
            // assign family_id on both persons (if missing or different)
            try {
                $this->setPersonFamily($person_id, $family_id);
                $this->setPersonFamily($related_id, $family_id);

                // If the family maps to a household, propagate household_id to persons
                $hid = $this->getHouseholdIdForFamily($family_id);
                if ($hid) {
                    $this->setPersonHousehold($person_id, (int)$hid);
                    $this->setPersonHousehold($related_id, (int)$hid);
                }
            } catch (\Throwable $ex) {
                // Non-fatal: relationship rows are created; persons table may be updated on a later step
            }
        }

        // Co-parenting logic: If adding a 'child', find the person's spouse and make them a parent too.
        if (strtolower($relationship) === 'child') {
            $spouse_stmt = $this->db->prepare("SELECT related_person_id FROM person_relationships WHERE person_id = :person_id AND relationship_type = 'spouse' LIMIT 1");
            $spouse_stmt->execute([':person_id' => $person_id]);
            $spouse_id = $spouse_stmt->fetchColumn();

            if ($spouse_id) {
                // Spouse becomes a parent to the child
                $this->upsertPersonRelationship((int)$spouse_id, $related_id, 'child', $family_id, false);
                // Child gets the spouse as a parent
                $this->upsertPersonRelationship($related_id, (int)$spouse_id, 'parent', $family_id, true);
            }
        }

        return true;
    }

    // Relationship sync helpers (inserts/updates inverses)
    public function getPersonRelationships(int $person_id)
    {
        $stmt = $this->db->prepare('SELECT related_person_id, relationship_type FROM person_relationships WHERE person_id = :pid');
        $stmt->execute([':pid' => $person_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $out[(int)$r['related_person_id']] = $r['relationship_type'];
        }
        return $out;
    }

    public function deletePersonRelationshipPair(int $person_id, int $related_id)
    {
        $del = $this->db->prepare('DELETE FROM person_relationships WHERE (person_id = :pid AND related_person_id = :rid) OR (person_id = :rid AND related_person_id = :pid)');
        return $del->execute([':pid' => $person_id, ':rid' => $related_id]);
    }

    public function upsertPersonRelationship(int $person_id, int $related_id, string $relationship, int $family_id, bool $is_inverse = false)
    {
        // If primary exists, update; else insert and optionally create inverse mapping
        $check = $this->db->prepare('SELECT id FROM person_relationships WHERE person_id = :pid AND related_person_id = :rid LIMIT 1');
        $check->execute([':pid' => $person_id, ':rid' => $related_id]);
        if ($check->fetch()) {
            $upd = $this->db->prepare('UPDATE person_relationships SET relationship_type = :rtype, family_id = :fid WHERE person_id = :pid AND related_person_id = :rid');
            return $upd->execute([':rtype' => $relationship, ':fid' => $family_id, ':pid' => $person_id, ':rid' => $related_id]);
        } else {
            $ins = $this->db->prepare('INSERT INTO person_relationships (person_id, related_person_id, relationship_type, family_id, is_inverse, created_at, updated_at) VALUES (:pid, :rid, :rtype, :fid, :is_inverse, NOW(), NOW())');
            return $ins->execute([':pid' => $person_id, ':rid' => $related_id, ':rtype' => $relationship, ':fid' => $family_id, ':is_inverse' => $is_inverse ? 1 : 0]);
        }
    }

    /* --- Search / graph helpers --- */

    public function searchPersons($q, $limit = 30)
    {
        $like = "%{$q}%";
        $stmt = $this->db->prepare("
            SELECT id, first_name, middle_name, last_name, birthdate
            FROM persons
            WHERE
                CONCAT_WS(' ', first_name, middle_name, last_name) LIKE :q
                OR CONCAT(first_name, ' ', last_name) LIKE :q
                OR first_name LIKE :q
                OR last_name LIKE :q
            LIMIT :lim
        ");
        $stmt->bindValue(':q', $like, PDO::PARAM_STR);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRelationshipsForPerson(int $for, bool $debug = false)
    {
        // Fetch any relationship rows where the person appears either as person_id or related_person_id
        $rel_sql = 'SELECT person_id, related_person_id, relationship_type, family_id, is_inverse FROM person_relationships WHERE person_id = :pid OR related_person_id = :pid';
        $stmt = $this->db->prepare($rel_sql);
        $stmt->execute([':pid' => $for]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) return ($debug ? ['rows' => [], 'sqls' => ['rel_sql' => $rel_sql]] : []);

        // Normalize to the perspective of $for: return neighbor id and relationship_type from $for's point of view
        $neighbors = [];
        $ids = [];
        foreach ($rows as $r) {
            if ((int)$r['person_id'] === (int)$for) {
                $nid = (int)$r['related_person_id'];
                $rel = $r['relationship_type'];
                $isInv = (int)$r['is_inverse'];
            } else {
                // the stored row has the queried person on the related_person_id side
                $nid = (int)$r['person_id'];
                // invert the relationship type for perspective (child <-> parent)
                $inv = $this->getInverseRelationship($r['relationship_type']);
                $rel = $inv ?? $r['relationship_type'];
                // mark as inverse because we flipped perspective
                $isInv = 1;
            }
            $neighbors[$nid] = ['id' => $nid, 'relationship_type' => $rel, 'family_id' => $r['family_id'], 'is_inverse' => $isInv];
            $ids[] = $nid;
        }

        // Fetch person basic info for all neighbor ids
        $unique = array_values(array_unique($ids));
        if (count($unique) === 0) return ($debug ? ['rows' => [], 'sqls' => ['rel_sql' => $rel_sql]] : []);
        $placeholders = implode(',', array_fill(0, count($unique), '?'));
        $ps_sql = "SELECT id, first_name, middle_name, last_name, sex FROM persons WHERE id IN ($placeholders)";
        $ps = $this->db->prepare($ps_sql);
        $ps->execute($unique);
        $persons = $ps->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($persons as $p) {
            $map[(int)$p['id']] = $p;
        }

        $out = [];
        foreach ($neighbors as $nid => $meta) {
            $p = $map[$nid] ?? ['first_name' => '', 'middle_name' => '', 'last_name' => '', 'sex' => null];
            $out[] = array_merge($meta, $p);
        }

        if ($debug) {
            return ['rows' => $out, 'sqls' => ['rel_sql' => $rel_sql, 'person_sql' => $ps_sql, 'person_ids' => $unique]];
        }

        return $out;
    }

    public function getEdgesForNodeList(array $ids)
    {
        if (empty($ids)) return [];
        $allIds = $ids;
        $placeholders = implode(',', array_fill(0, count($allIds), '?'));
        $sql = "SELECT person_id, related_person_id, relationship_type FROM person_relationships WHERE person_id IN ($placeholders) AND related_person_id IN ($placeholders)";
        $params = array_merge($allIds, $allIds);
        $er = $this->db->prepare($sql);
        $er->execute($params);
        $edges = $er->fetchAll(PDO::FETCH_ASSOC);
        if ($debug) return ['edges' => $edges, 'sql' => $sql, 'params' => $params];
        return $edges;
    }

    /* --- Next household number helper --- */

    public function nextHouseholdNoByPurok(int $purok_id = null, string $code = null)
    {
        if ($purok_id) {
            $stmt = $this->db->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(household_no, '-', -1) AS UNSIGNED)) AS maxnum FROM households WHERE purok_id = :purok_id AND household_no LIKE CONCAT('%','-','%')");
            $stmt->execute([':purok_id' => $purok_id]);
        } else {
            $stmt = $this->db->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(household_no, '-', -1) AS UNSIGNED)) AS maxnum FROM households WHERE household_no LIKE :like");
            $stmt->execute([':like' => $code . '-%']);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxnum = $row && $row['maxnum'] ? (int)$row['maxnum'] : 0;
        $next = $maxnum + 1;
        $number = str_pad($next, 3, '0', STR_PAD_LEFT);
        $household_no = $code ? ($code . '-' . $number) : null;
        return ['success' => true, 'number' => $number, 'next' => $next, 'household_no' => $household_no];
    }
}