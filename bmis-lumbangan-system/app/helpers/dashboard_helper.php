<?php
/**
 * Dashboard Helper - Fetch dynamic data from database
 */

require_once dirname(__DIR__) . '/config/Database.php';

/**
 * Get user's pending document requests count
 */
function getPendingRequestsCount($userId) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $query = "SELECT COUNT(*) as count FROM document_requests 
                  WHERE user_id = :user_id AND status = 'Pending'";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get user's completed document requests count
 */
function getCompletedRequestsCount($userId) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $query = "SELECT COUNT(*) as count FROM document_requests 
                  WHERE user_id = :user_id AND status = 'Released'";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get user's account creation year
 */
function getMemberSinceYear($userId) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $query = "SELECT YEAR(created_at) as year FROM users WHERE id = :user_id";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['year'] ?? date('Y');
    } catch (Exception $e) {
        return date('Y');
    }
}

/**
 * Get all user stats at once (more efficient)
 */
function getUserDashboardStats($userId) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $query = "
            SELECT
                u.person_id,
                (SELECT COUNT(*) FROM document_requests WHERE user_id = u.id AND status = 'Pending') as pending_requests,
                (SELECT COUNT(*) FROM document_requests WHERE user_id = u.id AND status = 'Released') as completed_requests,
                YEAR(u.created_at) as member_since,
                EXISTS(SELECT 1 FROM cvd_ncd_risk_assessments WHERE person_id = u.person_id AND is_approved = 1) as is_verified,
                TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(h.address, ',', 2), ',', -1)) as street_name,
                pu.name as purok_name,
                UPPER(CONCAT(SUBSTRING(p.first_name, 1, 1), IFNULL(SUBSTRING(p.middle_name, 1, 1), ''), SUBSTRING(p.last_name, 1, 1))) as initials,
                EXISTS(SELECT 1 FROM cvd_ncd_risk_assessments WHERE person_id = u.person_id AND YEAR(answered_at) = YEAR(CURDATE()) AND MONTH(answered_at) = MONTH(CURDATE())) as has_monthly_survey,
                (SELECT MAX(answered_at) FROM cvd_ncd_risk_assessments WHERE person_id = u.person_id) as last_survey_date
            FROM 
                users u
            JOIN 
                persons p ON u.person_id = p.id
            JOIN 
                families f ON p.family_id = f.id
            JOIN 
                households h ON f.household_id = h.id
            LEFT JOIN 
                puroks pu ON h.purok_id = pu.id
            WHERE 
                u.id = :user_id
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $verification_status = ($result && $result['is_verified']) ? 'Verified' : 'Unverified';
        
        // Construct the Resident ID
        $residentId = 'ID not available';
        if ($result) {
            $paddedPersonId = str_pad($result['person_id'], 5, '0', STR_PAD_LEFT);
            $residentId = "{$result['initials']}-{$result['member_since']}-{$paddedPersonId}";
        }

        // Survey logic
        $hasMonthlySurvey = $result['has_monthly_survey'] ?? 0;
        $surveyNumber = $hasMonthlySurvey ? 0 : 1;
        $lastSurveyDate = 'No survey taken yet';
        if ($result && $result['last_survey_date']) {
            $lastSurveyDate = date('F d, Y', strtotime($result['last_survey_date']));
        }
        $nextSurveyDate = date('F 01, Y', strtotime('first day of next month'));
        $surveyStatus = $hasMonthlySurvey ? 'Completed' : 'Open';
        $surveyStatusClass = $hasMonthlySurvey ? 'completed' : 'open';

        return [
            'pending_requests' => $result['pending_requests'] ?? 0,
            'completed_requests' => $result['completed_requests'] ?? 0,
            'member_since' => $result['member_since'] ?? date('Y'),
            'verification_status' => $verification_status,
            'address' => ($result && $result['street_name'] && $result['purok_name']) ? "{$result['street_name']}, {$result['purok_name']}" : 'Address not set',
            'resident_id' => $residentId,
            'has_monthly_survey' => $hasMonthlySurvey,
            'survey_number' => $surveyNumber,
            'last_survey_date' => $lastSurveyDate,
            'next_survey_date' => $nextSurveyDate,
            'survey_status' => $surveyStatus,
            'survey_status_class' => $surveyStatusClass
        ];
    } catch (Exception $e) {
        return [
            'pending_requests' => 0,
            'completed_requests' => 0,
            'member_since' => date('Y'),
            'verification_status' => 'Unverified',
            'address' => 'Address not set',
            'resident_id' => 'ID not available',
            'has_monthly_survey' => 0,
            'survey_number' => 1,
            'last_survey_date' => 'Not available',
            'next_survey_date' => date('F 01, Y', strtotime('first day of next month')),
            'survey_status' => 'Open',
            'survey_status_class' => 'open'
        ];
    }
}

/**
 * Get all active officials
 */
function getActiveOfficials() {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $query = "
            SELECT 
                full_name, 
                role, 
                contact_no, 
                email, 
                photo_url 
            FROM officials 
            WHERE active = 1 
            ORDER BY role = 'Punong Barangay' DESC, id ASC
        ";
        
        $stmt = $conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}
?>
