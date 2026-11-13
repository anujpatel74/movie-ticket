<?php
/**
 * CINEMA BOOKING - Database Configuration
 * Supports Railway, Cloud Containers, Docker, and Local XAMPP/Localhost environments.
 */

// --------------------------------------------------------
// 1. Resolve Credentials from Environment Variables
// --------------------------------------------------------

// Priority 1: Full URL connection string (DATABASE_URL or MYSQL_URL)
$databaseUrl = getenv('DATABASE_URL') ?: (getenv('MYSQL_URL') ?: ($_ENV['DATABASE_URL'] ?? ($_ENV['MYSQL_URL'] ?? '')));
$parsedUrl = !empty($databaseUrl) ? parse_url($databaseUrl) : false;

// Priority 2: Railway environment variables
// Railway automatically sets: MYSQLHOST, MYSQLPORT, MYSQLDATABASE, MYSQLUSER, MYSQLPASSWORD
$envHost = getenv('MYSQLHOST') ?: ($_ENV['MYSQLHOST'] ?? ($_SERVER['MYSQLHOST'] ?? ''));
$envPort = getenv('MYSQLPORT') ?: ($_ENV['MYSQLPORT'] ?? ($_SERVER['MYSQLPORT'] ?? ''));
$envName = getenv('MYSQLDATABASE') ?: ($_ENV['MYSQLDATABASE'] ?? ($_SERVER['MYSQLDATABASE'] ?? ''));
$envUser = getenv('MYSQLUSER') ?: ($_ENV['MYSQLUSER'] ?? ($_SERVER['MYSQLUSER'] ?? ''));
$envPass = getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : ($_ENV['MYSQLPASSWORD'] ?? ($_SERVER['MYSQLPASSWORD'] ?? null));

// Priority 3: Standard DB_* environment variables
if (empty($envHost)) {
    $envHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($_SERVER['DB_HOST'] ?? ''));
}
if (empty($envPort)) {
    $envPort = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? ($_SERVER['DB_PORT'] ?? ''));
}
if (empty($envName)) {
    $envName = getenv('DB_NAME') ?: (getenv('DB_DATABASE') ?: ($_ENV['DB_NAME'] ?? ($_SERVER['DB_NAME'] ?? '')));
}
if (empty($envUser)) {
    $envUser = getenv('DB_USER') ?: (getenv('DB_USERNAME') ?: ($_ENV['DB_USER'] ?? ($_SERVER['DB_USER'] ?? '')));
}
if ($envPass === null) {
    $envPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : ($_ENV['DB_PASS'] ?? ($_ENV['DB_PASSWORD'] ?? ($_SERVER['DB_PASS'] ?? null))));
}

// Check if URL provided values
if ($parsedUrl) {
    if (empty($envHost) && !empty($parsedUrl['host'])) $envHost = $parsedUrl['host'];
    if (empty($envPort) && !empty($parsedUrl['port'])) $envPort = (string)$parsedUrl['port'];
    if (empty($envName) && !empty($parsedUrl['path'])) $envName = ltrim($parsedUrl['path'], '/');
    if (empty($envUser) && !empty($parsedUrl['user'])) $envUser = $parsedUrl['user'];
    if ($envPass === null && isset($parsedUrl['pass'])) $envPass = $parsedUrl['pass'];
}

// --------------------------------------------------------
// 2. Environment Auto-Detection & Definition
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
    (php_sapi_name() === 'cli' && empty($envHost))
);

$hasEnvConfig = !empty($envHost);
$isProduction = $hasEnvConfig || !$isLocal;

if ($hasEnvConfig) {
    // Railway / Docker / Cloud production configuration from environment
    if (!defined('DB_HOST')) define('DB_HOST', $envHost);
    if (!defined('DB_NAME')) define('DB_NAME', !empty($envName) ? $envName : 'cinema_booking');
    if (!defined('DB_USER')) define('DB_USER', !empty($envUser) ? $envUser : 'root');
    if (!defined('DB_PASS')) define('DB_PASS', $envPass !== null ? $envPass : '');
    if (!defined('DB_PORT')) define('DB_PORT', !empty($envPort) ? $envPort : '3306');
} elseif ($isLocal) {
    // Local development configuration (XAMPP / Localhost)
    if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
    if (!defined('DB_NAME')) define('DB_NAME', 'cinema_booking');
    if (!defined('DB_USER')) define('DB_USER', 'root');
    if (!defined('DB_PASS')) define('DB_PASS', '');
    if (!defined('DB_PORT')) define('DB_PORT', '3306');
} else {
    // Remote / Production without provided environment variables (Strictly no hardcoded credentials)
    if (!defined('DB_HOST')) define('DB_HOST', '');
    if (!defined('DB_NAME')) define('DB_NAME', '');
    if (!defined('DB_USER')) define('DB_USER', '');
    if (!defined('DB_PASS')) define('DB_PASS', '');
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
        if (empty(DB_HOST) || empty(DB_NAME)) {
            error_log("Database Configuration Error: Missing DB_HOST or DB_NAME environment variables.");
            http_response_code(500);
            die(renderDatabaseConnectionError('Database environment configuration is missing on this host.'));
        }

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
            // Keep prepared statement emulation disabled for security against SQL injection
            PDO::ATTR_EMULATE_PREPARES   => false,
            // Keep persistent connections disabled to prevent exhaustion of MySQL connection limits
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log technical details securely on the server
            error_log("Database Connection Failure: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
            http_response_code(500);

            // Check if database does not exist
            if ($e->getCode() == 1049) {
                die(renderDatabaseMissingError());
            }
            // General connection error - safe user display
            die(renderDatabaseConnectionError($e->getMessage()));
        }
    }

    return $pdo;
}

