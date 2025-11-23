<?php
// index.php — main entry point (Front Controller)

//  Load configuration and controllers
require_once __DIR__ . '/../app/controllers/DocumentRequestController.php';
require_once __DIR__ . '/../app/controllers/admins/AdminDocumentController.php';
@require_once __DIR__ . '/../app/config/config.php';

// Add SurveyController so AJAX survey actions can be routed here
//require_once __DIR__ . '/../app/controllers/SurveyController.php';//
// Load announcement helpers (provides base_url/assets_url/uploads_url/announcement_image_url)
require_once __DIR__ . '/../app/helpers/announcement_helper.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
// Note: Survey controller is handled above for legacy procedural implementation
require_once __DIR__ . '/../app/controllers/ResidentController.php';
//  Handle AJAX/API actions
$action = $_GET['action'] ?? null;

if ($action) {
    $controller = new DocumentRequestController();
    $adminController = new AdminDocumentController();
//    $surveyController = new SurveyController(); // instantiate survey controller for AJAX survey actions

    switch ($action) {
        case 'getRequirements':
            $controller->getRequirements(); // must echo JSON
            break;

        case 'submitRequest':
            $controller->submitRequest(); // must echo JSON
            break;

        case 'getOngoingRequests':
            $controller->getOngoingRequestsAjax(); // must echo JSON
            break;

        case 'deleteRequest':
            $controller->deleteRequest(); // must echo JSON
            break;

        case 'getApprovedRequestsByUser' :
            $controller->getApprovedRequestsByUser(); // must echo JSON
            break;

        case 'getRequestsHistoryByUser' :
            $controller->getRequestHistoryByUser(); // must echo JSON
            break;

        case 'getAllRequests':
            $adminController->getAllRequests(); // returns JSON for DataTables
            break;

        case 'updateStatus':
            $adminController->updateRequestStatus(); // must echo JSON
            break;

        case 'getStatusSummary':
            $adminController->getStatusSummary();
            break;
        // Complaint API actions (mapped from legacy `route` values)
        case 'complaint_getDetails':
            $residentController = new ResidentController();
            $residentController->getDetails();
            break;

        case 'complaint_save':
            $residentController = new ResidentController();
            $residentController->save();
            break;

        case 'complaint_updateStatus':
            $residentController = new ResidentController();
            $residentController->updateStatus();
            break;

        case 'complaint_delete':
            $residentController = new ResidentController();
            $residentController->delete();
            break;
        case 'filterComplaints':
            // AJAX filter endpoint for complaints
            $admin = new AdminController();
            $admin->filterComplaints();
            break;

        case 'getComplaint':
            $admin = new AdminController();
            $admin->getComplaint();
            break;

        case 'createComplaint':
            $admin = new AdminController();
            $admin->createComplaint();
            break;

        case 'updateComplaint':
            $admin = new AdminController();
            $admin->updateComplaint();
            break;

        case 'deleteComplaint':
            $admin = new AdminController();
            $admin->deleteComplaint();
            break;

        case 'updateComplaintStatus':
            $admin = new AdminController();
            $admin->updateComplaintStatus();
            break;
        /*
         * Survey AJAX actions routed through front controller to SurveyController
         * These names match the action=... values used by the client-side scripts.
         */
        case 'create_assessment':
            $surveyController->create_assessment_action();
            break;

        case 'save_personal':
            $surveyController->save_personal_action();
            break;

        case 'save_vitals':
            $surveyController->save_vitals_action();
            break;

        case 'save_angina':
            $surveyController->save_angina_action();
            break;

        case 'save_diabetes':
            $surveyController->save_diabetes_action();
            break;

        case 'save_family_history':
            $surveyController->save_family_history_action();
            break;

        case 'save_family':
            $surveyController->save_family_action ?? $surveyController->save_family(); // fallback if named differently
            // Note: If your controller has a method for save_family, ensure its name matches; adjust if needed.
            break;

        case 'save_household':
            $surveyController->save_household_action ?? $surveyController->save_household();
            break;

        case 'save_lifestyle':
            $surveyController->save_lifestyle_action();
            break;

        case 'search_persons':
            $surveyController->search_persons_action();
            break;

        case 'get_person_relationships':
            $surveyController->get_person_relationships_action();
            break;

        case 'next_household_no':
            $surveyController->next_household_no_action();
            break;

        default:
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid action']);
            break;
    }

    exit; // Stop normal HTML output
}

