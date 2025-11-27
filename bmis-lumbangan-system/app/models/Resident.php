<?php
require_once __DIR__ . '/../config/Database.php';

class Resident {
    private $db;

    public function __construct($db = null) {
        if ($db) {
            $this->db = $db;
        } else {
            $this->db = (new Database())->getConnection();
        }
    }

    /**
     * Fetch all residents with household and purok information
     * Returns an array of associative rows.
     */
    public function getAll(): array {
        try {
                $sql = "SELECT p.id,
                               p.first_name,
                               p.middle_name,
                               p.last_name,
                               p.suffix,
                               p.sex,
                               p.marital_status,
                               u.mobile AS contact_no,
                               u.email AS email,
                               p.birthdate,
                               h.household_no,
                               h.address AS address,
                               pr.name AS purok_name
                        FROM persons p
                        LEFT JOIN households h ON p.household_id = h.id
                        LEFT JOIN puroks pr ON h.purok_id = pr.id
                        LEFT JOIN users u ON u.person_id = p.id
                        ORDER BY p.last_name, p.first_name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('Resident::getAll error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Return available puroks (id, name) for dropdowns
     */
    public function getPuroks(): array {
        try {
            $sql = "SELECT id, name FROM puroks ORDER BY name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('Resident::getPuroks error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get CVD/NCD risk assessments for a person ordered from newest to oldest
     * Returns array of associative rows.
     */
    public function getAssessmentsByPerson(int $personId): array {
        try {
            $sql = "SELECT id, person_id, answered_at, surveyed_by_official_id, survey_date, notes, is_approved, approved_by_official_id, approved_at, review_notes
                    FROM cvd_ncd_risk_assessments
                    WHERE person_id = :pid
                    ORDER BY answered_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':pid', $personId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('Resident::getAssessmentsByPerson error: ' . $e->getMessage());
            return [];
        }
    }
}
