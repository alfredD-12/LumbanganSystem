<?php include_once __DIR__ . '/../../config/config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Barangay Lumbangan System'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <!-- <link rel="stylesheet" href="<?php //echo BASE_URL . 'assets/css/Landing/landing.css'; ?>"> -->
    

</head>
<body>

    <nav class="navbar navbar-expand-lg bg-body-secondary position-sticky top-0 z-1">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                
                Barangay Lumbangan System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav">
                    <a class="nav-link active" aria-current="page" href="<?php echo BASE_PUBLIC . 'index.php?page=document_request'?>">Document</a>
                    <a class="nav-link" href="#">Announcement</a>
                    <a class="nav-link" href="#">Complaint</a>
                </div>
            </div>
        </div>
    </nav>