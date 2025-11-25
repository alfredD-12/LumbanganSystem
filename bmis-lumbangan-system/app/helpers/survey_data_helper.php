<?php
/**
 * Survey Data Helper
 * Pre-loads existing survey data from database for form population
 */

if (!function_exists('loadSurveyData')) {
    /**
     * Load all survey-related data for the logged-in user
     * @return array Associative array with person, vitals, lifestyle, etc.
     */
    function loadSurveyData() {
        $data = [
            'person' => [],
            'vitals' => [],
            'lifestyle' => [],
            'angina' => [],
            'diabetes' => [],
            'family_history' => [],
            'family' => [],
            'cvd_id' => null,
            'household' => []
        ];
        
        if (!isset($_SESSION['person_id'])) {
            return $data;
        }
        
        require_once dirname(__DIR__) . '/config/Database.php';
        $db = (new Database())->getConnection();
        $person_id = $_SESSION['person_id'];
        
        // 1. Load Person Data
        $stmt = $db->prepare("
            SELECT 
                p.*,
                TIMESTAMPDIFF(YEAR, p.birthdate, CURDATE()) as age
            FROM persons p
            WHERE p.id = :person_id
            LIMIT 1
        ");
        $stmt->execute(['person_id' => $person_id]);
        $data['person'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        
        // Load vitals from the most recent assessment, but only if it's from the current month.
        // This prevents carrying over old data to a new month's assessment.
        $vitals_stmt = $db->prepare("
            SELECT 
                v.height_cm, v.weight_kg, v.waist_circumference_cm
            FROM vitals v 
            JOIN cvd_ncd_risk_assessments c ON v.cvd_id = c.id 
            WHERE c.person_id = :person_id
              AND YEAR(c.survey_date) = YEAR(CURDATE())
              AND MONTH(c.survey_date) = MONTH(CURDATE())
            ORDER BY c.survey_date DESC, c.id DESC LIMIT 1
        ");
        $vitals_stmt->execute(['person_id' => $person_id]);
        $current_month_vitals = $vitals_stmt->fetch(PDO::FETCH_ASSOC);
        if ($current_month_vitals) $data['person'] = array_merge($data['person'], $current_month_vitals);

        // Load mobile from users table if not present on persons
        if (empty($data['person']['contact_no'])) {
            try {
                $u = $db->prepare('SELECT mobile FROM users WHERE person_id = :person_id LIMIT 1');
                $u->execute(['person_id' => $person_id]);
                $um = $u->fetch(PDO::FETCH_ASSOC);
                if ($um && isset($um['mobile']) && $um['mobile'] !== null) {
                    $data['person']['contact_no'] = $um['mobile'];
                }
            } catch (Exception $e) {
                // ignore
            }
        }

        // 2. Get most recent CVD assessment for the current month
        $stmt = $db->prepare("
            SELECT id, survey_date, answered_at
            FROM cvd_ncd_risk_assessments
            WHERE person_id = :person_id
              AND YEAR(answered_at) = YEAR(CURDATE())
              AND MONTH(answered_at) = MONTH(CURDATE())
            ORDER BY answered_at DESC
            LIMIT 1
        ");
        $stmt->execute(['person_id' => $person_id]);
        $assessment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($assessment) {
            $data['cvd_id'] = $assessment['id'];
            $cvd_id = $assessment['id'];
            
            // 3. Load Vitals
            $stmt = $db->prepare("SELECT * FROM vitals WHERE cvd_id = :cvd_id LIMIT 1");
            $stmt->execute(['cvd_id' => $cvd_id]);
            $data['vitals'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
            // 4. Load Lifestyle Risk
            $stmt = $db->prepare("SELECT * FROM lifestyle_risk WHERE cvd_id = :cvd_id LIMIT 1");
            $stmt->execute(['cvd_id' => $cvd_id]);
            $data['lifestyle'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
            // 5. Load Angina/Stroke Screening
            $stmt = $db->prepare("SELECT * FROM angina_stroke_screening WHERE cvd_id = :cvd_id LIMIT 1");
            $stmt->execute(['cvd_id' => $cvd_id]);
            $data['angina'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
            // 6. Load Diabetes Screening
            $stmt = $db->prepare("SELECT * FROM diabetes_screening WHERE cvd_id = :cvd_id LIMIT 1");
            $stmt->execute(['cvd_id' => $cvd_id]);
            $data['diabetes'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        }
        
        // 7. Load Family History (person-level) — always try to load most recent record
        try {
            $stmt = $db->prepare("
                SELECT * FROM health_family_history 
                WHERE person_id = :person_id 
                ORDER BY recorded_at DESC 
                LIMIT 1
            ");
            $stmt->execute(['person_id' => $person_id]);
            $data['family_history'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $data['family_history'] = [];
        }

        // 8. Load Family / Relationships for this person (related persons + type)
        try {
            $stmt = $db->prepare("
                -- Step 1: Get direct relationships for the current person
                (SELECT pr.related_person_id AS id, pr.relationship_type, p.first_name, p.middle_name, p.last_name, p.birthdate, p.sex
                 FROM person_relationships pr JOIN persons p ON p.id = pr.related_person_id
                 WHERE pr.person_id = :person_id)
                
                UNION
                
                -- Step 2: Get parents (grandparents of current person)
                (SELECT gp.related_person_id AS id, 'grandparent' AS relationship_type, p_gp.first_name, p_gp.middle_name, p_gp.last_name, p_gp.birthdate, p_gp.sex
                 FROM person_relationships p_rel -- my parents
                 JOIN person_relationships gp ON p_rel.related_person_id = gp.person_id AND gp.relationship_type = 'parent'
                 JOIN persons p_gp ON p_gp.id = gp.related_person_id
                 WHERE p_rel.person_id = :person_id AND p_rel.relationship_type = 'parent')
                
                UNION
                
                -- Step 3: Get siblings (children of my parents, excluding myself)
                (SELECT s_rel.person_id AS id, 'sibling' AS relationship_type, p_s.first_name, p_s.middle_name, p_s.last_name, p_s.birthdate, p_s.sex
                 FROM person_relationships p_rel -- my parents
                 JOIN person_relationships s_rel ON p_rel.related_person_id = s_rel.related_person_id AND s_rel.relationship_type = 'child'
                 JOIN persons p_s ON p_s.id = s_rel.person_id
                 WHERE p_rel.person_id = :person_id AND p_rel.relationship_type = 'parent' AND s_rel.person_id != :person_id)
                
                UNION
                
                -- Step 4: Get grandchildren (children of my children)
                (SELECT gc_rel.person_id AS id, 'grandchild' AS relationship_type, p_gc.first_name, p_gc.middle_name, p_gc.last_name, p_gc.birthdate, p_gc.sex
                 FROM person_relationships c_rel -- my children
                 JOIN person_relationships gc_rel ON c_rel.related_person_id = gc_rel.related_person_id AND gc_rel.relationship_type = 'child'
                 JOIN persons p_gc ON p_gc.id = gc_rel.person_id
                 WHERE c_rel.person_id = :person_id AND c_rel.relationship_type = 'child')
            ");
            $stmt->execute(['person_id' => $person_id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Normalize and de-duplicate
            $family_members = [];
            $seen_ids = [];
            $relationship_priority = [
                'spouse' => 1, 'parent' => 2, 'child' => 3, 'sibling' => 4, 
                'grandparent' => 5, 'grandchild' => 6, 'other' => 7
            ];

            foreach ($rows as &$r) {
                $id = (int)$r['id'];
                if (in_array($id, $seen_ids)) {
                    // If we see the same person again (e.g., direct 'other' and inferred 'sibling'),
                    // prioritize the more specific relationship.
                    $existing_idx = -1;
                    foreach ($family_members as $i => $fm) {
                        if ($fm['id'] === $id) {
                            $existing_idx = $i;
                            break;
                        }
                    }

                    if ($existing_idx !== -1) {
                        $current_priority = $relationship_priority[$r['relationship_type']] ?? 99;
                        $existing_priority = $relationship_priority[$family_members[$existing_idx]['relationship_type']] ?? 99;
                        if ($current_priority < $existing_priority) {
                            // The new relationship is more important, so replace it.
                            $family_members[$existing_idx]['relationship_type'] = $r['relationship_type'];
                        }
                    }
                    continue; // Skip adding a duplicate row
                }

                $seen_ids[] = $id;
                $r['full_name'] = trim(($r['first_name'] ?? '') . ' ' . ($r['middle_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
                $family_members[] = $r;
            }

            // Sort the final list
            usort($family_members, function($a, $b) use ($relationship_priority) {
                $prio_a = $relationship_priority[$a['relationship_type']] ?? 99;
                $prio_b = $relationship_priority[$b['relationship_type']] ?? 99;
                if ($prio_a != $prio_b) {
                    return $prio_a <=> $prio_b;
                }
                $name_a = ($a['last_name'] ?? '') . ($a['first_name'] ?? '');
                $name_b = ($b['last_name'] ?? '') . ($b['first_name'] ?? '');
                return strcasecmp($name_a, $name_b);
            });

            $data['family'] = $family_members;

        } catch (Exception $e) {
            $data['family'] = [];
        }
        
        // 9. Load Household / Families / Head-of-household data
        try {
            // Find the current person's family, then find the head of that family.
            // The head is either designated in families.head_person_id or has family_position = 'Head' in persons table.
            $head_stmt = $db->prepare("
                SELECT 
                    f.id AS family_id,
                    f.head_person_id,
                    (SELECT p_head.id FROM persons p_head WHERE p_head.family_id = p.family_id AND p_head.is_head = 1 LIMIT 1) as head_by_position,
                    f.household_id
                FROM persons p
                LEFT JOIN families f ON p.family_id = f.id
                WHERE p.id = :person_id
                LIMIT 1
            ");
            $head_stmt->execute(['person_id' => $person_id]);
            $head_result = $head_stmt->fetch(PDO::FETCH_ASSOC);

            $head_person_id = $person_id;
            $family_id = null;
            $household_id = null;
            if ($head_result) {
                $family_id = $head_result['family_id'] ?? null;
                $data['is_family_head'] = ($head_result['head_person_id'] == $person_id || $head_result['head_by_position'] == $person_id);
                $household_id = $head_result['household_id'] ?? null;
                // Prioritize explicit head_person_id from families
                $head_person_id = $head_result['head_person_id'] ?: ($head_result['head_by_position'] ?: $person_id);
            }

            // If the current person has no family_id (data not populated), try to derive
            // the family/household by looking up related persons (e.g., parent/spouse)
            // who have a family_id set. This handles cases where relationships exist
            // but the persons.family_id column is not populated for the child record.
            if (empty($family_id)) {
                try {
                    $rel_sql = "
                        SELECT p2.family_id AS family_id, f.head_person_id AS head_person_id, f.household_id AS household_id
                        FROM person_relationships pr
                        JOIN persons p2 ON (p2.id = pr.related_person_id OR p2.id = pr.person_id)
                        LEFT JOIN families f ON p2.family_id = f.id
                        WHERE (pr.person_id = :person_id OR pr.related_person_id = :person_id)
                          AND p2.id != :person_id
                          AND p2.family_id IS NOT NULL
                        LIMIT 1
                    ";
                    $rel_stmt = $db->prepare($rel_sql);
                    $rel_stmt->execute(['person_id' => $person_id]);
                    $rel_row = $rel_stmt->fetch(PDO::FETCH_ASSOC);
                    if ($rel_row && !empty($rel_row['family_id'])) {
                        $family_id = $rel_row['family_id'];
                        $household_id = $rel_row['household_id'] ?? $household_id;
                        $head_person_id = $rel_row['head_person_id'] ?: $head_person_id;
                        // mark as not head since we derived this from another person's family
                        $data['is_family_head'] = false;
                    }
                } catch (Exception $e) {
                    // ignore and continue — absence of family/household will leave fields empty
                }
            }

            // 9a. If there's a households row (via household_id), fetch it and merge fields
            if ($household_id) {
                $hh_stmt = $db->prepare("SELECT * FROM households WHERE id = :hid LIMIT 1");
                $hh_stmt->execute(['hid' => $household_id]);
                $hh = $hh_stmt->fetch(PDO::FETCH_ASSOC);
                if ($hh) {
                    // If households table stores a single 'address' column, parse it.
                    $addressParts = [];
                    if (!empty($hh['address'])) {
                        // Split on commas, trim each piece.
                        $parts = array_map('trim', explode(',', $hh['address'], 4));
                        $addressParts = [
                            'address_house_no' => $parts[0] ?? null,
                            'address_street' => $parts[1] ?? null,
                            'address_sitio_subdivision' => $parts[2] ?? null,
                            'address_building' => $parts[3] ?? null,
                        ];
                    }

                    // Map households columns to the expected form keys (don't overwrite existing household keys)
                    $map = [
                        'household_no' => $hh['household_no'] ?? null,
                        // Individual address fields prefer explicit columns if present, otherwise use parsed addressParts
                        'address_house_no' => $hh['address_house_no'] ?? ($addressParts['address_house_no'] ?? null),
                        'address_street' => $hh['address_street'] ?? ($addressParts['address_street'] ?? null),
                        'address_sitio_subdivision' => $hh['address_sitio_subdivision'] ?? ($addressParts['address_sitio_subdivision'] ?? null),
                        'address_building' => $hh['address_building'] ?? ($addressParts['address_building'] ?? null),
                        'purok_id' => isset($hh['purok_id']) ? (int)$hh['purok_id'] : null,
                        'home_ownership' => $hh['home_ownership'] ?? null,
                        'home_ownership_other' => $hh['home_ownership_other'] ?? null,
                        'construction_material' => $hh['construction_material'] ?? null,
                        'construction_material_other' => $hh['construction_material_other'] ?? null,
                        'lighting_facility' => $hh['lighting_facility'] ?? null,
                        'lighting_facility_other' => $hh['lighting_facility_other'] ?? null,
                        'toilet_type' => $hh['toilet_type'] ?? null,
                        'toilet_type_other' => $hh['toilet_type_other'] ?? null,
                        'water_level' => $hh['water_level'] ?? null,
                        'water_source' => $hh['water_source'] ?? null,
                        'water_storage' => $hh['water_storage'] ?? null,
                        'drinking_water_other_source' => $hh['drinking_water_other_source'] ?? null,
                        'garbage_container' => $hh['garbage_container'] ?? null,
                        'garbage_segregated' => isset($hh['garbage_segregated']) ? $hh['garbage_segregated'] : null,
                        'garbage_disposal_method' => $hh['garbage_disposal_method'] ?? null,
                        'garbage_disposal_other' => $hh['garbage_disposal_other'] ?? null
                    ];
                    // Merge without overwriting values already present
                    foreach ($map as $k => $v) {
                        if (!isset($data['household'][$k]) && $v !== null) $data['household'][$k] = $v;
                    }
                }
            }

            // 9c. Fetch the head's household address (useful for confirmation prompt). Do this regardless
            // of whether the current user is the head so the view can display the head's address when available.
            if ($household_id) {
                $head_addr_stmt = $db->prepare("SELECT * FROM households WHERE id = :hid LIMIT 1");
                $head_addr_stmt->execute(['hid' => $household_id]);
                $head_addr_res = $head_addr_stmt->fetch(PDO::FETCH_ASSOC);
                if ($head_addr_res) {
                    // Prefer a single 'address' column if present, otherwise build from individual fields
                    if (!empty($head_addr_res['address'])) {
                        $data['household']['head_address_full'] = $head_addr_res['address'];
                    } else {
                        $parts = [];
                        if (!empty($head_addr_res['address_house_no'])) $parts[] = $head_addr_res['address_house_no'];
                        if (!empty($head_addr_res['address_street'])) $parts[] = $head_addr_res['address_street'];
                        if (!empty($head_addr_res['address_sitio_subdivision'])) $parts[] = $head_addr_res['address_sitio_subdivision'];
                        if (!empty($head_addr_res['address_building'])) $parts[] = $head_addr_res['address_building'];
                        if (!empty($parts)) $data['household']['head_address_full'] = implode(', ', $parts);
                    }

                    // Also pre-populate purok_id from head's household if not already set
                    if (!isset($data['household']['purok_id']) && isset($head_addr_res['purok_id'])) {
                        $data['household']['purok_id'] = $head_addr_res['purok_id'];
                    }
                }
            }

            // 9b. Fetch family-level info and head user's contact/email
            $household_stmt = $db->prepare("
                SELECT 
                    f.family_number AS family_number,
                    f.residency_status AS residency_status,
                    f.length_of_residency_months AS length_of_residency_months,
                    u.mobile AS contact_no,
                    u.email AS email,
                    f.household_id AS household_id_from_family
                FROM persons p_head
                LEFT JOIN families f ON p_head.family_id = f.id
                LEFT JOIN users u ON u.person_id = p_head.id
                WHERE p_head.id = :head_person_id
                LIMIT 1
            ");
            $household_stmt->execute(['head_person_id' => $head_person_id]);
            $household_data = $household_stmt->fetch(PDO::FETCH_ASSOC);

            if ($household_data) {
                // Normalize keys used by views
                $merge = [];
                // prefer explicit family_number from families; fallback to head's contact_no if family_number empty
                $merge['family_number'] = $household_data['family_number'] ?: ($household_data['contact_no'] ?? null);
                $merge['contact_no'] = $household_data['contact_no'] ?? null;
                $merge['email'] = $household_data['email'] ?? null;
                $merge['residency_status'] = $household_data['residency_status'] ?? null;
                $merge['length_of_residency_months'] = $household_data['length_of_residency_months'] ?? null;

                // Merge into household dataset without clobbering existing keys when present
                foreach ($merge as $k => $v) {
                    if (!isset($data['household'][$k]) || $data['household'][$k] === '' || $data['household'][$k] === null) {
                        $data['household'][$k] = $v;
                    }
                }

                // If family row contains a household_id and we didn't fetch households earlier, try to fetch it now
                if (empty($household_id) && !empty($household_data['household_id_from_family'])) {
                    $hid = (int)$household_data['household_id_from_family'];
                    $hh_stmt2 = $db->prepare("SELECT * FROM households WHERE id = :hid LIMIT 1");
                    $hh_stmt2->execute(['hid' => $hid]);
                    $hh2 = $hh_stmt2->fetch(PDO::FETCH_ASSOC);
                    if ($hh2) {
                        $addressParts2 = [];
                        if (!empty($hh2['address'])) {
                            $parts2 = array_map('trim', explode(',', $hh2['address']));
                            while (count($parts2) < 4) $parts2[] = '';
                            $addressParts2 = $parts2;
                        }
                        $map2 = [
                            'household_no' => $hh2['household_no'] ?? null,
                            'address_house_no' => $hh2['address_house_no'] ?? ($addressParts2[0] ?? null),
                            'address_street' => $hh2['address_street'] ?? ($addressParts2[1] ?? null),
                            'address_sitio_subdivision' => $hh2['address_sitio_subdivision'] ?? ($addressParts2[2] ?? null),
                            'address_building' => $hh2['address_building'] ?? ($addressParts2[3] ?? null),
                            'home_ownership' => $hh2['home_ownership'] ?? null,
                            'home_ownership_other' => $hh2['home_ownership_other'] ?? null,
                            'construction_material' => $hh2['construction_material'] ?? null,
                            'construction_material_other' => $hh2['construction_material_other'] ?? null,
                            'lighting_facility' => $hh2['lighting_facility'] ?? null,
                            'lighting_facility_other' => $hh2['lighting_facility_other'] ?? null,
                            'toilet_type' => $hh2['toilet_type'] ?? null,
                            'toilet_type_other' => $hh2['toilet_type_other'] ?? null,
                            'water_level' => $hh2['water_level'] ?? null,
                            'water_source' => $hh2['water_source'] ?? null,
                            'water_storage' => $hh2['water_storage'] ?? null,
                            'drinking_water_other_source' => $hh2['drinking_water_other_source'] ?? null,
                            'garbage_container' => $hh2['garbage_container'] ?? null,
                            'garbage_segregated' => isset($hh2['garbage_segregated']) ? $hh2['garbage_segregated'] : null,
                            'garbage_disposal_method' => $hh2['garbage_disposal_method'] ?? null,
                            'garbage_disposal_other' => $hh2['garbage_disposal_other'] ?? null
                        ];
                        foreach ($map2 as $k => $v) {
                            if (!isset($data['household'][$k]) && $v !== null) $data['household'][$k] = $v;
                        }
                    }
                }
            }

        } catch (Exception $e) {
            // Ignore if it fails, fields will just be empty
        }

        return $data;
    }
}

if (!function_exists('surveyValue')) {
    /**
     * Safely get value from survey data array
     * @param string $section Section name (person, vitals, lifestyle, etc.)
     * @param string $field Field name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    function surveyValue($section, $field, $default = '') {
        global $surveyData;
        
        if (!isset($surveyData[$section][$field])) {
            return $default;
        }
        
        $value = $surveyData[$section][$field];
        
        // Handle NULL values
        if ($value === null) {
            return $default;
        }
        
        // HTML escape string values
        if (is_string($value)) {
            return htmlspecialchars($value);
        }
        
        return $value;
    }
}

if (!function_exists('isChecked')) {
    /**
     * Check if a checkbox/radio should be checked
     * @param string $section Section name
     * @param string $field Field name
     * @param mixed $value Value to check against
     * @return string 'checked' or empty string
     */
    function isChecked($section, $field, $value) {
        global $surveyData;
        
        if (!isset($surveyData[$section][$field])) {
            return '';
        }
        
        // For boolean values (tinyint)
        if (is_bool($surveyData[$section][$field]) || is_numeric($surveyData[$section][$field])) {
            $dbValue = (int)$surveyData[$section][$field];
            $checkValue = (int)$value;
            return $dbValue === $checkValue ? 'checked' : '';
        }
        
        // For string comparisons
        return $surveyData[$section][$field] == $value ? 'checked' : '';
    }
}

if (!function_exists('isSelected')) {
    /**
     * Check if a select option should be selected
     * @param string $section Section name
     * @param string $field Field name
     * @param mixed $value Value to check against
     * @return string 'selected' or empty string
     */
    function isSelected($section, $field, $value) {
        global $surveyData;
        
        if (!isset($surveyData[$section][$field])) {
            return '';
        }
        
        return $surveyData[$section][$field] == $value ? 'selected' : '';
    }
}