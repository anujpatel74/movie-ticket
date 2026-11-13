<?php
/**
 * CINEMA BOOKING - Database Configuration
 * Supports automatic environment detection (Local XAMPP vs Production / InfinityFree)
 */

// --------------------------------------------------------
// 1. Environment Auto-Detection
// --------------------------------------------------------
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = (
    in_array($httpHost, ['localhost', '127.0.0.1', '::1']) ||
    str_starts_with($httpHost, 'localhost:') ||
    str_starts_with($httpHost, '127.0.0.1:') ||
    str_starts_with($httpHost, '192.168.') ||
    str_starts_with($httpHost, '10.') ||
    str_starts_with($httpHost, '172.') ||
    str_ends_with($httpHost, '.trycloudflare.com') ||
    str_ends_with($httpHost, '.localtunnel.me') ||
    str_ends_with($httpHost, '.ngrok-free.app') ||
    str_ends_with($httpHost, '.ngrok.io') ||
    php_sapi_name() === 'cli'
);

if ($isLocal) {
    // ----------------------------------------------------
    // LOCAL CONFIGURATION (XAMPP / Localhost)
    // ----------------------------------------------------
    if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
    if (!defined('DB_NAME')) define('DB_NAME', 'cinema_booking');
    if (!defined('DB_USER')) define('DB_USER', 'root');
    if (!defined('DB_PASS')) define('DB_PASS', '');
    if (!defined('DB_PORT')) define('DB_PORT', '3306');
} else {
    // ----------------------------------------------------
    // PRODUCTION CONFIGURATION (InfinityFree / Live Hosting)
    // IMPORTANT: Replace with your actual InfinityFree MySQL details!
    // Found in: InfinityFree Client Area -> Manage -> Database Details
    // ----------------------------------------------------
    if (!defined('DB_HOST')) define('DB_HOST', 'sqlxxx.infinityfree.com'); // e.g. sql105.infinityfree.com (DO NOT use 'localhost')
    if (!defined('DB_NAME')) define('DB_NAME', 'if0_xxxxxxx_cinema_booking'); // Full DB name with your account prefix
    if (!defined('DB_USER')) define('DB_USER', 'if0_xxxxxxx'); // Your vPanel username
    if (!defined('DB_PASS')) define('DB_PASS', 'YOUR_ACCOUNT_PASSWORD'); // Your InfinityFree vPanel password
    if (!defined('DB_PORT')) define('DB_PORT', '3306');
}

if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a PDO database connection instance.
 *
 * @return PDO
 */
function getDBConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            // Free hosts restrict max connections; avoid persistent connections
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Check if database does not exist
            if ($e->getCode() == 1049) {
                die(renderDatabaseMissingError());
            }
            // General connection error
            die(renderDatabaseConnectionError($e->getMessage(), $dsn));
        }
    }

    return $pdo;
}

/**
 * Diagnostic screen when the database cannot be found
 */
function renderDatabaseMissingError(): string {
    global $isLocal;
    $dbName = htmlspecialchars(DB_NAME);

    if ($isLocal) {
        $instructions = '
            <p class="text-secondary">Please import the provided SQL file into your local MySQL server (XAMPP phpMyAdmin).</p>
            <h6 class="text-white mt-4">Local Setup Steps:</h6>
            <ol class="text-light">
                <li>Open XAMPP Control Panel and start <strong>Apache</strong> and <strong>MySQL</strong>.</li>
                <li>Go to <a href="http://localhost/phpmyadmin" target="_blank" class="text-danger fw-bold">http://localhost/phpmyadmin</a>.</li>
                <li>Click on the <strong>Import</strong> tab.</li>
                <li>Choose the file located at: <code>database/cinema_booking.sql</code></li>
                <li>Click <strong>Import</strong> at the bottom.</li>
            </ol>';
    } else {
        $instructions = '
            <p class="text-secondary">Database <code>' . $dbName . '</code> does not exist on your hosting MySQL server.</p>
            <h6 class="text-white mt-4">InfinityFree / Live Hosting Steps:</h6>
            <ol class="text-light">
                <li>Log in to your <strong>InfinityFree Control Panel (vPanel)</strong>.</li>
                <li>Click on <strong>MySQL Databases</strong>.</li>
                <li>Create a database name (note the full name with the <code>if0_xxxxxxx_</code> prefix).</li>
                <li>Update the <code>DB_NAME</code> value in <code>config/database.php</code> to match.</li>
                <li>Click <strong>phpMyAdmin</strong> next to the database and import <code>database/cinema_booking.sql</code>.</li>
            </ol>';
    }

    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Database Setup Required - CINEMA BOOKING</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: #07080c; color: #f3f4f6; font-family: system-ui, -apple-system, sans-serif; display: flex; align-items: center; min-height: 100vh; }
            .card { background: #0e111a; border: 1px solid rgba(255, 0, 85, 0.3); border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.7); }
        </style>
    </head>
    <body>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card p-4 p-md-5">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-danger fs-6 me-2">Setup Required</span>
                            <h3 class="m-0 text-white">Database Not Found</h3>
                        </div>
                        ' . $instructions . '
                        <div class="mt-4">
                            <a href="" class="btn btn-danger px-4 py-2" style="background: linear-gradient(135deg, #ff0055 0%, #7928ca 100%); border:none;">Refresh Page</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Diagnostic screen for connection errors
 */
function renderDatabaseConnectionError(string $message, string $dsn): string {
    global $isLocal;
    $host = htmlspecialchars(DB_HOST);
    $user = htmlspecialchars(DB_USER);

    $tip = '';
    if (!$isLocal) {
        $tip = '
        <div class="alert alert-info bg-dark border-info text-light small mt-3">
            <strong>InfinityFree Tip:</strong> Ensure that:
            <ul class="mb-0 mt-1">
                <li><code>DB_HOST</code> is set to your remote MySQL Hostname (e.g. <code>sql105.infinityfree.com</code>), <strong>never</strong> <code>localhost</code>.</li>
                <li><code>DB_USER</code> matches your vPanel account username (e.g. <code>if0_38472910</code>).</li>
                <li><code>DB_PASS</code> is your vPanel hosting password (not your email login password).</li>
            </ul>
        </div>';
    }

    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Database Connection Error - CINEMA BOOKING</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: #07080c; color: #f3f4f6; font-family: system-ui, -apple-system, sans-serif; display: flex; align-items: center; min-height: 100vh; }
            .card { background: #0e111a; border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 16px; }
        </style>
    </head>
    <body>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card p-4 p-md-5">
                        <h3 class="text-danger mb-3">Database Connection Error</h3>
                        <p class="text-secondary">Could not connect to MySQL server with current credentials.</p>
                        <div class="alert alert-dark border-secondary text-warning small font-monospace">' . htmlspecialchars($message) . '</div>
                        <p class="text-secondary small mb-1">Attempted connection to host <code>' . $host . '</code> with user <code>' . $user . '</code>.</p>
                        ' . $tip . '
                        <p class="text-light small mt-3">Edit <code>config/database.php</code> to verify your credentials.</p>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>';
}
