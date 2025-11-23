<?php
require_once __DIR__ . '/../models/Complaint.php';

class AdminController {
    private $complaintModel;

    public function __construct() {
        $this->complaintModel = new Complaint();
    }

    /**
     * AJAX: Get single complaint by id
     */
    public function getComplaint() {
        header('Content-Type: application/json');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No ID provided']);
            return;
        }

        try {
            $complaint = $this->complaintModel->getById($id);
            if ($complaint) {
                echo json_encode($complaint);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Complaint not found']);
            }
        } catch (Exception $e) {
            error_log('Error in AdminController::getComplaint: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Create new complaint
     */
    public function createComplaint() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $id = $this->complaintModel->create($_POST);
            if ($id) {
                $record = $this->complaintModel->getById($id);
                echo json_encode(['success' => true, 'message' => 'Complaint created successfully', 'data' => $record]);
            } else {
                throw new Exception('Failed to create complaint');
            }
        } catch (Exception $e) {
            error_log('Error in AdminController::createComplaint: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Update existing complaint
     */
    public function updateComplaint() {
        header('Content-Type: application/json');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No ID provided']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $updated = $this->complaintModel->update($id, $_POST);
            if ($updated) {
                $record = $this->complaintModel->getById($id);
                echo json_encode(['success' => true, 'message' => 'Complaint updated successfully', 'data' => $record]);
            } else {
                throw new Exception('Failed to update complaint');
            }
        } catch (Exception $e) {
            error_log('Error in AdminController::updateComplaint: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Delete complaint
     */
    public function deleteComplaint() {
        header('Content-Type: application/json');

        // Accept POSTed id or GET id
        $id = $_POST['id'] ?? $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'No ID provided']);
            return;
        }

        try {
            if ($this->complaintModel->delete($id)) {
                echo json_encode(['success' => true, 'message' => 'Complaint deleted']);
            } else {
                throw new Exception('Failed to delete complaint');
            }
        } catch (Exception $e) {
            error_log('Error in AdminController::deleteComplaint: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Update complaint status
     */
    public function updateComplaintStatus() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $id = $_POST['id'] ?? null;
        $status_id = $_POST['status_id'] ?? null;

        if (!$id || !$status_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            return;
        }

        try {
            if ($this->complaintModel->updateStatus($id, $status_id)) {
                $stats = $this->complaintModel->getStatistics();
                echo json_encode(['success' => true, 'message' => 'Status updated', 'stats' => $stats]);
            } else {
                throw new Exception('Failed to update status');
            }
        } catch (Exception $e) {
            error_log('Error in AdminController::updateComplaintStatus: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Display admin dashboard (complaint list page for admin/staff view)
     */
    public function index() {
        try {
            // Sanitize and prepare filters
            $filters = [
                'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
                'status_id' => isset($_GET['status_id']) ? trim($_GET['status_id']) : '',
                'case_type_id' => isset($_GET['case_type_id']) ? trim($_GET['case_type_id']) : ''
            ];

            $complaints = $this->complaintModel->getAll($filters);
            $statistics = $this->complaintModel->getStatistics();
            $statuses = $this->complaintModel->getStatuses();
            $caseTypes = $this->complaintModel->getCaseTypes();

            // Load admin view
            require_once __DIR__ . '/../views/complaint/admin.php';
        } catch (Exception $e) {
            error_log("Error in AdminController::index: " . $e->getMessage());
            die("Error loading admin dashboard: " . $e->getMessage());
        }
    }

    /**
     * Display resident dashboard (public-facing complaint browsing)
     */
    public function residentDashboard() {
        try {
            $complaints = $this->complaintModel->getAll();
            $statistics = $this->complaintModel->getStatistics();
            $statuses = $this->complaintModel->getStatuses();
            $caseTypes = $this->complaintModel->getCaseTypes();

            // Load resident view
            require_once __DIR__ . '/../views/residents/residents.php';
        } catch (Exception $e) {
            error_log("Error in AdminController::residentDashboard: " . $e->getMessage());
            die("Error loading resident dashboard");
        }
    }

    /**
     * AJAX: Return filtered complaints as JSON
     */
    public function filterComplaints() {
        try {
            $filters = [
                'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
                'status_id' => isset($_GET['status_id']) ? trim($_GET['status_id']) : '',
                'case_type_id' => isset($_GET['case_type_id']) ? trim($_GET['case_type_id']) : ''
            ];

            $results = $this->complaintModel->getAll($filters);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $results]);
        } catch (Exception $e) {
            error_log("Error in AdminController::filterComplaints: " . $e->getMessage());
            header('Content-Type: application/json', true, 500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
