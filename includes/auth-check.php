<?php
/**
 * CINEMA BOOKING - User Authentication Middleware
 */

require_once __DIR__ . '/functions.php';

if (!is_logged_in()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? base_url();
    set_flash('warning', 'Please sign in or create an account to proceed with your booking.');
    redirect(base_url('login.php'));
}

