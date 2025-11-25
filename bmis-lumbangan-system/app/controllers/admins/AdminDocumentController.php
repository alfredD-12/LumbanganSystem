<?php

use Dompdf\Dompdf;

require_once __DIR__ . '/../../models/DocumentRequest.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . "/../../models/DocumentType.php";
require_once __DIR__ . "/../../models/DocumentCategory.php";

class AdminDocumentController
{
    private $documentRequestModel;
    private $documentTypeModel;
    private $documentCategoryModel;

    public function __construct()
    {
        $db = new Database();
        $conn = $db->getConnection();
        $this->documentRequestModel = new DocumentRequest($conn);
        $this->documentTypeModel = new DocumentType($conn);
        $this->documentCategoryModel = new DocumentCategory($conn);
    }

    // Get all request and format it to json file
    public function getAllRequests()
    {
        $requests = $this->documentRequestModel->getAllRequests();
        header('Content-Type: application/json');
        echo json_encode(['data' => $requests]);
    }

    // Update request status
    public function updateRequestStatus()
    {
        $requestId = $_POST['request_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $remarks = $_POST['remarks'] ?? null;
        $approvalDate = $_POST['approval_date'] ?? null;
        $releaseDate = $_POST['release_date'] ?? null;

        if (!$requestId || !$status) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
            return;
        }

        // Update DB status first
        $result = $this->documentRequestModel->updateStatus(
            $requestId,
            $status,
            $remarks,
            $approvalDate,
            $releaseDate
        );

        if (!$result) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
            return;
        }

        $pdfPath = null;

        try {
            // If approved → generate PDF + store PDF path in DB
            if ($result && $status === "Approved") {
                $pdfPath = $this->generateRequestPDF($requestId);

                if ($pdfPath) {
                    $this->documentRequestModel->savePDFPath($requestId, $pdfPath);
                    $pdfGenerated = true;
                }
            }
        } catch (Exception $e) {
            error_log("PDF ERROR: " . $e->getMessage());
            $pdfPath = null;
        }

