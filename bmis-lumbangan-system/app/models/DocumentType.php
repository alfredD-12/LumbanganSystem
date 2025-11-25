<?php


class DocumentType
{
    private $conn;
    private $table = 'document_types';

    // Table columns
    public $document_type_id;
    public $document_name;
    public $description;
    public $requirements;
    public $fee;

    // Constructor: get database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Fetch all document types
     */
    public function readAll()
    {
        $query = "SELECT document_type_id, document_name FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getRequirementsByTypeId($id)
    {
        $sql = "SELECT requirements FROM document_types WHERE document_type_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['requirements'] ?? '';
    }

    public function getAllWithCategory()
    {
        $sql = "
            SELECT dt.*, dc.category_name
            FROM document_types dt
            LEFT JOIN document_categories dc
                ON dc.category_id = dt.category_id
            ORDER BY dt.document_type_id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch single document type
    public function getDocumentTypeById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM document_types WHERE document_type_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update document type
    public function updateDocumentType($id, $data)
    {
        $stmt = $this->conn->prepare("
            UPDATE document_types SET 
            document_name = ?, description = ?, requirements = ?, fee = ?, category_id = ?
            WHERE document_type_id = ?
        ");
        return $stmt->execute([
            $data['document_name'],
            $data['description'],
            $data['requirements'],
            $data['fee'],
            $data['category_id'],
            $id
        ]);
    }

    // Delete document type
    public function deleteDocumentType($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM document_types WHERE document_type_id = ?");
        return $stmt->execute([$id]);
    }

    public function insertDocumentType($categoryId, $name, $description, $requirements, $fee)
    {
        try {
            $sql = "INSERT INTO document_types 
                  (category_id, document_name, description, requirements, fee)
                VALUES (?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$categoryId, $name, $description, $requirements, $fee]);
        } catch (PDOException $e) {
            error_log("Insert document_type error: " . $e->getMessage());
            return false;
        }
    }
}
