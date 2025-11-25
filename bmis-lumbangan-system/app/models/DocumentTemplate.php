<?php

class DocumentTemplate
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getTemplate($typeId)
    {

        $query = "SELECT * FROM document_templates WHERE type_id = :type_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':type_id', $typeId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveTemplate($typeId, $html)
    {
        $query = "INSERT INTO document_templates (document_type_id, template_html)
                  VALUES (:document_type_id, :template_html)
                  ON DUPLICATE KEY UPDATE template_html = :template_html_update";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':document_type_id', $typeId);
        $stmt->bindParam(':template_html', $html);
        $stmt->bindParam(':template_html_update', $html);

        return $stmt->execute();
    }

    public function getColumns($tableName)
    {
        $query = "SHOW COLUMNS FROM " . $tableName;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
    }

    public function getAllTemplates()
    {
        $query = "SELECT dt.document_type_id, dt.document_name, t.template_html
                    FROM document_types dt
                    LEFT JOIN document_templates t 
                        ON dt.document_type_id = t.document_type_id
                    WHERE t.template_html IS NOT NULL
                    ORDER BY dt.document_name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
