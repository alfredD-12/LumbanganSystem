<?php

class DocumentCategory
{
    private $conn;
    private $table = 'document_categories';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllCategories()
    {
        $sql = "SELECT * FROM document_categories ORDER BY category_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
