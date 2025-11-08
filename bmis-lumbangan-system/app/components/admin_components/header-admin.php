<?php include_once __DIR__ . '/../../config/config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Barangay Lumbangan System'); ?></title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <!-- Admin UI CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL . 'assets/css/admins/document_admin.css'; ?>">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet" integrity="sha384-zmMNeKbOwzvUmxN8Z/VoYM+i+cwyC14+U9lq4+ZL0Ro7p1GMoh8uq8/HvIBgnh9+" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.bootstrap5.min.css" rel="stylesheet" integrity="sha384-HI7qMf1hznIZrIds5RatHHAOCn/7uGgsYQCanIyCeJDebKwCnoWnm4cB9SH+Z/ab" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.3.4/b-3.2.5/b-colvis-3.2.5/datatables.min.css" rel="stylesheet" integrity="sha384-b7CCWUkHYYyObRWK8dDxH6PCbeH3SHTbH+TzwIoEUU/Ol75XipyzcYbfdNWmNWFF" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.3.4/cr-2.1.2/datatables.min.css" rel="stylesheet" integrity="sha384-Kmlp1CBAWUtz5k1YIckZJpfqfz679/v2c11h9L26srNf/fL27CqaljUGsp7P2SN9" crossorigin="anonymous">


    

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
                    <a class="nav-link active" aria-current="page" href="<?php echo BASE_PUBLIC . 'index.php?page=admin_document_requests'?>">Document</a>
                    <a class="nav-link" href="#">Announcement</a>
                    <a class="nav-link" href="#">Complaint</a>
                </div>
            </div>
        </div>
    </nav>