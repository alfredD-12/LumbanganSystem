<?php
require_once __DIR__ . '/../models/Complaint.php';

class AdminController {
    private $complaintModel;

    public function __construct() {
        $this->complaintModel = new Complaint();
    }

    /**
     * AJAX: Get currently logged-in official profile
     */
    public function getOfficialProfile() {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['official_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in as official']);
            return;
        }

        require_once __DIR__ . '/../config/Database.php';
        require_once __DIR__ . '/../models/Official.php';

        $db = (new Database())->getConnection();
        $official = (new Official($db))->getById($_SESSION['official_id']);

        if ($official) echo json_encode(['success' => true, 'data' => $official]);
        else echo json_encode(['success' => false, 'message' => 'Official not found']);
    }

    /**
     * AJAX: Update official profile
     */
    public function updateOfficialProfile() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $official_id = $_SESSION['official_id'] ?? ($_POST['official_id'] ?? null);
        if (!$official_id) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            return;
        }

        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact_no = trim($_POST['contact_no'] ?? '');

        // Basic validation
        if ($full_name === '') {
            echo json_encode(['success' => false, 'message' => 'Full name is required']);
            return;
        }

        require_once __DIR__ . '/../config/Database.php';
        require_once __DIR__ . '/../models/Official.php';

        $db = (new Database())->getConnection();
        $officialModel = new Official($db);

        try {
            $ok = $officialModel->updateProfile($official_id, [
                'full_name' => $full_name,
                'email' => $email,
                'contact_no' => $contact_no
            ]);

            if ($ok) {
                // Update session full name if exists
                $_SESSION['full_name'] = $full_name;
                echo json_encode(['success' => true, 'message' => 'Profile updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No changes made or update failed']);
            }
        } catch (Exception $e) {
            error_log('Error updating official profile: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
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

    /**
     * AJAX: List all officials (returns JSON)
     */
    public function listOfficials() {
        header('Content-Type: application/json');
        require_once __DIR__ . '/../config/Database.php';
        require_once __DIR__ . '/../models/Official.php';

        $db = (new Database())->getConnection();
        $model = new Official($db);
        $rows = $model->getAll(true);

        // Group by role
        $grouped = [];
        foreach ($rows as $r) {
            $role = $r['role'] ?? 'Unspecified';
            if (!isset($grouped[$role])) $grouped[$role] = [];
            $grouped[$role][] = $r;
        }

        echo json_encode(['success' => true, 'data' => $grouped]);
    }

    /**
     * AJAX: Get single official by id
     */
    public function getOfficial() {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? null;
        if (!$id) { echo json_encode(['success'=>false,'message'=>'No id']); return; }
        require_once __DIR__ . '/../config/Database.php';
        require_once __DIR__ . '/../models/Official.php';
        $db = (new Database())->getConnection();
        $model = new Official($db);
        $row = $model->getById($id);
        if ($row) echo json_encode(['success'=>true,'data'=>$row]);
        else echo json_encode(['success'=>false,'message'=>'Not found']);
    }

    /**
     * AJAX: Create official
     */
    public function createOfficial() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Invalid method']); return; }
        $full_name = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        // Password should be same as username per requirements
        $password = $username;
        $role = trim($_POST['role'] ?? '');
        // allow submitting other role via other_role field
        if (strtolower($role) === 'other' || strtolower($role) === 'others') {
            $other = trim($_POST['other_role'] ?? '');
            if ($other !== '') $role = $other;
        }
        $email = trim($_POST['email'] ?? '');
        $contact_no = trim($_POST['contact_no'] ?? '');

        if ($full_name === '' || $username === '') { echo json_encode(['success'=>false,'message'=>'Missing required fields']); return; }

        require_once __DIR__ . '/../config/Database.php';
        require_once __DIR__ . '/../models/Official.php';
        require_once __DIR__ . '/../models/User.php';

        $db = (new Database())->getConnection();
        $model = new Official($db);
        $userModel = new User($db);

        // Server-side uniqueness checks
        try {
            // username must not exist in officials or users
            if ($model->usernameExists($username) || $userModel->usernameExists($username)) {
                echo json_encode(['success'=>false,'field'=>'username','message'=>'Username already exists']);
                return;
            }

            // email uniqueness within officials (if provided)
            if ($email !== '' && $model->emailExists($email)) {
                echo json_encode(['success'=>false,'field'=>'email','message'=>'Email already used by another official']);
                return;
            }

            // contact uniqueness within officials (if provided)
            if ($contact_no !== '' && $model->contactExists($contact_no)) {
                echo json_encode(['success'=>false,'field'=>'contact_no','message'=>'Contact number already used by another official']);
                return;
            }

            // Enforce single-instance roles for certain exclusive roles
            $exclusiveRoles = [
                'barangay captain',
                'barangay secretary',
                'barangay health worker president'
            ];
            $normalizedRole = strtolower(trim($role));
            if (in_array($normalizedRole, $exclusiveRoles, true)) {
                // check existing active officials for this role
                $all = $model->getAll(true);
                foreach ($all as $r) {
                    if (isset($r['role']) && strcasecmp(trim($r['role']), $role) === 0) {
                        echo json_encode(['success'=>false,'field'=>'role','message'=>'An official with this role already exists']);
                        return;
                    }
                }
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $id = $model->create([ 'full_name'=>$full_name, 'username'=>$username, 'password_hash'=>$hash, 'role'=>$role, 'email'=>$email, 'contact_no'=>$contact_no ]);
            if ($id) echo json_encode(['success'=>true,'message'=>'Official created','id'=>$id]);
            else echo json_encode(['success'=>false,'message'=>'Create failed']);
        } catch (Exception $e) { error_log('createOfficial: '.$e->getMessage()); echo json_encode(['success'=>false,'message'=>'Server error']); }
    }

    /**
     * AJAX: Check availability for a field (username/email/contact_no)
     */
    public function checkAvailability() {
        header('Content-Type: application/json');
        $field = $_GET['field'] ?? $_POST['field'] ?? null;
        $value = $_GET['value'] ?? $_POST['value'] ?? null;
        if (!$field || !$value) { echo json_encode(['success'=>false,'message'=>'Missing parameters']); return; }

        require_once __DIR__ . '/../config/Database.php';
        require_once __DIR__ . '/../models/Official.php';
        require_once __DIR__ . '/../models/User.php';

        $db = (new Database())->getConnection();
        $official = new Official($db);
        $user = new User($db);

        $field = strtolower($field);
        if ($field === 'username') {
            $exists = $official->usernameExists($value) || $user->usernameExists($value);
            echo json_encode(['success'=>true,'available'=>!$exists]);
            return;
        }
        if ($field === 'email') {
            $exists = $official->emailExists($value);
            echo json_encode(['success'=>true,'available'=>!$exists]);
            return;
        }
        if ($field === 'contact_no' || $field === 'contact') {
            $exists = $official->contactExists($value);
            echo json_encode(['success'=>true,'available'=>!$exists]);
            return;
        }

        echo json_encode(['success'=>false,'message'=>'Unsupported field']);
    }

    /**
     * AJAX: Admin updates an official (by id)
     */
    public function updateOfficialAdmin() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Invalid method']); return; }
        $id = $_POST['id'] ?? null;
        if (!$id) { echo json_encode(['success'=>false,'message'=>'Missing id']); return; }

        $data = [];
        if (isset($_POST['full_name'])) $data['full_name'] = trim($_POST['full_name']);
        if (isset($_POST['username'])) $data['username'] = trim($_POST['username']);
        if (isset($_POST['role'])) $data['role'] = trim($_POST['role']);
        if (isset($_POST['email'])) $data['email'] = trim($_POST['email']);
        if (isset($_POST['contact_no'])) $data['contact_no'] = trim($_POST['contact_no']);
        if (!empty($_POST['password'])) $data['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);

        require_once __DIR__ . '/../config/Database.php';
        require_once __DIR__ . '/../models/Official.php';
        $db = (new Database())->getConnection();
        $model = new Official($db);

        try {
            // If role is being changed, enforce exclusive-role constraint
            if (isset($data['role'])) {
                $exclusiveRoles = [
                    'barangay captain',
                    'barangay secretary',
                    'barangay health worker president'
                ];
                $newRoleNorm = strtolower(trim($data['role']));
                if (in_array($newRoleNorm, $exclusiveRoles, true)) {
                    $current = $model->getById($id);
                    $currentRole = $current['role'] ?? '';
                    // If role is actually changing to an exclusive role, ensure no other official already holds it
                    if (strcasecmp(trim($currentRole), $data['role']) !== 0) {
                        $all = $model->getAll(true);
                        foreach ($all as $r) {
                            if ($r['id'] == $id) continue;
                            if (isset($r['role']) && strcasecmp(trim($r['role']), $data['role']) === 0) {
                                echo json_encode(['success'=>false,'field'=>'role','message'=>'Another official already has this role']);
                                return;
                            }
                        }
                    }
                }
            }
            $ok = $model->updateById($id, $data);
            if ($ok) echo json_encode(['success'=>true,'message'=>'Official updated']);
            else echo json_encode(['success'=>false,'message'=>'Update failed']);
        } catch (Exception $e) { error_log('updateOfficialAdmin: '.$e->getMessage()); echo json_encode(['success'=>false,'message'=>'Server error']); }
    }

    /**
     * AJAX: Delete (soft) official
     */
    public function deleteOfficial() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Invalid method']); return; }
        $id = $_POST['id'] ?? null; if (!$id) { echo json_encode(['success'=>false,'message'=>'Missing id']); return; }
        require_once __DIR__ . '/../config/Database.php';
        require_once __DIR__ . '/../models/Official.php';
        $db = (new Database())->getConnection();
        $model = new Official($db);
        try { $ok = $model->deleteById($id); if ($ok) echo json_encode(['success'=>true,'message'=>'Deleted']); else echo json_encode(['success'=>false,'message'=>'Delete failed']); } catch (Exception $e) { error_log('deleteOfficial: '.$e->getMessage()); echo json_encode(['success'=>false,'message'=>'Server error']); }
    }
}
