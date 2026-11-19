<?php

declare(strict_types=1);

$activePage = isset($currentPage) && is_string($currentPage) ? $currentPage : 'lecturers';
$titleText = isset($pageTitle) && is_string($pageTitle) ? $pageTitle : 'SIM Akademik Dosen';
$flashMessage = isset($flash) && is_array($flash) ? $flash : null;
$errorBanner = isset($dbError) && is_string($dbError) && $dbError !== '' ? $dbError : null;
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titleText) ?> — TP8 DPBO MVC</title>
    <link rel="stylesheet" href="style/bootstrap.min.css">
    <link rel="stylesheet" href="style/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php?page=lecturers">SIM Akademik MVC</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'lecturers' ? 'active fw-semibold' : '' ?>" href="index.php?page=lecturers">
                            Dosen (Lecturers)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'departments' ? 'active fw-semibold' : '' ?>" href="index.php?page=departments">
                            Departemen (Departments)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'courses' ? 'active fw-semibold' : '' ?>" href="index.php?page=courses">
                            Mata Kuliah (Courses)
                        </a>
                    </li>
                </ul>
                <span class="navbar-text small text-light opacity-75">
                    Arsitektur MVC &bull; PDO Prepared Statement
                </span>
            </div>
        </div>
    </nav>

    <main class="app-main container py-4">
        <?php if ($flashMessage !== null && $flashMessage['message'] !== ''): ?>
            <div class="alert alert-<?= e($flashMessage['type']) ?> alert-dismissible fade show" role="alert">
                <?= e($flashMessage['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <?php if ($errorBanner !== null): ?>
            <div class="alert alert-danger" role="alert">
                <h5 class="alert-heading mb-1">Koneksi Database Bermasalah</h5>
                <p class="mb-0"><?= e($errorBanner) ?></p>
            </div>
        <?php endif; ?>
