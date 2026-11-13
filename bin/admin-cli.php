<?php
/**
 * CINEMA BOOKING - Administrator CLI Management Utility
 * Safe, non-destructive tool for creating and resetting admin credentials.
 * 
 * Usage:
 *   php bin/admin-cli.php create --username=admin --email=admin@example.com --name="Super Admin" --password="YourSecurePassword" [--role=superadmin]
 *   php bin/admin-cli.php reset-password --username=admin --password="YourNewPassword"
 *   php bin/admin-cli.php list
 *   php bin/admin-cli.php help
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access denied: This script can only be run from the command line.\n");
}

require_once __DIR__ . '/../includes/functions.php';

function parse_cli_args(array $argv): array {
    $args = ['command' => $argv[1] ?? 'help', 'options' => []];
    for ($i = 2; $i < count($argv); $i++) {
        if (str_starts_with($argv[$i], '--')) {
            $parts = explode('=', substr($argv[$i], 2), 2);
            $key = $parts[0];
            $val = $parts[1] ?? true;
            $args['options'][$key] = $val;
        }
    }
    return $args;
}

$parsed = parse_cli_args($argv);
$command = strtolower($parsed['command']);
$options = $parsed['options'];

if ($command === 'help' || in_array($command, ['-h', '--help'])) {
    echo "CINEMA BOOKING - Administrator CLI Management Utility\n\n";
    echo "Available Commands:\n";
    echo "  create          Create a new administrator account\n";
    echo "                  Flags: --username=... --email=... --name=\"...\" --password=\"...\" [--role=superadmin|admin|manager]\n\n";
    echo "  reset-password  Reset an administrator's password\n";
    echo "                  Flags: --username=... --password=\"...\"\n\n";
    echo "  list            List all configured administrator accounts (credentials never printed)\n\n";
    echo "  help            Show this usage information\n";
    exit(0);
}

$pdo = getDBConnection();

switch ($command) {
    case 'create':
        $username = trim($options['username'] ?? '');
        $email = trim($options['email'] ?? '');
        $name = trim($options['name'] ?? 'Administrator');
        $password = $options['password'] ?? '';
        $role = in_array($options['role'] ?? '', ['superadmin', 'admin', 'manager']) ? $options['role'] : 'admin';

        if (empty($username) || empty($email) || empty($password)) {
            echo "[ERROR] Missing required arguments.\n";
            echo "Usage: php bin/admin-cli.php create --username=<username> --email=<email> --name=\"<name>\" --password=\"<password>\" [--role=superadmin|admin|manager]\n";
            exit(1);
        }

        if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $username)) {
            echo "[ERROR] Invalid username. Must be at least 3 alphanumeric characters.\n";
            exit(1);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "[ERROR] Invalid email address: {$email}\n";
            exit(1);
        }

        if (strlen($password) < 8) {
            echo "[ERROR] Password must be at least 8 characters long.\n";
            exit(1);
        }

        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id, username, email FROM admins WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $email]);
        if ($existing = $stmt->fetch()) {
            if (strtolower($existing['username']) === strtolower($username)) {
                echo "[ERROR] Administrator username '{$username}' already exists (ID: {$existing['id']}).\n";
            } else {
                echo "[ERROR] Administrator email '{$email}' already exists (ID: {$existing['id']}).\n";
            }
            exit(1);
        }

        // Hash using PHP's current strongest default algorithm (BCrypt/Argon2)
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO admins (username, full_name, email, password, role, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'active', NOW())
        ");
        $stmt->execute([$username, $name, $email, $hash, $role]);
        $newId = $pdo->lastInsertId();

        echo "[SUCCESS] Administrator created successfully!\n";
        echo "  ID:       {$newId}\n";
        echo "  Username: {$username}\n";
        echo "  Name:     {$name}\n";
        echo "  Email:    {$email}\n";
        echo "  Role:     {$role}\n";
        echo "  Status:   active\n";
        break;

    case 'reset-password':
        $username = trim($options['username'] ?? '');
        $password = $options['password'] ?? '';

        if (empty($username) || empty($password)) {
            echo "[ERROR] Missing required arguments.\n";
            echo "Usage: php bin/admin-cli.php reset-password --username=<username> --password=\"<new_password>\"\n";
            exit(1);
        }

        if (strlen($password) < 8) {
            echo "[ERROR] Password must be at least 8 characters long.\n";
            exit(1);
        }

        $stmt = $pdo->prepare("SELECT id, username, full_name, email FROM admins WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if (!$admin) {
            echo "[ERROR] No administrator found matching username or email '{$username}'.\n";
            exit(1);
        }

        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $up = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $up->execute([$newHash, $admin['id']]);

        echo "[SUCCESS] Password for administrator '{$admin['username']}' (ID: {$admin['id']}) was updated successfully.\n";
        break;

    case 'list':
        $stmt = $pdo->query("SELECT id, username, full_name, email, role, status, created_at FROM admins ORDER BY id ASC");
        $admins = $stmt->fetchAll();

        echo "----------------------------------------------------------------------------------------------------\n";
        printf("%-4s | %-16s | %-22s | %-26s | %-10s | %-8s\n", "ID", "Username", "Name", "Email", "Role", "Status");
        echo "----------------------------------------------------------------------------------------------------\n";
        foreach ($admins as $a) {
            printf(
                "%-4d | %-16s | %-22s | %-26s | %-10s | %-8s\n",
                $a['id'],
                substr($a['username'], 0, 16),
                substr($a['full_name'], 0, 22),
                substr($a['email'], 0, 26),
                $a['role'],
                $a['status']
            );
        }
        echo "----------------------------------------------------------------------------------------------------\n";
        echo "Total: " . count($admins) . " administrator(s)\n";
        break;

    case 'help':
    default:
        echo "CINEMA BOOKING - Administrator CLI Management Utility\n\n";
        echo "Available Commands:\n";
        echo "  create          Create a new administrator account\n";
        echo "                  Flags: --username=... --email=... --name=\"...\" --password=\"...\" [--role=superadmin|admin|manager]\n\n";
        echo "  reset-password  Reset an administrator's password\n";
        echo "                  Flags: --username=... --password=\"...\"\n\n";
        echo "  list            List all configured administrator accounts (credentials never printed)\n\n";
        echo "  help            Show this usage information\n";
        break;
}
