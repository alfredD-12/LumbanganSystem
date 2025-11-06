<?php
require_once __DIR__ . '/../../models/DocumentRequest.php';
require_once __DIR__ . '/../../config/Database.php';

class AdminDocumentController {
    private $documentRequestModel;

    public function __construct() {
        $db = new Database();
        $conn = $db->getConnection();
        $this->documentRequestModel = new DocumentRequest($conn);

    }

    // Get all request and format it to json file
    public function getAllRequests() {
        $requests = $this->documentRequestModel->getAllRequests();
        header('Content-Type: application/json');
        echo json_encode(['data' => $requests]);
    }

    // Update request status
    public function updateRequestStatus() {
        $requestId = $_POST['request_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $remarks = $_POST['remarks'] ?? null;

        if ($requestId && $status) {
            $result = $this->documentRequestModel->updateStatus($requestId, $status, $remarks);
            echo json_encode(['success' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
        }
    }

    // Load view for admin document requests
    public function showAdminDocumentRequestsPage() {
        include __DIR__ . '/../../views/admins/document_request_admin.php';
    }
}