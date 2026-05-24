<?php
require_once __DIR__ . '/../config/Database.php';

class Complaint {
    private $pdo;
    private $table_name = "incidents";

    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
        $this->ensurePoliceForwardingColumns();
    }

    /**
     * Get all complaints with filters
     */
    public function getAll($filters = []) {
        $query = "SELECT DISTINCT i.id,
                         i.user_id,
                         i.incident_title,
                         i.blotter_type,
                         i.case_type_id,
                         i.complainant_name,
                         i.complainant_type,
                         i.complainant_contact,
                         i.complainant_gender,
                         i.complainant_birthday,
                         i.complainant_address,
                         i.offender_type,
                         i.offender_gender,
                         i.offender_name,
                         i.offender_address,
                         i.offender_description,
                         i.date_of_incident,
                         i.time_of_incident,
                         i.location,
                         i.narrative,
                         i.status_id,
                         i.forwarded_to_police,
                         i.forwarded_to_police_at,
                         i.created_at,
                         i.updated_at,
                         i.resolved_at,
                         s.label as status_label,
                         ct.label as case_type
                  FROM {$this->table_name} i
                  LEFT JOIN statuses s ON i.status_id = s.id
                  LEFT JOIN case_types ct ON i.case_type_id = ct.id
                  WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['search']) && is_string($filters['search']) && trim($filters['search']) !== '') {
            $searchTerm = '%' . trim($filters['search']) . '%';
            $query .= " AND (
                i.complainant_name LIKE :search1 
                OR i.location LIKE :search2 
                OR i.narrative LIKE :search3
                OR i.incident_title LIKE :search4
                OR i.offender_name LIKE :search5
                OR i.blotter_type LIKE :search6
                OR ct.label LIKE :search7
                OR s.label LIKE :search8
            )";
            $params[':search1'] = $searchTerm;
            $params[':search2'] = $searchTerm;
            $params[':search3'] = $searchTerm;
            $params[':search4'] = $searchTerm;
            $params[':search5'] = $searchTerm;
            $params[':search6'] = $searchTerm;
            $params[':search7'] = $searchTerm;
            $params[':search8'] = $searchTerm;
        }
        
        if (isset($filters['status_id']) && is_string($filters['status_id']) && trim($filters['status_id']) !== '') {
            $query .= " AND i.status_id = :status_id";
            $params[':status_id'] = $filters['status_id'];
        }
        
        if (isset($filters['case_type_id']) && is_string($filters['case_type_id']) && trim($filters['case_type_id']) !== '') {
            $query .= " AND i.case_type_id = :case_type_id";
            $params[':case_type_id'] = $filters['case_type_id'];
        }

        // Police Portal guard: optionally exclude resident-submitted complaints.
        // Resident submissions have a non-NULL `user_id`.
        if (!empty($filters['exclude_resident'])) {
            $query .= " AND i.user_id IS NULL";
        }

        if (!empty($filters['police_forwarded'])) {
            $query .= " AND i.forwarded_to_police = 1";
        }

        if (!empty($filters['active_only'])) {
            $query .= " AND i.status_id <> 3 AND LOWER(COALESCE(s.label, '')) <> 'resolved'";
        }
        
        $query .= " ORDER BY i.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single complaint by ID
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT i.*, 
                   s.label as status_label,
                   ct.label as case_type
            FROM {$this->table_name} i
            LEFT JOIN statuses s ON i.status_id = s.id
            LEFT JOIN case_types ct ON i.case_type_id = ct.id
            WHERE i.id = ?
        ");
        
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new complaint
     */
    public function create($data) {
        $statusId = $this->getDefaultStatusId();
        if ($statusId === null) {
            throw new Exception('Complaint status is not configured. Please seed the statuses table.');
        }

        $caseTypeId = $this->resolveCaseTypeId($data['case_type_id'] ?? null);
        if ($caseTypeId === null) {
            throw new Exception('Case types are not configured. Please seed the case_types table.');
        }

        $stmt = $this->pdo->prepare("INSERT INTO {$this->table_name} (
            user_id, incident_title, blotter_type, complainant_name, complainant_type,
            complainant_gender, complainant_contact, complainant_birthday, complainant_address,
            offender_name, offender_type, offender_gender, offender_address, offender_description,
            case_type_id, date_of_incident, time_of_incident,
            location, narrative, status_id
        ) VALUES (
            :user_id, :incident_title, :blotter_type, :complainant_name, :complainant_type,
            :complainant_gender, :complainant_contact, :complainant_birthday, :complainant_address,
            :offender_name, :offender_type, :offender_gender, :offender_address, :offender_description,
            :case_type_id, :date_of_incident, :time_of_incident,
            :location, :narrative, :status_id
        )");

        $params = [
            ':user_id' => !empty($data['user_id']) ? $data['user_id'] : null,
            ':incident_title' => $data['incident_title'] ?? null,
            ':blotter_type' => $data['blotter_type'] ?? null,
            ':complainant_name' => $data['complainant_name'],
            ':complainant_type' => $data['complainant_type'] ?? null,
            ':complainant_gender' => $data['complainant_gender'],
            ':complainant_contact' => $data['complainant_contact'] ?? null,
            ':complainant_birthday' => $data['complainant_birthday'] ?? null,
            ':complainant_address' => $data['complainant_address'] ?? null,
            ':offender_name' => $data['offender_name'] ?? null,
            ':offender_type' => $data['offender_type'] ?? null,
            ':offender_gender' => $data['offender_gender'] ?? null,
            ':offender_address' => $data['offender_address'] ?? null,
            ':offender_description' => $data['offender_description'] ?? null,
            ':case_type_id' => $caseTypeId,
            ':date_of_incident' => $data['date_of_incident'],
            ':time_of_incident' => $data['time_of_incident'] ?? '00:00',
            ':location' => $data['location'],
            ':narrative' => $data['narrative'],
            ':status_id' => $statusId
        ];

        if ($stmt->execute($params)) {
            return $this->pdo->lastInsertId();
        }
        
        return false;
    }

    /**
     * Update complaint
     */
    public function update($id, $data) {
        $caseTypeId = $this->resolveCaseTypeId($data['case_type_id'] ?? null);
        if ($caseTypeId === null) {
            throw new Exception('Case types are not configured. Please seed the case_types table.');
        }

        $stmt = $this->pdo->prepare("UPDATE {$this->table_name} SET 
            user_id = :user_id,
            incident_title = :incident_title,
            blotter_type = :blotter_type,
            complainant_name = :complainant_name,
            complainant_type = :complainant_type,
            complainant_gender = :complainant_gender,
            complainant_contact = :complainant_contact,
            complainant_birthday = :complainant_birthday,
            complainant_address = :complainant_address,
            offender_name = :offender_name,
            offender_type = :offender_type,
            offender_gender = :offender_gender,
            offender_address = :offender_address,
            offender_description = :offender_description,
            case_type_id = :case_type_id,
            date_of_incident = :date_of_incident,
            time_of_incident = :time_of_incident,
            location = :location,
            narrative = :narrative,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = :id");
        
        $params = [
            ':user_id' => !empty($data['user_id']) ? $data['user_id'] : null,
            ':incident_title' => $data['incident_title'],
            ':blotter_type' => $data['blotter_type'],
            ':complainant_name' => $data['complainant_name'],
            ':complainant_type' => $data['complainant_type'] ?? null,
            ':complainant_gender' => $data['complainant_gender'],
            ':complainant_contact' => $data['complainant_contact'] ?? null,
            ':complainant_birthday' => $data['complainant_birthday'] ?? null,
            ':complainant_address' => $data['complainant_address'] ?? null,
            ':offender_name' => $data['offender_name'] ?? null,
            ':offender_type' => $data['offender_type'] ?? null,
            ':offender_gender' => $data['offender_gender'] ?? null,
            ':offender_address' => $data['offender_address'] ?? null,
            ':offender_description' => $data['offender_description'] ?? null,
            ':case_type_id' => $caseTypeId,
            ':date_of_incident' => $data['date_of_incident'],
            ':time_of_incident' => $data['time_of_incident'] ?? '00:00',
            ':location' => $data['location'],
            ':narrative' => $data['narrative'],
            ':id' => $id
        ];

        return $stmt->execute($params);
    }

    /**
     * Update complaint status
     */
    public function updateStatus($id, $status_id) {
        // Check current status to prevent updating resolved complaints
        $current = $this->getById($id);
        if ($current && $current['status_id'] == 3) {
            throw new Exception('Cannot update status of a resolved complaint');
        }
        
        // If status is being set to Resolved (3), set resolved_at timestamp
        if ($status_id == 3) {
            $stmt = $this->pdo->prepare("UPDATE {$this->table_name} SET 
                status_id = :status_id,
                resolved_at = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :id");
        } else {
            $stmt = $this->pdo->prepare("UPDATE {$this->table_name} SET 
                status_id = :status_id,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :id");
        }
        
        return $stmt->execute([
            ':status_id' => $status_id,
            ':id' => $id
        ]);
    }

    /**
     * Mark an existing complaint as available to the Police Portal.
     */
    public function forwardToPolice($id) {
        $complaint = $this->getById($id);
        if (!$complaint) {
            return false;
        }

        $statusLabel = strtolower(trim((string) ($complaint['status_label'] ?? '')));
        if ((int) ($complaint['status_id'] ?? 0) === 3 || $statusLabel === 'resolved') {
            return false;
        }

        $stmt = $this->pdo->prepare("UPDATE {$this->table_name} SET
            forwarded_to_police = 1,
            forwarded_to_police_at = COALESCE(forwarded_to_police_at, CURRENT_TIMESTAMP),
            updated_at = CURRENT_TIMESTAMP
            WHERE id = :id");

        return $stmt->execute([':id' => $id]);
    }

    /**
     * Add forwarding columns for existing installations that predate this workflow.
     */
    private function ensurePoliceForwardingColumns() {
        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM {$this->table_name} LIKE 'forwarded_to_police'");
            if (!$stmt || !$stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->pdo->exec("ALTER TABLE {$this->table_name}
                    ADD COLUMN forwarded_to_police TINYINT(1) NOT NULL DEFAULT 0 AFTER status_id,
                    ADD COLUMN forwarded_to_police_at DATETIME DEFAULT NULL AFTER forwarded_to_police,
                    ADD KEY idx_incident_forwarded_to_police (forwarded_to_police)");
            }
        } catch (Exception $e) {
            error_log('Unable to ensure police forwarding columns: ' . $e->getMessage());
        }
    }

    /**
     * Delete complaint
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table_name} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get statistics
     */
    public function getStatistics() {
        return [
            'total' => (int) $this->pdo->query("SELECT COUNT(*) FROM {$this->table_name}")->fetchColumn(),
            'pending' => (int) $this->pdo->query("SELECT COUNT(*) FROM {$this->table_name} WHERE status_id = 1")->fetchColumn(),
            'investigating' => (int) $this->pdo->query("SELECT COUNT(*) FROM {$this->table_name} WHERE status_id = 2")->fetchColumn(),
            'resolved' => (int) $this->pdo->query("SELECT COUNT(*) FROM {$this->table_name} WHERE status_id = 3")->fetchColumn()
        ];
    }

    /**
     * Ensure a valid default status exists and return its id.
     */
    private function getDefaultStatusId() {
        $count = (int) $this->pdo->query("SELECT COUNT(*) FROM statuses")->fetchColumn();
        if ($count === 0) {
            $seed = $this->pdo->prepare("INSERT INTO statuses (label) VALUES (?), (?), (?), (?)");
            $seed->execute(['Pending', 'Investigating', 'Resolved', 'Ongoing']);
        }

        $stmt = $this->pdo->prepare("SELECT id FROM statuses WHERE LOWER(label) = 'pending' LIMIT 1");
        $stmt->execute();
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int) $id;
        }

        $fallback = $this->pdo->query("SELECT id FROM statuses ORDER BY id ASC LIMIT 1")->fetchColumn();
        return $fallback ? (int) $fallback : null;
    }

    /**
     * Resolve the provided case type id or return a safe default.
     */
    private function resolveCaseTypeId($caseTypeId) {
        $incomingId = (int) $caseTypeId;

        $count = (int) $this->pdo->query("SELECT COUNT(*) FROM case_types")->fetchColumn();
        if ($count === 0) {
            $seed = $this->pdo->prepare("INSERT INTO case_types (label) VALUES (?), (?), (?)");
            $seed->execute(['Criminal', 'Civil', 'Others']);
        }

        if ($incomingId > 0) {
            $stmt = $this->pdo->prepare("SELECT id FROM case_types WHERE id = ? LIMIT 1");
            $stmt->execute([$incomingId]);
            $id = $stmt->fetchColumn();
            if ($id) {
                return (int) $id;
            }
        }

        $labelMap = [
            1 => 'Criminal',
            2 => 'Civil',
            3 => 'Others'
        ];

        if ($incomingId > 0 && isset($labelMap[$incomingId])) {
            $stmt = $this->pdo->prepare("SELECT id FROM case_types WHERE LOWER(label) = LOWER(?) LIMIT 1");
            $stmt->execute([$labelMap[$incomingId]]);
            $id = $stmt->fetchColumn();
            if ($id) {
                return (int) $id;
            }
        }

        $fallback = $this->pdo->query("SELECT id FROM case_types ORDER BY id ASC LIMIT 1")->fetchColumn();
        return $fallback ? (int) $fallback : null;
    }

    /**
     * Get all statuses
     */
    public function getStatuses() {
        $stmt = $this->pdo->query("SELECT DISTINCT id, label FROM statuses ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all case types
     */
    public function getCaseTypes() {
        $stmt = $this->pdo->query("SELECT DISTINCT id, label FROM case_types ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
