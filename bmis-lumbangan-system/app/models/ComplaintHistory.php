<?php
require_once __DIR__ . '/../config/Database.php';

class ComplaintHistory
{
    private $pdo;
    private $table = 'complaint_history';

    public function __construct()
    {
        $database = new Database();
        $this->pdo = $database->getConnection();
        $this->ensureTableExists();
    }

    public function logStatusChange($complaintId, $previousStatusId, $updatedStatusId, $updatedByOfficialId, $remarks = null)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (complaint_id, previous_status_id, updated_status_id, updated_by_official_id, remarks)
             VALUES (:complaint_id, :previous_status_id, :updated_status_id, :updated_by_official_id, :remarks)"
        );

        return $stmt->execute([
            ':complaint_id' => (int) $complaintId,
            ':previous_status_id' => $previousStatusId !== null ? (int) $previousStatusId : null,
            ':updated_status_id' => (int) $updatedStatusId,
            ':updated_by_official_id' => (int) $updatedByOfficialId,
            ':remarks' => ($remarks !== null && trim((string) $remarks) !== '') ? trim((string) $remarks) : null,
        ]);
    }

    public function getAll($filters = [])
    {
        $query = "SELECT h.id,
                         h.complaint_id,
                         i.complainant_name,
                         i.blotter_type,
                         ct.label AS case_type,
                         h.previous_status_id,
                         prev.label AS previous_status_label,
                         h.updated_status_id,
                         cur.label AS updated_status_label,
                         h.updated_by_official_id,
                         o.full_name AS updated_by_name,
                         h.remarks,
                         h.created_at
                  FROM {$this->table} h
                  LEFT JOIN incidents i ON i.id = h.complaint_id
                  LEFT JOIN case_types ct ON ct.id = i.case_type_id
                  LEFT JOIN statuses prev ON prev.id = h.previous_status_id
                  LEFT JOIN statuses cur ON cur.id = h.updated_status_id
                  LEFT JOIN officials o ON o.id = h.updated_by_official_id
                  WHERE COALESCE(i.forwarded_to_police, 0) = 1";

        $params = [];

        if (isset($filters['search']) && is_string($filters['search']) && trim($filters['search']) !== '') {
            $search = trim((string) $filters['search']);
            $like = '%' . $search . '%';
            $query .= " AND (CAST(h.complaint_id AS CHAR) LIKE :search_id OR i.complainant_name LIKE :search_name)";
            $params[':search_id'] = $like;
            $params[':search_name'] = $like;
        }

        if (isset($filters['status_id']) && is_string($filters['status_id']) && trim($filters['status_id']) !== '') {
            $query .= " AND h.updated_status_id = :status_id";
            $params[':status_id'] = trim((string) $filters['status_id']);
        }

        if (isset($filters['from']) && is_string($filters['from']) && trim($filters['from']) !== '') {
            $query .= " AND h.created_at >= :from";
            $params[':from'] = trim((string) $filters['from']) . ' 00:00:00';
        }

        if (isset($filters['to']) && is_string($filters['to']) && trim($filters['to']) !== '') {
            $query .= " AND h.created_at <= :to";
            $params[':to'] = trim((string) $filters['to']) . ' 23:59:59';
        }

        $query .= ' ORDER BY h.created_at DESC';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function ensureTableExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            complaint_id INT NOT NULL,
            previous_status_id INT DEFAULT NULL,
            updated_status_id INT NOT NULL,
            updated_by_official_id BIGINT UNSIGNED NOT NULL,
            remarks TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_complaint_history_complaint (complaint_id),
            KEY idx_complaint_history_updated_status (updated_status_id),
            KEY idx_complaint_history_updated_by (updated_by_official_id),
            KEY idx_complaint_history_created_at (created_at),
            CONSTRAINT fk_complaint_history_incident FOREIGN KEY (complaint_id) REFERENCES incidents (id) ON DELETE CASCADE,
            CONSTRAINT fk_complaint_history_prev_status FOREIGN KEY (previous_status_id) REFERENCES statuses (id) ON DELETE SET NULL,
            CONSTRAINT fk_complaint_history_new_status FOREIGN KEY (updated_status_id) REFERENCES statuses (id) ON DELETE RESTRICT,
            CONSTRAINT fk_complaint_history_official FOREIGN KEY (updated_by_official_id) REFERENCES officials (id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->pdo->exec($sql);
    }
}
