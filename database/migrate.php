<?php
/**
 * CINEMA BOOKING - Database Migration Runner
 * Safe, idempotent, non-destructive migration engine.
 * 
 * Usage:
 *   php database/migrate.php         # Run all pending migrations
 *   php database/migrate.php status  # View migration status
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access denied: This script can only be run from the command line.\n");
}

require_once __DIR__ . '/../config/database.php';

$action = $argv[1] ?? 'migrate';

try {
    $pdo = getDBConnection();
} catch (Exception $e) {
    echo "[ERROR] Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// 1. Ensure migrations tracking table exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `migrations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `migration` VARCHAR(255) NOT NULL UNIQUE,
        `batch` INT NOT NULL,
        `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// 2. Discover migration files
$migrationDir = __DIR__ . '/migrations';
if (!is_dir($migrationDir)) {
    mkdir($migrationDir, 0755, true);
}

$files = glob($migrationDir . '/*.sql');
sort($files);

// 3. Fetch applied migrations
$appliedStmt = $pdo->query("SELECT migration, batch, applied_at FROM migrations ORDER BY id ASC");
$appliedRows = $appliedStmt->fetchAll();
$appliedMap = [];
foreach ($appliedRows as $row) {
    $appliedMap[$row['migration']] = $row;
}

$maxBatch = (int)$pdo->query("SELECT COALESCE(MAX(batch), 0) FROM migrations")->fetchColumn();

// Handle 'status' command
if ($action === 'status') {
    echo "=================================================================================\n";
    echo "CINEMA BOOKING - Database Migration Status\n";
    echo "=================================================================================\n";
    printf("%-35s | %-10s | %-6s | %-20s\n", "Migration File", "Status", "Batch", "Applied At");
    echo "---------------------------------------------------------------------------------\n";

    if (empty($files)) {
        echo "No migration files found in database/migrations/\n";
    }

    foreach ($files as $file) {
        $name = basename($file);
        if (isset($appliedMap[$name])) {
            printf("%-35s | \033[32mApplied\033[0m    | %-6d | %-20s\n", $name, $appliedMap[$name]['batch'], $appliedMap[$name]['applied_at']);
        } else {
            printf("%-35s | \033[33mPending\033[0m    | %-6s | %-20s\n", $name, '-', '-');
        }
    }
    echo "=================================================================================\n";
    exit(0);
}

// Handle 'migrate' command
$newBatch = $maxBatch + 1;
$pending = [];

foreach ($files as $file) {
    $name = basename($file);
    if (!isset($appliedMap[$name])) {
        $pending[] = $file;
    }
}

if (empty($pending)) {
    echo "[MIGRATE] Database is already up to date. No pending migrations.\n";
    exit(0);
}

echo "[MIGRATE] Running " . count($pending) . " pending migration(s) (Batch {$newBatch})...\n";

foreach ($pending as $filePath) {
    $filename = basename($filePath);
    echo "  -> Applying: {$filename}... ";

    $sqlContent = file_get_contents($filePath);
    if ($sqlContent === false) {
        echo "FAILED (cannot read file)\n";
        exit(1);
    }

    // Split SQL by statement (handles semicolons followed by newline or end of file)
    $statements = array_filter(
        array_map('trim', preg_split('/;\s*(\r\n|\n|$)/', $sqlContent)),
        fn($st) => !empty($st) && !str_starts_with($st, '--') && !str_starts_with($st, '/*')
    );

    try {
        $pdo->beginTransaction();

        foreach ($statements as $stmtSql) {
            // Remove leading comment lines if any
            $cleaned = preg_replace('/^--[^\n]*\n?/m', '', $stmtSql);
            $cleaned = trim($cleaned);
            if (!empty($cleaned)) {
                $pdo->exec($cleaned);
            }
        }

        // Record migration in database
        $recordStmt = $pdo->prepare("INSERT INTO migrations (migration, batch, applied_at) VALUES (?, ?, NOW())");
        $recordStmt->execute([$filename, $newBatch]);

        $pdo->commit();
        echo "DONE\n";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "FAILED!\n";
        echo "[ERROR] Migration failed in {$filename}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "[MIGRATE] All migrations applied successfully!\n";

