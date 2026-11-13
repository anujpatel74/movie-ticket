<?php
/**
 * CINEMA BOOKING - Admin Logout
 */

require_once __DIR__ . '/../includes/functions.php';

logout_admin();
set_flash('info', 'Admin session terminated.');
redirect(base_url('admin/login.php'));

