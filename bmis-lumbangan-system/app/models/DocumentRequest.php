<?php

class DocumentRequest
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Fetch ongoing requests for a specific user
    public function getOngoingRequestsByUser($userId)
    {
        $sql = "SELECT 
                    dr.request_id, 
                    dr.document_type_id, 
                    dr.request_date, 
                    dr.status, 
                    dr.requested_for, 
                    dr.relation_to_requestee,
                    dt.document_name
                FROM document_requests dr
                INNER JOIN document_types dt 
                    ON dr.document_type_id = dt.document_type_id
                WHERE dr.user_id = ? 
                AND dr.status IN ('Pending')
                ORDER BY dr.request_date DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(1, $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO document_requests
                (user_id, document_type_id, purpose, proof_upload, requested_for, relation_to_requestee)
                VALUES (:user_id, :document_type_id, :purpose, :proof_upload, :requested_for, :relation)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':document_type_id', $data['document_type_id'], PDO::PARAM_INT);
        $stmt->bindParam(':purpose', $data['purpose']);
        $stmt->bindParam(':proof_upload', $data['proof_upload']);
        $stmt->bindParam(':requested_for', $data['requested_for']);
        $stmt->bindParam(':relation', $data['relation_to_requestee']);

        return $stmt->execute();
    }

    public function delete($requestId)
    {
        $sql = "DELETE FROM document_requests WHERE request_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $requestId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getById($requestId)
    {
        $sql = "SELECT * FROM document_requests WHERE request_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $requestId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //Fetch users whose request are approved
    public function getApprovedRequestsByUser($userId)
    {
        $sqlQuery = "SELECT 
                        dr.request_id, 
                        dr.document_type_id, 
                        dr.request_date, 
                        dr.status, 
                        dr.requested_for, 
                        dr.relation_to_requestee,
                        dr.approval_date,
                        dt.document_name
                    FROM document_requests dr
                    INNER JOIN document_types dt 
                        ON dr.document_type_id = dt.document_type_id
                    WHERE dr.user_id = ? 
                      AND dr.status = 'Approved' AND dr.approval_date IS NOT NULL
                    ORDER BY dr.request_date DESC";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(1, $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch requests with status = 'Released' for a specific user
    public function getReleasedRequestsByUser($userId)
    {
        $sql = "SELECT 
                        dr.request_id, 
                        dr.document_type_id, 
                        dr.request_date, 
                        dr.status, 
                        dr.requested_for, 
                        dr.relation_to_requestee,
                        dr.release_date,
                        dt.document_name
                    FROM document_requests dr
                    INNER JOIN document_types dt 
                        ON dr.document_type_id = dt.document_type_id
                    WHERE dr.user_id = ? 
                      AND dr.status = 'Released' AND dr.release_date IS NOT NULL
                    ORDER BY dr.request_date DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(1, $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch requests with status = 'Rejected' for a specific user
    public function getRejectedRequestsByUser($userId)
    {
        $sql = "SELECT 
                        dr.request_id, 
                        dr.document_type_id, 
                        dr.request_date, 
                        dr.status, 
                        dr.requested_for, 
                        dr.relation_to_requestee,
                        dr.remarks,
                        dt.document_name
                    FROM document_requests dr
                    INNER JOIN document_types dt 
                        ON dr.document_type_id = dt.document_type_id
                    WHERE dr.user_id = ? 
                      AND dr.status = 'Rejected'
                    ORDER BY dr.request_date DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(1, $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Fetch users whose request are rejected or released
    public function getHistoryRequestsByUser($userId)
    {
        $sqlQuery = "SELECT 
                        dr.request_id, 
                        dr.document_type_id, 
                        dr.request_date, 
                        dr.status, 
                        dr.requested_for, 
                        dr.relation_to_requestee,
                        dr.approval_date,
                        dr.remarks,
                        dt.document_name
                    FROM document_requests dr
                    INNER JOIN document_types dt 
                        ON dr.document_type_id = dt.document_type_id
                    WHERE dr.user_id = ? 
                      AND dr.status IN ('Rejected', 'Released', 'Approved')
                    ORDER BY dr.request_date DESC";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(1, $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* From this section, the method the methods for
    the admin side are placed. */

    //Fetch all document requests with documents names joined
    public function getAllRequests()
    {
        $sql = "SELECT
                dr.request_id, 
                dr.user_id,
                dr.document_type_id, 
                dr.request_date, 
                dr.status, 
                dr.requested_for, 
                dr.relation_to_requestee,
                dr.purpose,
                dr.proof_upload,
                dr.approval_date,
                dr.release_date,
                dr.pdf_file_path,
                dr.remarks,
                CONCAT(p.last_name, ', ', p.first_name, ' ', p.middle_name) AS requester_name,
                dt.document_name,
                dt.requirements
            FROM document_requests dr
            INNER JOIN document_types dt 
                ON dr.document_type_id = dt.document_type_id
            LEFT JOIN users u
                ON dr.user_id = u.id
            LEFT JOIN persons p
                ON u.person_id = p.id
            ORDER BY dr.request_date DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    private function getApprovalDate($id)
    {
        $stmt = $this->conn->prepare("SELECT approval_date FROM document_requests WHERE request_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Update request status
    public function updateStatus($requestId, $status, $remarks = null, $approvalDate = null, $releaseDate = null)
    {
        // Build SQL dynamically depending on status
        $sql = "UPDATE document_requests 
                SET status = :status,
                    remarks = :remarks, 
                    approval_date = :approval_date,
                    release_date = :release_date
                WHERE request_id = :id";

        $stmt = $this->conn->prepare($sql);

        // Determine date logic
        if ($status === "Approved") {
            $approvalDate = date('Y-m-d H:i:s');
            $releaseDate = null; // reset
        } elseif ($status === "Released") {
            $releaseDate = date('Y-m-d H:i:s');
            $approvalDate = $this->getApprovalDate($requestId);
        } else {
            // Reset both if not approved or released
            $approvalDate = null;
            $releaseDate = null;
        }

        // Bind parameters
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':remarks', $remarks);
        $stmt->bindParam(':approval_date', $approvalDate);
        $stmt->bindParam(':release_date', $releaseDate);
        $stmt->bindParam(':id', $requestId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function savePDFPath($requestId, $pdfPath)
    {
        $stmt = $this->conn->prepare("
        UPDATE document_requests 
        SET pdf_file_path = :pdfPath
        WHERE request_id = :requestId
    ");

        return $stmt->execute([
            ':pdfPath' => $pdfPath,
            ':requestId' => $requestId
        ]);
    }


    public function getRequestData($requestId)
    {
        $stmt = $this->conn->prepare("
                                    SELECT 
                                        dr.*,
                                        dt.document_name AS document_type_name,
                                        u.username,
                                        p.first_name,
                                        p.middle_name,
                                        p.last_name,
                                        p.suffix,

                                        -- Create a full name for the requester
                                        CONCAT(p.first_name, ' ',
                                            COALESCE(CONCAT(p.middle_name, ' '), ''),
                                            p.last_name,
                                            COALESCE(CONCAT(' ', p.suffix), '')
                                        ) AS requester_full_name,

                                        -- Create subject full name (if requested_for IS NOT NULL)
                                        CASE
                                            WHEN dr.requested_for IS NOT NULL AND dr.requested_for <> ''
                                            THEN dr.requested_for
                                            ELSE CONCAT(p.first_name, ' ',
                                                        COALESCE(CONCAT(p.middle_name, ' '), ''),
                                                        p.last_name,
                                                        COALESCE(CONCAT(' ', p.suffix), '')
                                            )
                                        END AS subject_full_name

                                    FROM document_requests dr
                                    JOIN document_types dt ON dr.document_type_id = dt.document_type_id
                                    JOIN users u ON dr.user_id = u.id
                                    JOIN persons p ON u.person_id = p.id
                                    WHERE dr.request_id = :request_id
                                    ");

        $stmt->execute([':request_id' => $requestId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getTemplateByRequest($requestId)
    {
        $stmt = $this->conn->prepare("
            SELECT t.template_html 
            FROM document_templates t
            JOIN document_requests dr ON t.document_type_id = dr.document_type_id
            WHERE dr.request_id = :request_id
        ");
        $stmt->execute([':request_id' => $requestId]);
        return $stmt->fetchColumn();
    }



    public function getStatusSummary()
    {
        $sql = "SELECT status, COUNT(*) AS total FROM document_requests GROUP BY status";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summary = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0, 'Released' => 0];
        foreach ($rows as $row) {
            if (isset($summary[$row['status']])) {
                $summary[$row['status']] = (int)$row['total'];
            }
        }
        return $summary;
    }


    public function fetchDocumentTypes()
    {
        $stmt = $this->conn->prepare("SELECT document_type_id, document_name FROM document_types ORDER BY document_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // PDO fetchAll
    }

    public function insertAdminRequest($data)
    {
        $sql = "INSERT INTO document_requests (user_id, requested_for, document_type_id, purpose, status)
            VALUES (:user_id, :requested_for, :document_type_id, :purpose, 'Pending')";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':user_id' => $data['user_id'], // null
            ':requested_for' => $data['requested_for'],
            ':document_type_id' => $data['document_type_id'],
            ':purpose' => $data['purpose'],
        ]);
    }

    // Update request details (user editable fields)
    public function updateDetails($requestId, $data)
    {
        $sql = "UPDATE document_requests SET
                    document_type_id = :document_type_id,
                    purpose = :purpose,
                    proof_upload = :proof_upload,
                    requested_for = :requested_for,
                    relation_to_requestee = :relation
                WHERE request_id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':document_type_id', $data['document_type_id'], PDO::PARAM_INT);
        $stmt->bindParam(':purpose', $data['purpose']);
        $stmt->bindParam(':proof_upload', $data['proof_upload']);
        $stmt->bindParam(':requested_for', $data['requested_for']);
        $stmt->bindParam(':relation', $data['relation_to_requestee']);
        $stmt->bindParam(':id', $requestId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Remove a proof file path from the request's proof_upload column
    public function removeProofFromRequest($requestId, $filePath)
    {
        // Fetch current files
        $stmt = $this->conn->prepare("SELECT proof_upload FROM document_requests WHERE request_id = :id");
        $stmt->execute([':id' => $requestId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $existing = [];
        if ($row && !empty($row['proof_upload'])) {
            $existing = array_filter(array_map('trim', explode(',', $row['proof_upload'])));
        }

        if (empty($existing)) {
            // nothing to remove
            return true;
        }

        // Remove any entries that match exact path or matching filename
        $targetBase = basename($filePath);
        $updated = array_values(array_filter($existing, function ($p) use ($filePath, $targetBase) {
            if ($p === $filePath) return false;
            if (basename($p) === $targetBase) return false;
            return true;
        }));

        $newStr = implode(',', $updated);
        $upd = $this->conn->prepare("UPDATE document_requests SET proof_upload = :proof WHERE request_id = :id");
        return $upd->execute([':proof' => $newStr, ':id' => $requestId]);
    }
}
