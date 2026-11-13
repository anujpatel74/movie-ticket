<?php
/**
 * CINEMA BOOKING - User Logout
 */

require_once __DIR__ . '/includes/functions.php';

logout_user();
set_flash('info', 'You have been safely signed out.');
redirect(base_url('index.php'));

