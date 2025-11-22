<?php
require_once __DIR__ . '/../models/Complaint.php';

class AdminController {
    private $complaintModel;

    public function __construct() {
        $this->complaintModel = new Complaint();
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
