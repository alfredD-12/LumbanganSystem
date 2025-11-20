<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Gallery.php';

class GalleryController {
    private $galleryModel;
    
    public function __construct() {
        $this->galleryModel = new Gallery();
    }
    
    public function handleRequest() {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        switch ($action) {
            case 'fetch':
                $this->fetchGallery();
                break;
            case 'create':
                $this->createGallery();
                break;
            case 'update':
                $this->updateGallery();
                break;
            case 'delete':
                $this->deleteGallery();
                break;
            case 'toggle':
                $this->toggleGallery();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    }
    
    private function fetchGallery() {
        $activeOnly = isset($_GET['active_only']) && $_GET['active_only'] === 'true';
        $gallery = $this->galleryModel->getAll($activeOnly);
        echo json_encode(['success' => true, 'data' => $gallery]);
    }
    
    private function createGallery() {
        try {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $displayOrder = $_POST['display_order'] ?? 0;
            
            // Validate required fields
            if (empty($title)) {
                echo json_encode(['success' => false, 'message' => 'Title is required']);
                return;
            }
            
            // Handle file upload
            if (!isset($_FILES['image'])) {
                echo json_encode(['success' => false, 'message' => 'No image file uploaded']);
                return;
            }
            
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize (2MB)',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
                ];
                $errorMsg = $errorMessages[$_FILES['image']['error']] ?? 'Unknown upload error';
                echo json_encode(['success' => false, 'message' => $errorMsg]);
                return;
            }
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF allowed']);
                return;
            }
            
            $uploadDir = __DIR__ . '/../uploads/gallery/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
                    return;
                }
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                if ($this->galleryModel->create($title, $description, $fileName, $displayOrder)) {
                    echo json_encode(['success' => true, 'message' => 'Gallery item added successfully']);
                } else {
                    // Clean up uploaded file if database insert fails
                    unlink($uploadPath);
                    echo json_encode(['success' => false, 'message' => 'Failed to save to database']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file to: ' . $uploadPath]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
        }
    }
    
    private function updateGallery() {
        $id = $_POST['id'] ?? 0;
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $displayOrder = $_POST['display_order'] ?? 0;
        
        // Handle file upload if new image provided
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $uploadDir = __DIR__ . '/../uploads/gallery/';
            $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                // Delete old image
                $oldGallery = $this->galleryModel->getById($id);
                if ($oldGallery && file_exists($uploadDir . $oldGallery['image_path'])) {
                    unlink($uploadDir . $oldGallery['image_path']);
                }
                $imagePath = $fileName;
            }
        }
        
        if ($this->galleryModel->update($id, $title, $description, $imagePath, $displayOrder)) {
            echo json_encode(['success' => true, 'message' => 'Gallery item updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update gallery item']);
        }
    }
    
    private function deleteGallery() {
        $id = $_POST['id'] ?? 0;
        
        if ($this->galleryModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Gallery item deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete gallery item']);
        }
    }
    
    private function toggleGallery() {
        $id = $_POST['id'] ?? 0;
        
        if ($this->galleryModel->toggleActive($id)) {
            echo json_encode(['success' => true, 'message' => 'Gallery status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
    }
}

// Handle request if called directly
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new GalleryController();
    $controller->handleRequest();
}