// ✅ Handle page routing (HTML views)
@require_once __DIR__ . '/../app/helpers/session_helper.php';

$page = $_GET['page'] ?? null;

if (!$page) {
    if (isLoggedIn() && isUser()) {
        $page = 'dashboard_resident';
    } else {
        $page = 'landing';
    }
}

switch ($page) {
    case 'logout':
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }
        session_unset();
        session_destroy();

        $isXhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isXhr) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Logged out']);
            return;
        }
        $redirect = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=landing';
        header('Location: ' . $redirect);
        exit;

    case 'landing':
        if (isLoggedIn() && isUser()) {
            $redirect = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=dashboard_resident';
            header('Location: ' . $redirect);
            exit;
        }
        include __DIR__ . '/../app/views/landing/landing.php';
        break;

    case 'dashboard_resident':
        include __DIR__ . '/../app/views/Dashboard/dashboard.php';
        break;

    case 'document_request':
        $controller = new DocumentRequestController();
        $controller->showRequestForm();
        break;

    case 'admin_document_requests':
        $adminController = new AdminDocumentController();
        $adminController->showAdminDocumentRequestsPage();
        break;
    case 'public_announcement':
        require_once __DIR__ . '/../app/controllers/PublicAnnouncementController.php';
        $pubController = new PublicAnnouncementController();
        $pubController->index();
        break;
    case 'admin_announcements':
        require_once __DIR__ . '/../app/controllers/AnnouncementController.php';
        $annController = new AnnouncementController();
        $annController->index();
        break;
    case 'survey_wizard_personal':
        require_once __DIR__ . '/../app/controllers/SurveyController.php';
        $surveyController = new SurveyController();
        $surveyController->wizard_personal();
        break;
    case 'survey_wizard_vitals':
        require_once __DIR__ . '/../app/controllers/SurveyController.php';
        $surveyController = new SurveyController();
        $surveyController->wizard_vitals();
        break;
    case 'survey_wizard_family_history':
        require_once __DIR__ . '/../app/controllers/SurveyController.php';
        $surveyController = new SurveyController();
        $surveyController->wizard_family_history();
        break;
    case 'survey_wizard_family':
        require_once __DIR__ . '/../app/controllers/SurveyController.php';
        $surveyController = new SurveyController();
        $surveyController->wizard_family();
        break;
    case 'survey_wizard_lifestyle':
        require_once __DIR__ . '/../app/controllers/SurveyController.php';
        $surveyController = new SurveyController();
        $surveyController->wizard_lifestyle();
        break;
    case 'survey_wizard_angina':
        require_once __DIR__ . '/../app/controllers/SurveyController.php';
        $surveyController = new SurveyController();
        $surveyController->wizard_angina();
        break;
    case 'survey_wizard_diabetes':
        require_once __DIR__ . '/../app/controllers/SurveyController.php';
        $surveyController = new SurveyController();
        $surveyController->wizard_diabetes();
        break;
    case 'survey_wizard_household':
        require_once __DIR__ . '/../app/controllers/SurveyController.php';
        $surveyController = new SurveyController();
        $surveyController->wizard_household();
        break;
    case 'resident_complaints':
        require_once __DIR__ . '/../app/controllers/ResidentController.php';
        $comController = new ResidentController();
        $comController->index();
        break;
    case 'admin_complaints':
        require_once __DIR__ . '/../app/controllers/AdminController.php';
        $redController = new AdminController();
        $redController->index();
    // Example for future pages
    // case 'resident_list':
    //     $controller = new ResidentController();
    //     $controller->index();
    //     break;

    default:
        http_response_code(404);
        echo "404 - Page not found.";
        break;
}