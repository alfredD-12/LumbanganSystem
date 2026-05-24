<?php
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../models/ComplaintHistory.php';
require_once __DIR__ . '/../models/Official.php';
require_once __DIR__ . '/../services/ComplaintMailSender.php';
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';

class PoliceController
{
    private $complaintModel;
    private $officialModel;
    private $db;

    public function __construct()
    {
        $this->complaintModel = new Complaint();
        $database = new Database();
        $this->db = $database->getConnection();
        $this->officialModel = new Official($this->db);
    }

    public function dashboard()
    {
        if (!$this->ensurePoliceAccess()) {
            return;
        }

        try {
            // Respect optional search and filter query parameters (search, status_id, case_type_id)
            $filters = [
                'search' => isset($_GET['search']) ? trim((string) $_GET['search']) : '',
                'status_id' => isset($_GET['status_id']) ? trim((string) $_GET['status_id']) : '',
                'case_type_id' => isset($_GET['case_type_id']) ? trim((string) $_GET['case_type_id']) : '',
                // Police Portal only receives complaints manually forwarded by admins.
                'police_forwarded' => true,
                // Keep resolved complaints out of the active Police Complaints / Blotter list.
                'active_only' => true,
            ];
            $complaints = $this->complaintModel->getAll($filters);
            $dashboardComplaints = $this->complaintModel->getAll([
                'police_forwarded' => true,
            ]);
            $availableStatuses = $this->getAllowedStatuses();
            $allStatuses = $this->complaintModel->getStatuses();
            $caseTypes = $this->complaintModel->getCaseTypes();
            $statusOptionsById = [];

            $historyFilters = [
                'search' => isset($_GET['history_search']) ? trim((string) $_GET['history_search']) : '',
                'status_id' => isset($_GET['history_status_id']) ? trim((string) $_GET['history_status_id']) : '',
                'from' => isset($_GET['history_from']) ? trim((string) $_GET['history_from']) : '',
                'to' => isset($_GET['history_to']) ? trim((string) $_GET['history_to']) : '',
            ];
            $historyRecords = [];
            $historyLoadError = '';
            try {
                $historyModel = new ComplaintHistory();
                $historyRecords = $historyModel->getAll($historyFilters);
            } catch (Exception $e) {
                $historyRecords = [];
                $historyLoadError = 'Complaint History is not available yet. Please run the database migration for complaint history.';
                error_log('Error loading complaint history: ' . $e->getMessage());
            }

            foreach ($availableStatuses as $status) {
                $statusOptionsById[(int) $status['id']] = $status['label'];
            }

            $flashSuccess = trim((string) ($_GET['success'] ?? ''));
            $flashError = trim((string) ($_GET['error'] ?? ''));

            require __DIR__ . '/../views/police/dashboard.php';
        } catch (Exception $e) {
            error_log('Error in PoliceController::dashboard: ' . $e->getMessage());
            $complaints = [];
            $dashboardComplaints = [];
            $availableStatuses = [];
            $allStatuses = [];
            $caseTypes = [];
            $statusOptionsById = [];
            $historyRecords = [];
            $historyLoadError = '';
            $flashSuccess = '';
            $flashError = 'Unable to load police dashboard right now.';
            require __DIR__ . '/../views/police/dashboard.php';
        }
    }

