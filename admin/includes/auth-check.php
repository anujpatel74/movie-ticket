<?php
/**
 * CINEMA BOOKING - Admin Authentication Middleware
 */

require_once __DIR__ . '/../../includes/functions.php';

if (!is_admin_logged_in()) {
    set_flash('warning', 'Please sign in with administrator credentials.');
    redirect(base_url('admin/login.php'));
}

