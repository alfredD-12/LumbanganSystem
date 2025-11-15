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
            'cvd_id' => null
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
        
            // If contact number isn't stored on persons table, try loading it from users.mobile
            // (some installations keep contact on users table). This ensures views using
            // personValue('contact_no') will render the user's mobile when available.
            if (empty($data['person']['contact_no'])) {
                try {
                    $u = $db->prepare('SELECT mobile FROM users WHERE person_id = :person_id LIMIT 1');
                    $u->execute(['person_id' => $person_id]);
                    $um = $u->fetch(PDO::FETCH_ASSOC);
                    if ($um && isset($um['mobile']) && $um['mobile'] !== null) {
                        $data['person']['contact_no'] = $um['mobile'];
                    }
                } catch (Exception $e) {
                    // ignore and leave person.contact_no empty if query fails
                }
            }
        
        // 2. Get most recent CVD assessment (within 30 days)
        $stmt = $db->prepare("
            SELECT id, survey_date, answered_at
            FROM cvd_ncd_risk_assessments
            WHERE person_id = :person_id
              AND survey_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ORDER BY survey_date DESC
            LIMIT 1
        ");
        $stmt->execute(['person_id' => $person_id]);
        $assessment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($assessment) {
            $data['cvd_id'] = $assessment['id'];
            $cvd_id = $assessment['id'];
            
            // 3. Load Vitals
            $stmt = $db->prepare("
                SELECT * FROM vitals WHERE cvd_id = :cvd_id LIMIT 1
            ");
            $stmt->execute(['cvd_id' => $cvd_id]);
            $data['vitals'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
            // 4. Load Lifestyle Risk
            $stmt = $db->prepare("
                SELECT * FROM lifestyle_risk WHERE cvd_id = :cvd_id LIMIT 1
            ");
            $stmt->execute(['cvd_id' => $cvd_id]);
            $data['lifestyle'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
            // 5. Load Angina/Stroke Screening
            $stmt = $db->prepare("
                SELECT * FROM angina_stroke_screening WHERE cvd_id = :cvd_id LIMIT 1
            ");
            $stmt->execute(['cvd_id' => $cvd_id]);
            $data['angina'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
            // 6. Load Diabetes Screening
            $stmt = $db->prepare("
                SELECT * FROM diabetes_screening WHERE cvd_id = :cvd_id LIMIT 1
            ");
            $stmt->execute(['cvd_id' => $cvd_id]);
            $data['diabetes'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
            // 7. Load Family History (uses person_id, not cvd_id)
            $stmt = $db->prepare("
                SELECT * FROM health_family_history 
                WHERE person_id = :person_id 
                ORDER BY recorded_at DESC 
                LIMIT 1
            ");
            $stmt->execute(['person_id' => $person_id]);
            $data['family_history'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
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
