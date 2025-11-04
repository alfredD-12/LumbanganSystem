<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/DocumentType.php';
require_once __DIR__ . '/../models/DocumentRequest.php';




class DocumentRequestController {
    private $db;
    private $documentRequest;
    private $documentType;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->documentRequest = new DocumentRequest($this->db);
        $this->documentType = new DocumentType($this->db);
    }

    public function showRequestForm() {
        session_start();

        // Ensure only logged-in users can access this
        // if (!isset($_SESSION['user_id'])) {
        //     header("Location: login.php");
        //     exit;
        // }

        $userId = 1;

        // Fetch all document types for the dropdown
        $stmt = $this->documentType->readAll();
        $documentTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch ongoing document requests specific to the logged-in user
        $requests = $this->documentRequest->getOngoingRequestsByUser($userId);

        // Load the view and pass the data
        include __DIR__ . '/../views/resident/document_request.php';
    }

    public function getRequirements() {
    header('Content-Type: application/json');

    if (!isset($_GET['document_type_id'])) {
        echo json_encode(['requirements' => []]);
        exit;
    }

    $docTypeId = intval($_GET['document_type_id']);

    $row = $this->documentType->getRequirementsByTypeId($docTypeId);

    // The model returns a string like "Valid ID, Barangay Clearance, ...", split it
    $requirements = $row ? array_map('trim', explode(',', $row)) : [];

    echo json_encode(['requirements' => $requirements]);
    exit;
    }

    public function submitRequest() {
    header('Content-Type: application/json');

    // 1️⃣ Basic validation
    if (!isset($_POST['document_type_id'], $_POST['purpose'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    $userId = 1; // Replace with session user ID when authentication is ready
    $documentTypeId = intval($_POST['document_type_id']);
    $purpose = trim($_POST['purpose']);
    $requestedFor = $_POST['requested_for'] ?? null;
    $relation = $_POST['relation_to_requestee'] ?? null;

    // 2️⃣ Handle uploaded files
    $uploadedPaths = [];
    if (!empty($_FILES['proof_upload'])) {
        $files = $_FILES['proof_upload'];

        $uploadDir = __DIR__ . '/../uploads/proofs/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        for ($i = 0; $i < count($files['name']); $i++) {
            $tmpName = $files['tmp_name'][$i];
            $name = basename($files['name'][$i]);
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $uniqueName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $name);
            $dest = $uploadDir . $uniqueName;

            if (move_uploaded_file($tmpName, $dest)) {
                $uploadedPaths[] = 'uploads/proofs/' . $uniqueName; // relative path for DB
            }
        }
    }

    $proofUploadStr = implode(',', $uploadedPaths);

    // 3️⃣ Insert into DB via DocumentRequest model
    $inserted = $this->documentRequest->create([
        'user_id' => $userId,
        'document_type_id' => $documentTypeId,
        'purpose' => $purpose,
        'proof_upload' => $proofUploadStr,
        'requested_for' => $requestedFor,
        'relation_to_requestee' => $relation
    ]);

    if ($inserted) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save request.']);
    }
    
    }

    public function getOngoingRequestsAjax() {
    header('Content-Type: application/json');

    $userId = 1; // Replace with session user ID
    $requests = $this->documentRequest->getOngoingRequestsByUser($userId);

    echo json_encode($requests);
    }

    // Get approved document requests by user
    public function getApprovedRequestsByUser() {
        header('Content-Type: application/json');

        $userId = 1; // Replace with session user ID
        $requests = $this->documentRequest->getApprovedRequestsByUser($userId);

        echo json_encode($requests);
    }

    public function deleteRequest() {
    header('Content-Type: application/json');
    session_start();

    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['request_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing request ID']);
        return;
    }

    $requestId = intval($input['request_id']);
    $userId = 1; // logged-in user

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        return;
    }

    // 1. Get the request
    $request = $this->documentRequest->getById($requestId);

    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found']);
        return;
    }

    // 2. Verify the request belongs to logged-in user
    if ($request['user_id'] != $userId) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    // 3. Delete uploaded files
    if (!empty($request['proof_upload'])) {
        $files = explode(',', $request['proof_upload']);
        foreach ($files as $file) {
            $filePath = __DIR__ . '/../' . trim($file); // use relative path from DB
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    // 4. Delete the database record
    if ($this->documentRequest->delete($requestId)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete request']);
    }
}

    // Fetch request history with status released or rejected
    public function getRequestHistoryByUser() {
        header('Content-Type: application/json');

        $userId = 1; // Replace with session user ID
        $requests = $this->documentRequest->getHistoryRequestsByUser($userId);

        echo json_encode($requests);
    }





    
}
