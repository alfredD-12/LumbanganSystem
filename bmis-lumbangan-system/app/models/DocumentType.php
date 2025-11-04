<?php


class DocumentType {
    private $conn;
    private $table = 'document_types';

    // Table columns
    public $document_type_id;
    public $document_name;
    public $description;
    public $requirements;
    public $fee;

    // Constructor: get database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Fetch all document types
     */
    public function readAll() {
        $query = "SELECT document_type_id, document_name FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getRequirementsByTypeId($id) {
    $sql = "SELECT requirements FROM document_types WHERE document_type_id = :id LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['requirements'] ?? '';
    }


}