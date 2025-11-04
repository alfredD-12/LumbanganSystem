<?php

class DocumentRequest {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Fetch ongoing requests for a specific user
    public function getOngoingRequestsByUser($userId) { 
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

    public function create($data) {
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

    public function delete($requestId) {
    $sql = "DELETE FROM document_requests WHERE request_id = :id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $requestId, PDO::PARAM_INT);
    return $stmt->execute();
    }

    public function getById($requestId) {
    $sql = "SELECT * FROM document_requests WHERE request_id = :id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $requestId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //Fetch users whose request are approved
    public function getApprovedRequestsByUser($userId) {
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

    //Fetch users whose request are rejected or released
    public function getHistoryRequestsByUser($userId) {
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



}
