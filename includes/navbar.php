<?php
/**
 * CINEMA BOOKING - Navigation Bar Component
 * Clean, compact, production-grade cinema navigation
 */
require_once __DIR__ . '/functions.php';

$allCities = [];
try {
    $allCities = get_all_cities();
} catch (Exception $e) {
    $allCities = [['id' => 1, 'name' => 'Mumbai']];
}
$selectedCity = get_selected_city();
$user = current_user();
?>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top cinematic-navbar">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand me-3" href="<?= base_url('index.php') ?>">
            <span class="brand-icon">
                <i class="bi bi-film"></i>
            </span>
            <span>CINEMA<span class="brand-accent">BOOKING</span></span>
        </a>

        <!-- Location / City Dropdown -->
        <div class="dropdown me-auto me-lg-3">
            <button class="btn btn-city dropdown-toggle" type="button" id="cityDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-geo-alt-fill text-danger"></i>
                <span><?= htmlspecialchars($selectedCity['name']) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-city" aria-labelledby="cityDropdown">
                <li class="dropdown-header text-uppercase fs-xs text-muted">Select City</li>
                <?php foreach ($allCities as $city): ?>
                    <li>
                        <a class="dropdown-item <?= ($city['id'] == $selectedCity['id']) ? 'active' : '' ?>" 
                           href="?city=<?= (int)$city['id'] ?>">
                            <?= htmlspecialchars($city['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0 shadow-none px-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links & Search -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= is_active_page('index.php') ?>" href="<?= base_url('index.php') ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_active_page('movies.php') ?>" href="<?= base_url('movies.php') ?>">Movies</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= is_active_page('theatres.php') ?>" href="<?= base_url('theatres.php') ?>">Theatres</a>
                </li>
                <?php if ($user): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= is_active_page('my-bookings.php') ?>" href="<?= base_url('my-bookings.php') ?>">My Bookings</a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Search Bar Wrapper with Live Suggestions -->
            <div class="nav-search-wrapper position-relative me-lg-3 mb-2 mb-lg-0">
                <form class="d-flex nav-search-form" action="<?= base_url('movies.php') ?>" method="GET" autocomplete="off" id="navSearchForm">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 text-secondary ps-3 pe-1">
                            <i class="bi bi-search small" id="searchIcon"></i>
                            <span class="spinner-border spinner-border-sm text-danger d-none" id="searchSpinner" role="status" aria-hidden="true" style="width: 0.85rem; height: 0.85rem;"></span>
                        </span>
                        <input class="form-control nav-search-input border-0" type="search" name="q" id="navSearchInput" placeholder="Search movies by title..." aria-label="Search" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" autocomplete="off">
                        <button type="button" class="btn btn-link text-secondary p-0 pe-2 text-decoration-none <?= !empty($_GET['q']) ? '' : 'd-none' ?>" id="clearSearchBtn" title="Clear search">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                </form>

                <!-- Live Search Suggestions Dropdown -->
                <div class="search-suggestions-dropdown d-none" id="searchSuggestionsDropdown">
                    <div class="search-suggestions-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom border-secondary border-opacity-25">
                        <span class="fs-xs text-uppercase letter-spacing text-secondary fw-bold" id="searchSuggestionsTitle">Suggestions</span>
                        <span class="badge bg-dark border border-secondary text-secondary fs-xs" id="searchResultCount">0 found</span>
                    </div>
                    <div class="search-suggestions-list" id="searchSuggestionsList" role="listbox">
                        <!-- Populated via JavaScript -->
                    </div>
                    <div class="search-suggestions-footer px-3 py-2 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center bg-black bg-opacity-40">
                        <div class="d-flex align-items-center gap-1">
                            <img src="<?= asset_url('images/tmdb-logo.svg') ?>" alt="TMDB" style="height: 10px; opacity: 0.75;">
                            <span class="text-secondary fs-xs" style="font-size: 0.65rem;">Cinema DB & TMDB</span>
                        </div>
                        <a href="<?= base_url('movies.php') ?>" class="fs-xs text-danger text-decoration-none fw-semibold" id="searchViewAllLink">View all &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- User Auth Buttons -->
            <div class="d-flex align-items-center gap-2">
                <?php if ($user): ?>
                    <div class="dropdown">
                        <button class="btn btn-user-profile dropdown-toggle d-flex align-items-center gap-2" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="user-avatar">
                                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                            </span>
                            <span class="d-none d-sm-inline"><?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow" aria-labelledby="userMenu">
                            <li class="px-3 py-2 border-bottom border-secondary border-opacity-25">
                                <div class="fw-bold text-white small"><?= htmlspecialchars($user['full_name']) ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($user['email']) ?></div>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 small" href="<?= base_url('my-bookings.php') ?>">
                                    <i class="bi bi-ticket-perforated text-danger me-2"></i>My Bookings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider border-secondary border-opacity-25 my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 small text-danger" href="<?= base_url('logout.php') ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= base_url('login.php') ?>" class="btn btn-outline-dark-custom py-1 px-3">
                        Sign In
                    </a>
                    <a href="<?= base_url('register.php') ?>" class="btn btn-accent py-1 px-3">
                        Sign Up
                    </a>
                <?php endif; ?>

                <!-- Admin Access Shortcut -->
                <a href="<?= base_url('admin/index.php') ?>" class="btn btn-outline-dark-custom py-1 px-2 text-secondary" title="Admin Portal" target="_blank">
                    <i class="bi bi-shield-lock"></i>
                </a>
            </div>
        </div>
    </div>
</nav>
