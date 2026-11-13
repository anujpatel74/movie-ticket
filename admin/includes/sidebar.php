<?php
/**
 * CINEMA BOOKING - Admin Sidebar Navigation
 */
require_once __DIR__ . '/../../includes/functions.php';

$activeNav = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="admin-sidebar-header d-flex align-items-center justify-content-between">
        <a href="<?= base_url('admin/index.php') ?>" class="text-decoration-none d-flex align-items-center gap-2">
            <span class="fs-4 text-danger"><i class="bi bi-film"></i></span>
            <span class="fs-5 fw-bold text-white tracking-wider">CINEMA<span class="text-danger">ADMIN</span></span>
        </a>
        <button class="btn btn-sm btn-dark d-lg-none text-secondary" onclick="document.querySelector('.admin-sidebar').classList.remove('show')">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <nav class="admin-nav">
        <div class="text-uppercase text-secondary fs-xs fw-bold px-3 mb-2">Main</div>
        <a href="<?= base_url('admin/index.php') ?>" class="nav-link <?= $activeNav === 'index.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>Dashboard
        </a>

        <div class="text-uppercase text-secondary fs-xs fw-bold px-3 mt-4 mb-2">Management</div>
        <a href="<?= base_url('admin/movies.php') ?>" class="nav-link <?= $activeNav === 'movies.php' ? 'active' : '' ?>">
            <i class="bi bi-camera-reels"></i>Movies
        </a>
        <a href="<?= base_url('admin/theatres.php') ?>" class="nav-link <?= $activeNav === 'theatres.php' ? 'active' : '' ?>">
            <i class="bi bi-building"></i>Theatres
        </a>
        <a href="<?= base_url('admin/screens.php') ?>" class="nav-link <?= $activeNav === 'screens.php' ? 'active' : '' ?>">
            <i class="bi bi-grid-3x3"></i>Screens & Seats
        </a>
        <a href="<?= base_url('admin/shows.php') ?>" class="nav-link <?= $activeNav === 'shows.php' ? 'active' : '' ?>">
            <i class="bi bi-calendar-event"></i>Showtimes
        </a>

        <div class="text-uppercase text-secondary fs-xs fw-bold px-3 mt-4 mb-2">Bookings & Users</div>
        <a href="<?= base_url('admin/bookings.php') ?>" class="nav-link <?= $activeNav === 'bookings.php' ? 'active' : '' ?>">
            <i class="bi bi-ticket-perforated"></i>Bookings & Tickets
        </a>
        <a href="<?= base_url('admin/users.php') ?>" class="nav-link <?= $activeNav === 'users.php' ? 'active' : '' ?>">
            <i class="bi bi-people"></i>User Accounts
        </a>

        <div class="text-uppercase text-secondary fs-xs fw-bold px-3 mt-4 mb-2">System</div>
        <a href="<?= base_url('index.php') ?>" target="_blank" class="nav-link">
            <i class="bi bi-box-arrow-up-right"></i>Customer Site
        </a>
        <a href="<?= base_url('admin/logout.php') ?>" class="nav-link text-danger">
            <i class="bi bi-box-arrow-right"></i>Sign Out
        </a>
    </nav>
</aside>