        echo json_encode([
            'success' => $result,
            'pdf_generated' => ($pdfPath !== null),
            'pdf_path' => $pdfPath
        ]);
    }


    private function generateRequestPDF($requestId)
    {
        $requestData = $this->documentRequestModel->getRequestData($requestId);
        $templateHtml = $this->documentRequestModel->getTemplateByRequest($requestId);

        if (!$requestData || !$templateHtml) return null;

        // 🔹 Normalize old placeholders to use {{subject.full_name}}
        $templateHtml = str_replace(
            [
                '{{persons.first_name}} {{persons.middle_name}} {{persons.last_name}} {{persons.suffix}}',
                '{{persons.first_name}} {{persons.middle_name}} {{persons.last_name}}',
                '{{persons.first_name}} {{persons.last_name}}'
            ],
            '{{subject.full_name}}',
            $templateHtml
        );

        /*
    |--------------------------------------------------------------------------
    | NAME RESOLUTION LOGIC
    | Decide who should appear on the certificate:
    | - If requested_for is NOT NULL, use that
    | - Otherwise, use the requester full name
    |--------------------------------------------------------------------------
    */

        if (!empty($requestData['requested_for'])) {
            // EXAMPLE: "Juan Dela Cruz Jr"
            $requestData['subject_full_name'] = $requestData['requested_for'];
        } else {
            // Build the requester’s full name
            $requestData['subject_full_name'] = trim(
                ($requestData['first_name'] ?? '') . ' ' .
                    ($requestData['middle_name'] ?? '') . ' ' .
                    ($requestData['last_name'] ?? '') . ' ' .
                    ($requestData['suffix'] ?? '')
            );
        }

        // Also prepare requester_full_name if needed in template
        $requestData['requester_full_name'] = trim(
            ($requestData['first_name'] ?? '') . ' ' .
                ($requestData['middle_name'] ?? '') . ' ' .
                ($requestData['last_name'] ?? '') . ' ' .
                ($requestData['suffix'] ?? '')
        );

        /*
    |--------------------------------------------------------------------------
    | TEMPLATE PLACEHOLDER REPLACEMENT
    |--------------------------------------------------------------------------
    */
        $renderedHtml = preg_replace_callback(
            '/{{([a-z_]+)\.([a-z_]+)}}/',
            function ($matches) use ($requestData) {

                // Exclusive placeholders handled first
                if ($matches[0] === '{{subject.full_name}}') {
                    return $requestData['subject_full_name'] ?? '';
                }

                if ($matches[0] === '{{requester.full_name}}') {
                    return $requestData['requester_full_name'] ?? '';
                }

                // automatic fallback (existing logic)
                $column = $matches[2];
                return $requestData[$column] ?? '';
            },
            $templateHtml
        );

        /*
    |--------------------------------------------------------------------------
    | DOMPDF RENDERING
    |--------------------------------------------------------------------------
    */
        $dompdf = new Dompdf();
        $dompdf->loadHtml($renderedHtml);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $outputPath = __DIR__ . "/../../../public/uploads/generated_pdfs/request_{$requestId}.pdf";
        file_put_contents($outputPath, $dompdf->output());

        return "uploads/generated_pdfs/request_{$requestId}.pdf";
    }



    // Load view for admin document requests
    public function showAdminDocumentRequestsPage()
    {
        include __DIR__ . '/../../views/admins/document_request_admin.php';
    }

    public function getStatusSummary()
    {
        $summary = $this->documentRequestModel->getStatusSummary();
        echo json_encode($summary);
    }

    public function loadDocumentTypesView()
    {

        require_once __DIR__ . '/../../views/admins/document_type.php';
    }

    public function getDocumentTypesData()
    {
        header('Content-Type: application/json');

        $documentTypes = $this->documentTypeModel->getAllWithCategory();
        $categories = $this->documentCategoryModel->getAllCategories();

        echo json_encode([
            "types" => $documentTypes,
            "categories" => $categories
        ]);
    }

    // Fetch single document type
    public function getDocumentType()
    {
        $id = $_GET['id'];
        $type = $this->documentTypeModel->getDocumentTypeById($id);
        echo json_encode($type);
    }

    // Update document type
    public function updateDocumentType()
    {
        $data = $_POST; // document_name, description, requirements, fee, category_id
        $id = $data['document_type_id'];
        $success = $this->documentTypeModel->updateDocumentType($id, $data);
        echo json_encode(['success' => $success]);
    }

    // Delete document type
    public function deleteDocumentType()
    {
        $id = $_POST['id'];
        $success = $this->documentTypeModel->deleteDocumentType($id);
        echo json_encode(['success' => $success]);
    }

    public function getDocumentCategories()
    {
        $categories = $this->documentCategoryModel->getAllCategories();
        header('Content-Type: application/json');
        echo json_encode($categories);
    }

    // Insert a new document type
    public function addDocumentType()
    {
        header("Content-Type: application/json");

        if (!isset($_POST['document_name'], $_POST['category_id'])) {
            echo json_encode(["success" => false, "error" => "Missing fields"]);
            return;
        }

        $documentName = trim($_POST['document_name']);
        $categoryId   = intval($_POST['category_id']);
        $description  = $_POST['description'] ?? null;
        $requirements = $_POST['requirements'] ?? null;
        $fee          = $_POST['fee'] ?? 0;

        $result = $this->documentTypeModel->insertDocumentType(
            $categoryId,
            $documentName,
            $description,
            $requirements,
            $fee
        );

        if ($result) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "DB insert failed"]);
        }
    }
    //Add admin request from admin side
    public function addAdminRequest()
    {
        $data = [
            'user_id' => null, // Walk-in resident has no user_id
            'requested_for' => $_POST['requested_for'],
            'document_type_id' => $_POST['document_type_id'],
            'purpose' => $_POST['purpose'],
        ];

        $result = $this->documentRequestModel->insertAdminRequest($data);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Inserted successfully' : 'Insert failed'
        ]);
    }

    public function getDocumentTypes()
    {
        echo json_encode($this->documentRequestModel->fetchDocumentTypes());
    }
}
