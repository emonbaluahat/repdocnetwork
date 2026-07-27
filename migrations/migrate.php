<?php
/**
 * RepDoc Network — Database Migration Runner
 *
 * Usage:
 *   php migrations/migrate.php              # Run pending migrations
 *   php migrations/migrate.php --seed       # Run migrations + seed data
 *   php migrations/migrate.php --fresh      # Drop all tables and re-run
 *   php migrations/migrate.php --rollback   # Rollback last batch
 */

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/vendor/autoload.php';

$envFile = APP_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            $pos = strpos($line, '=');
            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/database.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

$args = $argv ?? [];
$fresh = in_array('--fresh', $args);
$seed = in_array('--seed', $args);
$rollback = in_array('--rollback', $args);

echo "=== RepDoc Network Migration Runner ===\n\n";

if ($fresh) {
    echo "Dropping all tables...\n";
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        if ($table === 'migrations') continue;
        $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        echo "  Dropped: {$table}\n";
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    $pdo->exec("TRUNCATE TABLE migrations");
    echo "All tables dropped. Migration tracking reset.\n\n";
}

if ($rollback) {
    $lastBatch = (int) $pdo->query("SELECT COALESCE(MAX(batch), 0) FROM migrations")->fetchColumn();
    if ($lastBatch > 0) {
        $rollbackStmt = $pdo->prepare("SELECT migration FROM migrations WHERE batch = :batch ORDER BY id DESC");
        $rollbackStmt->execute(['batch' => $lastBatch]);
        $files = $rollbackStmt->fetchAll(PDO::FETCH_COLUMN);

        echo "Rolling back batch {$lastBatch} (" . count($files) . " migration(s)):\n\n";
        foreach (array_reverse($files) as $file) {
            $pattern = explode('_', $file, 2)[1] ?? $file;
            $pattern = preg_replace('/\.sql$/', '', $pattern);
            echo "  Reverting: {$pattern}\n";
        }

        $cleanup = $pdo->prepare("DELETE FROM migrations WHERE batch = :batch");
        $cleanup->execute(['batch' => $lastBatch]);
        echo "\nRollback completed. Please manually drop tables created in this batch.\n\n";
    } else {
        echo "Nothing to roll back.\n\n";
    }
    exit;
}

$pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `migration` VARCHAR(255) NOT NULL,
    `batch` INT UNSIGNED NOT NULL,
    `executed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$executed = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
$migrationDir = APP_ROOT . '/migrations';
$files = glob($migrationDir . '/*.sql');
sort($files);

$pending = [];
foreach ($files as $file) {
    $filename = basename($file);
    if (!in_array($filename, $executed)) {
        $pending[] = $file;
    }
}

if (empty($pending)) {
    echo "All migrations are up to date.\n\n";
} else {
    $batch = (int) $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations")->fetchColumn();
    $insert = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)");

    echo "Running " . count($pending) . " migration(s) in batch {$batch}:\n\n";

    foreach ($pending as $file) {
        $filename = basename($file);
        echo "  Running: {$filename}... ";

        try {
            $sql = file_get_contents($file);
            $statements = explode(';', $sql);

            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }

            $insert->execute(['migration' => $filename, 'batch' => $batch]);
            echo "OK\n";
        } catch (PDOException $e) {
            while ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo "FAILED\n";
            echo "  Error: " . $e->getMessage() . "\n";
            echo "  File: {$filename}\n\n";
            exit(1);
        }
    }

    echo "\n" . count($pending) . " migration(s) completed successfully.\n";
}

if ($seed) {
    echo "\n=== Seeding Data ===\n\n";
    $seedDir = APP_ROOT . '/seeds';
    $seedFiles = glob($seedDir . '/*.sql');
    sort($seedFiles);

    if (empty($seedFiles)) {
        echo "No seed files found.\n";
    } else {
        foreach ($seedFiles as $file) {
            $filename = basename($file);
            echo "  Seeding: {$filename}... ";
            try {
                $sql = file_get_contents($file);
                $pdo->exec($sql);
                echo "OK\n";
            } catch (PDOException $e) {
                echo "FAILED\n";
                echo "  Error: " . $e->getMessage() . "\n";
                exit(1);
            }
        }
        echo "\nSeeding completed.\n";
    }
}

echo "\nDone.\n";
