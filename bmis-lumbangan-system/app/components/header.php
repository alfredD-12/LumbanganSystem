<?php include_once __DIR__ . '/../config/config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Baranagay Lumbangan System'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="<?php  echo BASE_URL . 'assets/css/bootstrap5/css/bootstrap.min.css';?>">
    <link rel="stylesheet" href="<?php  echo BASE_URL . 'assets/css/header.css';?>">

</head>
<body>
    <nav class="navbar navbar-expand-md navbar-dark bg-primary sticky-md-top p-1 p-md-2">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img class="image" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQR5ZnaOZ07LQE_OIw7lSECckwWoP10OMtbShRHTeaRnePRQBJKv7ItTDKyzIAMYch0UbQ&usqp=CAU" alt="Logo" width="30" height="24" class="d-inline-block align-text-top">
                Barangay Lumbangan System</a>
            
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_PUBLIC . 'index.php?page=document_request'; ?>">Document</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=announcements">Announcements</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=complaint">Complaint</a>
                    </li>
                </ul>
            </div>
            
            <div class="btn-group ms-auto" role="group" aria-label="Navbar buttons">
                <button class="btn btn-primary" type="button"><i class="fa-solid fa-bell"></i></button>
            </div>
            <div></div>
            <div class="ms-2">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </nav>