/**
 * Diagnostic screen when the database cannot be found
 */
function renderDatabaseMissingError(): string {
    global $isProduction;

    if ($isProduction) {
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Service Unavailable - CINEMA BOOKING</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { background: #06070a; color: #f3f4f6; font-family: system-ui, -apple-system, sans-serif; display: flex; align-items: center; min-height: 100vh; }
                .card { background: #0f121c; border: 1px solid rgba(255, 0, 85, 0.3); border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.8); }
            </style>
        </head>
        <body>
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-7 col-lg-6">
                        <div class="card p-4 p-md-5 text-center">
                            <span class="fs-1 text-danger mb-3 d-inline-block"><i class="bi bi-database-slash"></i></span>
                            <h3 class="text-white fw-bold mb-2">Service Setup In Progress</h3>
                            <p class="text-secondary small mb-4">The application database is currently initializing or undergoing routine maintenance. Please check back in a few moments.</p>
                            <div>
                                <a href="" class="btn btn-danger px-4 py-2 rounded-pill fw-semibold" style="background: linear-gradient(135deg, #ff0055 0%, #7928ca 100%); border:none;">Retry Connection</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>';
    }

    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Database Setup Required - Local Development</title>
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
                            <h3 class="m-0 text-white">Database <code>cinema_booking</code> Not Found</h3>
                        </div>
                        <p class="text-secondary">Please import the provided SQL file into your local MySQL server (XAMPP phpMyAdmin).</p>
                        <h6 class="text-white mt-4">Local Setup Steps:</h6>
                        <ol class="text-light">
                            <li>Open XAMPP Control Panel and start <strong>Apache</strong> and <strong>MySQL</strong>.</li>
                            <li>Go to <a href="http://localhost/phpmyadmin" target="_blank" class="text-danger fw-bold">http://localhost/phpmyadmin</a>.</li>
                            <li>Click on the <strong>Import</strong> tab.</li>
                            <li>Choose the file located at: <code>database/cinema_booking.sql</code></li>
                            <li>Click <strong>Import</strong> at the bottom.</li>
                        </ol>
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
function renderDatabaseConnectionError(string $debugMessage = ''): string {
    global $isProduction;

    if ($isProduction) {
        // Safe, generic production error page: NEVER expose host, user, password, or DSN
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Service Unavailable - CINEMA BOOKING</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { background: #06070a; color: #f3f4f6; font-family: system-ui, -apple-system, sans-serif; display: flex; align-items: center; min-height: 100vh; }
                .card { background: #0f121c; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.8); }
            </style>
        </head>
        <body>
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-7 col-lg-6">
                        <div class="card p-4 p-md-5 text-center">
                            <span class="fs-1 text-danger mb-3 d-inline-block"><i class="bi bi-exclamation-octagon-fill"></i></span>
                            <h3 class="text-white fw-bold mb-2">Service Temporarily Unavailable</h3>
                            <p class="text-secondary small mb-4">We are experiencing technical difficulties establishing a connection to our database. Our systems have logged this issue. Please try again in a few moments.</p>
                            <div>
                                <a href="" class="btn btn-outline-danger px-4 py-2 rounded-pill fw-semibold">Refresh Page</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>';
    }

    // Localhost debug screen for developer assistance
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Database Connection Error - Local Development</title>
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
                        <h3 class="text-danger mb-3">Database Connection Error (Local)</h3>
                        <p class="text-secondary">Could not connect to MySQL server with current local configuration.</p>
                        ' . (!empty($debugMessage) ? '<div class="alert alert-dark border-secondary text-warning small font-monospace">' . htmlspecialchars($debugMessage) . '</div>' : '') . '
                        <p class="text-secondary small mb-1">Host: <code>' . htmlspecialchars(DB_HOST) . '</code> | Port: <code>' . htmlspecialchars(DB_PORT) . '</code> | Database: <code>' . htmlspecialchars(DB_NAME) . '</code></p>
                        <p class="text-light small mt-3">Ensure MySQL is running in XAMPP and check credentials in <code>config/database.php</code> or environment variables.</p>
                        <div class="mt-4">
                            <a href="" class="btn btn-danger px-4 py-2">Retry</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>';
}