    public function updateComplaintStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithMessage('error', 'Invalid request method.');
            return;
        }

        if (!$this->ensurePoliceAccess()) {
            return;
        }

        csrf_require_valid_token();

        $complaintId = (int) ($_POST['id'] ?? 0);
        $statusId = (int) ($_POST['status_id'] ?? 0);

        if ($complaintId <= 0 || $statusId <= 0) {
            $this->redirectWithMessage('error', 'Missing complaint or status.');
            return;
        }

        $allowedStatuses = $this->getAllowedStatuses();
        $allowedIds = array_map(static function ($status) {
            return (int) ($status['id'] ?? 0);
        }, $allowedStatuses);

        if (!in_array($statusId, $allowedIds, true)) {
            $this->redirectWithMessage('error', 'Selected status is not allowed for police updates.');
            return;
        }

        try {
            $currentComplaint = $this->complaintModel->getById($complaintId);
            if (!$this->isForwardedToPolice($currentComplaint)) {
                $this->redirectWithMessage('error', 'This complaint is not available in the Police Portal.');
                return;
            }

            $officialId = (int) ($_SESSION['official_id'] ?? 0);
            if ($officialId <= 0) {
                $this->redirectWithMessage('error', 'Unable to identify the police user for history logging.');
                return;
            }

            $newStatusLabel = $this->getStatusLabelById($statusId, $allowedStatuses);
            $remarks = isset($_POST['remarks']) ? trim((string) $_POST['remarks']) : '';
            if ($this->isResolvedStatus($newStatusLabel, $statusId) && $remarks === '') {
                $this->redirectWithMessage('error', 'Resolution remarks are required when marking a complaint as Resolved.');
                return;
            }

            $previousStatusId = $currentComplaint ? (int) ($currentComplaint['status_id'] ?? 0) : null;
            if ($previousStatusId !== null && $previousStatusId <= 0) {
                $previousStatusId = null;
            }

            $updated = $this->complaintModel->updateStatus($complaintId, $statusId);
            if ($updated) {
                try {
                    $history = new ComplaintHistory();
                    $history->logStatusChange($complaintId, $previousStatusId, $statusId, $officialId, $remarks);
                } catch (Exception $e) {
                    error_log('Complaint history log failed: ' . $e->getMessage());
                    $this->redirectWithMessage('error', 'Status updated, but Complaint History could not be saved. Please check the complaint history migration.');
                    return;
                }

                if ($this->isResolvedStatus($newStatusLabel, $statusId)) {
                    $this->notifyResidentComplaintResolved($currentComplaint, $remarks);
                }

                $this->redirectWithMessage('success', 'Complaint status updated successfully.');
                return;
            }

            $this->redirectWithMessage('error', 'Failed to update complaint status.');
        } catch (Exception $e) {
            error_log('Error in PoliceController::updateComplaintStatus: ' . $e->getMessage());
            $this->redirectWithMessage('error', 'Unable to update status: ' . $e->getMessage());
        }
    }

    public function getComplaintDetails()
    {
        header('Content-Type: application/json');

        if (!$this->ensurePoliceAccess()) {
            return;
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid complaint id.']);
            return;
        }

        try {
            $complaint = $this->complaintModel->getById($id);
            if (!$complaint) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Complaint not found.']);
                return;
            }

            if (!$this->isForwardedToPolice($complaint)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Complaint not found.']);
                return;
            }

            echo json_encode(['success' => true, 'data' => $complaint]);
        } catch (Exception $e) {
            error_log('Error in PoliceController::getComplaintDetails: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to load complaint details.']);
        }
    }

    public function sendComplaintEmail()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        if (!$this->ensurePoliceAccess()) {
            return;
        }

        csrf_require_valid_token();

        $complaintId = (int) ($_POST['complaint_id'] ?? 0);
        $recipientEmail = trim((string) ($_POST['recipient_email'] ?? ''));
        $notifyAuthority = (int) ($_POST['notify_authority'] ?? 0) === 1;

        if ($complaintId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid complaint id.']);
            return;
        }

        if ($notifyAuthority) {
            $recipientEmail = defined('SECURITY_ALERT_EMAIL') && SECURITY_ALERT_EMAIL !== ''
                ? SECURITY_ALERT_EMAIL
                : (defined('SENDER_EMAIL') ? SENDER_EMAIL : $recipientEmail);
        }

        if ($recipientEmail === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Recipient email is required.']);
            return;
        }

        try {
            $complaint = $this->complaintModel->getById($complaintId);
            if (!$complaint) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Complaint not found.']);
                return;
            }

            if (!$this->isForwardedToPolice($complaint)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Complaint not found.']);
                return;
            }

            $subject = 'Police Notification: Complaint #' . (int) $complaint['id'];
            $body = $this->buildComplaintEmailBody($complaint);
            $mailer = new ComplaintMailSender();
            $sent = $mailer->sendComplaintAlert($recipientEmail, $subject, $body);

            if (!$sent) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Email could not be sent.']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Email notification sent.']);
        } catch (Exception $e) {
            error_log('Error in PoliceController::sendComplaintEmail: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to send email notification.']);
        }
    }

    public function changePassword()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        if (!$this->ensurePoliceAccess()) {
            return;
        }

        csrf_require_valid_token();

        $officialId = (int) ($_SESSION['official_id'] ?? 0);
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($officialId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid session. Please log in again.']);
            return;
        }

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'All password fields are required.']);
            return;
        }

        if (strlen($newPassword) < 8) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters long.']);
            return;
        }

        if ($newPassword !== $confirmPassword) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'New password and confirm password do not match.']);
            return;
        }

        $official = $this->officialModel->getById($officialId);
        if (!$official || empty($official['password_hash'])) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Police account not found.']);
            return;
        }

        if (!password_verify($currentPassword, $official['password_hash'])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
            return;
        }

        if (password_verify($newPassword, $official['password_hash'])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'New password must be different from current password.']);
            return;
        }

        $updated = $this->officialModel->updateById($officialId, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        if (!$updated) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to update password right now.']);
            return;
        }

        echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
    }

    private function ensurePoliceAccess()
    {
        if (!isPolice()) {
            $redirect = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=landing';
            header('Location: ' . $redirect);
            exit;
        }

        return true;
    }

    private function getAllowedStatuses()
    {
        $statuses = $this->complaintModel->getStatuses();
        $allowedLabels = ['pending', 'ongoing', 'resolved', 'investigating'];
        $filtered = [];

        foreach ($statuses as $status) {
            $label = strtolower(trim((string) ($status['label'] ?? '')));
            if (in_array($label, $allowedLabels, true)) {
                $filtered[] = $status;
            }
        }

        if (!empty($filtered)) {
            return $filtered;
        }

        return array_values(array_filter($statuses, static function ($status) {
            $id = (int) ($status['id'] ?? 0);
            return in_array($id, [1, 2, 3], true);
        }));
    }

    private function isForwardedToPolice($complaint)
    {
        return is_array($complaint) && (int) ($complaint['forwarded_to_police'] ?? 0) === 1;
    }

    private function getStatusLabelById($statusId, array $statuses)
    {
        foreach ($statuses as $status) {
            if ((int) ($status['id'] ?? 0) === (int) $statusId) {
                return trim((string) ($status['label'] ?? ''));
            }
        }

        return '';
    }

    private function isResolvedStatus($statusLabel, $statusId)
    {
        return strtolower(trim((string) $statusLabel)) === 'resolved' || (int) $statusId === 3;
    }

    private function notifyResidentComplaintResolved(array $complaint, $remarks = '')
    {
        $residentUserId = (int) ($complaint['user_id'] ?? 0);
        $complaintId = (int) ($complaint['id'] ?? 0);

        if ($residentUserId <= 0 || $complaintId <= 0) {
            return;
        }

        try {
            $title = 'Complaint Resolved';
            $incidentTitle = trim((string) ($complaint['incident_title'] ?? 'your complaint'));
            $message = 'Your forwarded complaint "' . $incidentTitle . '" has been marked as resolved by the Police Portal.';
            $resolutionRemarks = trim((string) $remarks);
            if ($resolutionRemarks !== '') {
                $message .= ' Resolution remarks: ' . $resolutionRemarks;
            }

            $stmt = $this->db->prepare("
                INSERT INTO notifications
                    (user_id, user_type, notification_type, title, message, link, reference_id)
                VALUES
                    (:user_id, 'user', 'complaint', :title, :message, :link, :reference_id)
            ");

            $stmt->execute([
                ':user_id' => $residentUserId,
                ':title' => $title,
                ':message' => $message,
                ':link' => '?page=resident_complaints#incident-' . $complaintId,
                ':reference_id' => $complaintId,
            ]);
        } catch (Exception $e) {
            error_log('Resident complaint resolved notification failed: ' . $e->getMessage());
        }
    }

    private function buildComplaintEmailBody(array $complaint)
    {
        $label = htmlspecialchars((string) ($complaint['status_label'] ?? 'Pending'), ENT_QUOTES, 'UTF-8');
        $caseType = htmlspecialchars((string) ($complaint['case_type'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars((string) ($complaint['incident_title'] ?? 'Complaint'), ENT_QUOTES, 'UTF-8');
        $complainant = htmlspecialchars((string) ($complaint['complainant_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
        $location = htmlspecialchars((string) ($complaint['location'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
        $date = htmlspecialchars((string) ($complaint['date_of_incident'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
        $time = htmlspecialchars((string) ($complaint['time_of_incident'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
        $narrative = nl2br(htmlspecialchars((string) ($complaint['narrative'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'));

        return '<h2>Complaint Notification</h2>'
            . '<p><strong>Complaint ID:</strong> ' . (int) ($complaint['id'] ?? 0) . '</p>'
            . '<p><strong>Title:</strong> ' . $title . '</p>'
            . '<p><strong>Status:</strong> ' . $label . '</p>'
            . '<p><strong>Case Type:</strong> ' . $caseType . '</p>'
            . '<hr>'
            . '<h3>Complainant</h3>'
            . '<p><strong>Name:</strong> ' . $complainant . '</p>'
            . '<p><strong>Contact:</strong> ' . htmlspecialchars((string) ($complaint['complainant_contact'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Address:</strong> ' . htmlspecialchars((string) ($complaint['complainant_address'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') . '</p>'
            . '<hr>'
            . '<h3>Incident Details</h3>'
            . '<p><strong>Date:</strong> ' . $date . '</p>'
            . '<p><strong>Time:</strong> ' . $time . '</p>'
            . '<p><strong>Location:</strong> ' . $location . '</p>'
            . '<p><strong>Description:</strong><br>' . $narrative . '</p>'
            . '<hr>'
            . '<h3>Offender (if provided)</h3>'
            . '<p><strong>Name:</strong> ' . htmlspecialchars((string) ($complaint['offender_name'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Gender:</strong> ' . htmlspecialchars((string) ($complaint['offender_gender'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Address:</strong> ' . htmlspecialchars((string) ($complaint['offender_address'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Description:</strong> ' . htmlspecialchars((string) ($complaint['offender_description'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') . '</p>';
    }

    private function redirectWithMessage($type, $message)
    {
        $param = $type === 'success' ? 'success' : 'error';
        $redirect = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '')
            . '/index.php?page=dashboard_police&'
            . $param
            . '='
            . urlencode($message);

        header('Location: ' . $redirect);
        exit;
    }
}
