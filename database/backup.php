<?php
/**
 * CINEMA BOOKING - Database Backup Utility
 * Generates a timestamped SQL backup before migrations or maintenance.
 * 
 * Usage:
 *   php database/backup.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access denied: This script can only be run from the command line.\n");
}

require_once __DIR__ . '/../config/database.php';

$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0700, true);
}

try {
    $pdo = getDBConnection();
} catch (Exception $e) {
    echo "[ERROR] Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$timestamp = date('Ymd_His');
$filename = "backup_cinema_booking_{$timestamp}.sql";
$filePath = $backupDir . '/' . $filename;

echo "[BACKUP] Creating database backup...\n";
echo "  Target: database/backups/{$filename}\n";

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

$handle = fopen($filePath, 'w');
if (!$handle) {
    echo "[ERROR] Cannot open backup file for writing.\n";
    exit(1);
}

fwrite($handle, "-- ========================================================\n");
fwrite($handle, "-- CINEMA BOOKING DATABASE BACKUP\n");
fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
fwrite($handle, "-- Database:  " . DB_NAME . "\n");
fwrite($handle, "-- Host:      " . DB_HOST . "\n");
fwrite($handle, "-- ========================================================\n\n");
fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

foreach ($tables as $table) {
    echo "  -> Backing up table: {$table}... ";

    // Dump Schema
    $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
    fwrite($handle, "\n-- --------------------------------------------------------\n");
    fwrite($handle, "-- Structure for table `{$table}`\n");
    fwrite($handle, "-- --------------------------------------------------------\n");
    fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
    fwrite($handle, $createStmt[1] . ";\n\n");

    // Dump Data
    $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
        fwrite($handle, "-- Data for table `{$table}` (" . count($rows) . " rows)\n");
        fwrite($handle, "INSERT INTO `{$table}` VALUES \n");

        $rowStrings = [];
        foreach ($rows as $row) {
            $values = array_map(function ($val) use ($pdo) {
                if ($val === null) return 'NULL';
                return $pdo->quote($val);
            }, array_values($row));
            $rowStrings[] = '(' . implode(', ', $values) . ')';
        }
        fwrite($handle, implode(",\n", $rowStrings) . ";\n");
    }

    echo "DONE (" . count($rows) . " rows)\n";
}

fwrite($handle, "\nSET FOREIGN_KEY_CHECKS = 1;\n");
fclose($handle);

$fileSizeBytes = filesize($filePath);
$fileSizeKb = round($fileSizeBytes / 1024, 2);

echo "[SUCCESS] Database backup completed successfully!\n";
echo "  File: {$filePath} ({$fileSizeKb} KB)\n";

