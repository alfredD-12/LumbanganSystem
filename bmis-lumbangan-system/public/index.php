<?php
// index.php — main entry point (Front Controller)

//  Load configuration and controllers
require_once __DIR__ . '/../app/controllers/DocumentRequestController.php';
require_once __DIR__ . '/../app/controllers/admins/AdminDocumentController.php';

//  Handle AJAX/API actions
$action = $_GET['action'] ?? null;

if ($action) {
    $controller = new DocumentRequestController();
    $adminController = new AdminDocumentController();

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

        default:
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid action']);
            break;
    }

    exit; // Stop normal HTML output
}

// ✅ Handle page routing (HTML views)
$page = $_GET['page'] ?? 'document_request';

switch ($page) {
    case 'document_request':
        $controller = new DocumentRequestController();
        $controller->showRequestForm();
        break;

    case 'admin_document_requests':
        $adminController = new AdminDocumentController();
        $adminController->showAdminDocumentRequestsPage();
        break;

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


