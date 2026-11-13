<?php
/**
 * CINEMA BOOKING - Helper & Utility Functions
 */

if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    // Detect HTTPS (including reverse proxies like Railway, Cloudflare, AWS)
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
        (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
    );

    // Set secure session cookie parameters
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } else {
        session_set_cookie_params(0, '/; samesite=Lax', '', $isHttps, true);
    }

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');

    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Get the base application URL dynamically.
 * Works seamlessly whether hosted at root, /CINEMA-BOOKING/, or a subdirectory.
 */
function base_url(string $path = ''): string {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = dirname($scriptName);
    
    // Normalize backslashes on Windows
    $dir = str_replace('\\', '/', $dir);

    // If script is in admin/ or backend/ or auth/, climb back to project root
    $subdirs = ['/admin/includes', '/admin', '/backend', '/auth', '/includes'];
    foreach ($subdirs as $sub) {
        if (substr($dir, -strlen($sub)) === $sub) {
            $dir = substr($dir, 0, -strlen($sub));
            break;
        }
    }

    $base = rtrim($dir, '/');
    $path = ltrim($path, '/');
    
    if (empty($path)) {
        return $base ?: '/';
    }
    
    return ($base ? $base . '/' : '/') . $path;
}

/**
 * Helper to generate asset URLs
 */
function asset_url(string $path = ''): string {
    return base_url('assets/' . ltrim($path, '/'));
}

/**
 * Robust movie poster resolver (handles local assets, relative paths, and CDN URLs)
 * Automatically verifies physical presence on disk and provides clean SVG fallback
 */
function movie_poster_url(?string $posterUrl): string {
    if (empty($posterUrl)) {
        return asset_url('images/poster-placeholder.svg');
    }
    if (str_starts_with($posterUrl, 'http://') || str_starts_with($posterUrl, 'https://')) {
        return $posterUrl;
    }
    
    $relative = ltrim(str_replace('\\', '/', $posterUrl), '/');
    $fullPath = __DIR__ . '/../' . $relative;

    if (file_exists($fullPath) && is_file($fullPath)) {
        $parts = explode('/', $relative);
        $encoded = array_map('rawurlencode', $parts);
        return base_url(implode('/', $encoded));
    }

    return asset_url('images/poster-placeholder.svg');
}

/**
 * Robust movie backdrop resolver with size verification and fallback
 */
function movie_backdrop_url(?string $backdropUrl, ?string $posterUrl = null): string {
    if (!empty($backdropUrl)) {
        if (str_starts_with($backdropUrl, 'http://') || str_starts_with($backdropUrl, 'https://')) {
            return $backdropUrl;
        }
        $rel = ltrim(str_replace('\\', '/', $backdropUrl), '/');
        $full = __DIR__ . '/../' . $rel;
        if (file_exists($full) && is_file($full) && filesize($full) > 4096) {
            $parts = explode('/', $rel);
            $encoded = array_map('rawurlencode', $parts);
            return base_url(implode('/', $encoded));
        }
    }
    if (!empty($posterUrl)) {
        return movie_poster_url($posterUrl);
    }
    return asset_url('images/oppenheimer_bg.jpg');
}

/**
 * Sanitize string input to prevent XSS
 */
function sanitize(?string $data): string {
    if ($data === null) return '';
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF Protection
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash messaging system (success, danger, warning, info)
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function render_flash(): string {
    $flash = get_flash();
    if (!$flash) return '';
    
    $iconMap = [
        'success' => 'bi-check-circle-fill',
        'danger'  => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        'info'    => 'bi-info-circle-fill'
    ];
    $icon = $iconMap[$flash['type']] ?? 'bi-info-circle';

    return sprintf(
        '<div class="alert alert-%s alert-dismissible fade show cinematic-alert" role="alert">
            <i class="bi %s me-2"></i>%s
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>',
        htmlspecialchars($flash['type']),
        $icon,
        htmlspecialchars($flash['message'])
    );
}

/**
 * User Authentication Helpers
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array {
    if (!is_logged_in()) return null;
    return [
        'id'        => $_SESSION['user_id'],
        'full_name' => $_SESSION['user_name'] ?? 'User',
        'email'     => $_SESSION['user_email'] ?? '',
        'role'      => $_SESSION['user_role'] ?? 'user'
    ];
}

function logout_user(): void {
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role']);
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Admin Authentication Helpers
 */
function is_admin_logged_in(): bool {
    return !empty($_SESSION['admin_id']);
}

function current_admin(): ?array {
    if (!is_admin_logged_in()) return null;
    return [
        'id'        => $_SESSION['admin_id'],
        'username'  => $_SESSION['admin_username'] ?? 'admin',
        'full_name' => $_SESSION['admin_name'] ?? 'Administrator',
        'email'     => $_SESSION['admin_email'] ?? '',
        'role'      => $_SESSION['admin_role'] ?? 'admin'
    ];
}

function logout_admin(): void {
    unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_name'], $_SESSION['admin_email'], $_SESSION['admin_role']);
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Formatting Utilities
 */
function format_currency($amount): string {
    return '₹' . number_format((float)$amount, 2);
}

function format_date(string $date, string $format = 'D, d M Y'): string {
    try {
        $dt = new DateTime($date);
        return $dt->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

function format_time(string $time, string $format = 'h:i A'): string {
    try {
        $dt = new DateTime($time);
        return $dt->format($format);
    } catch (Exception $e) {
        return $time;
    }
}

function format_duration(int $minutes): string {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    if ($hours > 0 && $mins > 0) {
        return "{$hours}h {$mins}m";
    } elseif ($hours > 0) {
        return "{$hours}h";
    }
    return "{$mins}m";
}

function generate_booking_reference(): string {
    return 'CNM-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5)) . '-' . rand(10, 99);
}

function generate_transaction_reference(): string {
    return 'TXN-DEMO-' . rand(100000, 999999);
}

/**
 * Safe JSON response helper for API endpoints
 */
function json_response(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Check if the current page matches the given page name for navbar active status
 */
function is_active_page(string $page): string {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return ($currentPage === $page) ? 'active' : '';
}

/**
 * Get or set the active browsing city
 */
function get_selected_city(): array {
    $pdo = getDBConnection();
    
    // Check if city_id query param is passed
    if (!empty($_GET['city'])) {
        $cityId = (int)$_GET['city'];
        $stmt = $pdo->prepare("SELECT * FROM cities WHERE id = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$cityId]);
        $city = $stmt->fetch();
        if ($city) {
            $_SESSION['selected_city'] = $city;
            return $city;
        }
    }
    
    if (!empty($_SESSION['selected_city'])) {
        return $_SESSION['selected_city'];
    }

    // Default to first city (e.g. Mumbai)
    $stmt = $pdo->query("SELECT * FROM cities WHERE is_active = 1 ORDER BY id ASC LIMIT 1");
    $city = $stmt->fetch();
    if ($city) {
        $_SESSION['selected_city'] = $city;
        return $city;
    }

    return ['id' => 1, 'name' => 'Mumbai', 'state' => 'Maharashtra'];
}

function get_all_cities(): array {
    $pdo = getDBConnection();
    return $pdo->query("SELECT * FROM cities WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
}

/**
 * Redirect utility
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

