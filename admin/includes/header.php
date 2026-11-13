<?php
/**
 * CINEMA BOOKING - Admin Header Component
 */
require_once __DIR__ . '/../../includes/functions.php';

$adminTitle = isset($adminTitle) ? htmlspecialchars($adminTitle) . ' | Admin Portal' : 'Admin Portal - CINEMA BOOKING';
$currentAdmin = current_admin();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $adminTitle ?></title>

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= asset_url('css/admin.css') ?>">
</head>
<body class="admin-body">
    <!-- Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Wrapper -->
    <div class="admin-main-wrapper">
        <!-- Top Navbar -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-dark d-lg-none border-secondary" type="button" onclick="document.querySelector('.admin-sidebar').classList.toggle('show')">
                    <i class="bi bi-list"></i>
                </button>
                <h5 class="m-0 text-white fw-bold d-none d-sm-block">Cinema Operations Portal</h5>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="<?= base_url('index.php') ?>" class="btn btn-sm btn-outline-light rounded-pill px-3" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i>View Live Site
                </a>

                <div class="dropdown">
                    <button class="btn btn-dark btn-sm rounded-pill border-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 0.75rem; font-weight: bold;">
                            A
                        </div>
                        <span class="d-none d-md-inline small text-light"><?= htmlspecialchars($currentAdmin['full_name'] ?? 'Admin') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                        <li class="px-3 py-2 border-bottom border-secondary border-opacity-25">
                            <strong class="d-block text-white"><?= htmlspecialchars($currentAdmin['full_name'] ?? 'Admin') ?></strong>
                            <small class="text-secondary"><?= htmlspecialchars($currentAdmin['email'] ?? 'admin@cinemabooking.com') ?></small>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="<?= base_url('admin/logout.php') ?>">
                                <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Admin Content Container -->
        <main class="admin-content">
            <?= render_flash() ?>

