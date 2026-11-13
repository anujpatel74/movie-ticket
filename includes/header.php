<?php
/**
 * CINEMA BOOKING - Global Header Component
 */
require_once __DIR__ . '/functions.php';

$pageTitle = isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | CINEMA BOOKING' : 'CINEMA BOOKING - Ultimate Movie Ticket Experience';
$activeCity = get_selected_city();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Book movie tickets online with premium recliner, IMAX and 4DX seating at CINEMA BOOKING.">
    <meta name="theme-color" content="#090B10">
    <title><?= $pageTitle ?></title>

    <!-- Google Fonts: Plus Jakarta Sans, Space Grotesk, Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS Architecture -->
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/responsive.css') ?>">
    
    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body class="cinematic-body">
<?php
if (!isset($hideNavbar) || !$hideNavbar) {
    include __DIR__ . '/navbar.php';
}
?>
<main class="cinematic-main">
    <div class="container mt-3">
        <?= render_flash() ?>
    </div>

