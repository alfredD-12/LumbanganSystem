<?php
require_once __DIR__ . '/../models/Complaint.php';

class ResidentController {
    private $complaintModel;

    public function __construct() {
        $this->complaintModel = new Complaint();
    }

    /**
     * Display resident complaints page
     */
    public function index() {
        try {
            // Get filter parameters
            $filters = [
                'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
                'status_id' => isset($_GET['status_id']) ? trim($_GET['status_id']) : '',
                'case_type_id' => isset($_GET['case_type_id']) ? trim($_GET['case_type_id']) : ''
            ];

            // Get data from model
            $complaints = $this->complaintModel->getAll($filters);
            $statistics = $this->complaintModel->getStatistics();
            $statuses = $this->complaintModel->getStatuses();
            $caseTypes = $this->complaintModel->getCaseTypes();

            // Load view
            require_once __DIR__ . '/../views/residents/residents.php';
        } catch (Exception $e) {
            error_log('Error in ResidentController::index: ' . $e->getMessage());
            // Provide fallback data on error
            $complaints = [];
            $statistics = ['total' => 0, 'pending' => 0, 'investigating' => 0, 'resolved' => 0];
            $statuses = [];
            $caseTypes = [];
            require_once __DIR__ . '/../views/residents/residents.php';
        }
    }

    /**
     * Get complaint details (AJAX)
     */
    public function getDetails() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No ID provided']);
            return;
        }

        try {
            $complaint = $this->complaintModel->getById($_GET['id']);
            
            if ($complaint) {
                echo json_encode($complaint);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Complaint not found']);
            }
        } catch (Exception $e) {
            error_log('Error in ResidentController::getDetails: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Create or update complaint (AJAX)
     */
    public function save() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            // Check if this is an update
            if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
                // Validate required fields
                $required_fields = [
                    'incident_title', 'blotter_type', 'complainant_name',
                    'complainant_gender', 'date_of_incident', 'location',
                    'narrative', 'case_type_id'
                ];

                foreach ($required_fields as $field) {
                    if (!isset($_POST[$field]) || empty($_POST[$field])) {
                        throw new Exception("Missing required field: $field");
                    }
                }

                // Update
                if ($this->complaintModel->update($_GET['id'], $_POST)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Complaint updated successfully'
                    ]);
                } else {
                    throw new Exception("Failed to update complaint");
                }
            } else {
                // Create new
                $id = $this->complaintModel->create($_POST);
                
                if ($id) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Complaint created successfully',
                        'id' => $id
                    ]);
                } else {
                    throw new Exception("Failed to create complaint");
                }
            }
        } catch (Exception $e) {
            error_log("Error in ResidentController::save: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update complaint status (AJAX)
     */
    public function updateStatus() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            if (!isset($_POST['incident_id']) || !isset($_POST['status_id'])) {
                throw new Exception('Missing required parameters');
            }

            if ($this->complaintModel->updateStatus($_POST['incident_id'], $_POST['status_id'])) {
                $stats = $this->complaintModel->getStatistics();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Status updated successfully',
                    'stats' => $stats
                ]);
            } else {
                throw new Exception("Failed to update status");
            }
        } catch (Exception $e) {
            error_log("Error in ResidentController::updateStatus: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete complaint (AJAX)
     */
    public function delete() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'error' => 'No ID provided']);
            return;
        }

        try {
            if ($this->complaintModel->delete($_GET['id'])) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Error deleting complaint");
            }
        } catch (Exception $e) {
            error_log("Error in ResidentController::delete: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
