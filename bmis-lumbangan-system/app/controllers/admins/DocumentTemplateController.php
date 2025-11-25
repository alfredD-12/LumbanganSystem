<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/DocumentTemplate.php';
require_once __DIR__ . '/../../models/DocumentType.php';

class DocumentTemplateController
{

    private $conn;
    private $documentTemplateModel;
    private $documentTypeModel;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->documentTemplateModel = new DocumentTemplate($this->conn);
        $this->documentTypeModel = new DocumentType($this->conn);
    }

    public function showDocTemplateView()
    {
        require __DIR__ . '/../../views/admins/document_template.php';
    }

    public function getDocumentTypes()
    {

        $types = $this->documentTypeModel->readAll()->fetchAll(PDO::FETCH_ASSOC) ?? [];

        echo json_encode($types);
    }

    public function editTemplate()
    {
        $typeId = $_GET['type_id'] ?? null;
        $template = $this->documentTemplateModel->getTemplate($typeId);

        require __DIR__ . './../../views/admins/document_template.php';
    }

    public function saveTemplate()
    {

        $typeId = $_POST['document_type_id'] ?? null;
        $html = $_POST['template_html'] ?? null;

        $success = $this->documentTemplateModel->saveTemplate($typeId, $html);

        echo json_encode([
            "success" => $success,
            "message" => $success ? "Template saved successfully." : "Failed to save template."
        ]);
    }

    public function getTemplatePlaceholders()
    {
        //set the columns to exclude
        $exclude = [
            "users" => ["password_hash", "status", "last_login_at", "created_at", "updated_at"],
            "document_requests" => ["remarks", "proof_upload"],
            "document_types" => [],
            "persons" => [
                "family_id",
                "created_at",
                "updated_at",
                "is_deceased",
                "is_pregnant",
                "religion",
                "occupation",
                "highest_educ_attainment",
                "disability",
                "blood_type"
            ],
        ];

        $placeholders = [];

        foreach ($exclude as $table => $excludedColumns) {
            $columns = $this->documentTemplateModel->getColumns($table);


            $columns = array_values(array_filter($columns, function ($col) use ($excludedColumns) {
                return !in_array($col, $excludedColumns);
            }));

            $placeholders[$table] = $columns;
        }
        echo json_encode($placeholders);
    }

    public function getTemplates()
    {
        $templates = $this->documentTemplateModel->getAllTemplates();

        echo json_encode($templates);
    }
}
