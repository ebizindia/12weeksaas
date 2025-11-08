<?php
/**
 * Phase 1 Database Migration Runner
 *
 * This script runs the Phase 1 individual SaaS conversion migration
 *
 * IMPORTANT:
 * - Take a full database backup before running this
 * - Run this script only once
 * - Check the results carefully
 */

// Include configuration
require_once dirname(__DIR__) . '/config.php';
require_once CONST_INCLUDES_DIR . '/ebiz-autoload.php';
require_once CONST_INCLUDES_DIR . '/general-func.php';

// Security: Only allow from command line or localhost
if (php_sapi_name() !== 'cli' && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
    die('This script can only be run from localhost or command line');
}

echo "============================================================================\n";
echo "Phase 1: Individual SaaS Conversion - Database Migration\n";
echo "============================================================================\n\n";

// Ask for confirmation
if (php_sapi_name() === 'cli') {
    // Check if --force flag is provided
    $force = in_array('--force', $argv ?? []);

    if (!$force) {
        echo "WARNING: This will modify your database structure.\n";
        echo "Have you taken a backup? (yes/no): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim($line) != 'yes') {
            echo "Migration cancelled. Please take a backup first.\n";
            echo "Tip: Run with --force flag to skip confirmation\n";
            exit;
        }
        fclose($handle);
    } else {
        echo "⚠ Running with --force flag (skipping confirmation)\n\n";
    }
}

try {
    // Connect to database
    \eBizIndia\PDOConn::connectToDB('mysql');
    $db_conn = \eBizIndia\PDOConn::getConnection();

    echo "✓ Database connection established\n\n";

    // Read migration SQL file
    $migration_file = __DIR__ . '/phase1-individual-saas-migration.sql';

    if (!file_exists($migration_file)) {
        throw new Exception("Migration file not found: $migration_file");
    }

    $sql = file_get_contents($migration_file);

    echo "✓ Migration file loaded\n\n";

    // Remove comments and split into statements
    $sql = preg_replace('/--.*$/m', '', $sql); // Remove single-line comments
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove multi-line comments

    // Split by semicolon but keep transaction statements together
    $statements = [];
    $current_statement = '';
    $lines = explode("\n", $sql);

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        $current_statement .= $line . "\n";

        // Check if line ends with semicolon
        if (substr($line, -1) === ';') {
            $statements[] = trim($current_statement);
            $current_statement = '';
        }
    }

    echo "Executing migration statements...\n\n";

    $success_count = 0;
    $error_count = 0;

    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;

        // Skip certain statements that are for verification only
        if (stripos($statement, 'SELECT "Column') !== false) {
            continue;
        }

        try {
            $db_conn->exec($statement);
            $success_count++;

            // Show progress for key operations
            if (stripos($statement, 'ALTER TABLE') !== false) {
                preg_match('/ALTER TABLE\s+`?(\w+)`?/i', $statement, $matches);
                $table = $matches[1] ?? 'unknown';
                echo "  ✓ Modified table: $table\n";
            } elseif (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE(?:\s+IF NOT EXISTS)?\s+`?(\w+)`?/i', $statement, $matches);
                $table = $matches[1] ?? 'unknown';
                echo "  ✓ Created table: $table\n";
            } elseif (stripos($statement, 'CREATE INDEX') !== false) {
                preg_match('/CREATE INDEX(?:\s+IF NOT EXISTS)?\s+`?(\w+)`?/i', $statement, $matches);
                $index = $matches[1] ?? 'unknown';
                echo "  ✓ Created index: $index\n";
            } elseif (stripos($statement, 'INSERT INTO') !== false) {
                echo "  ✓ Inserted default data\n";
            } elseif (stripos($statement, 'START TRANSACTION') !== false) {
                echo "  ✓ Transaction started\n";
            } elseif (stripos($statement, 'COMMIT') !== false) {
                echo "  ✓ Transaction committed\n";
            }

        } catch (PDOException $e) {
            // Some errors are acceptable (like "column already exists")
            $acceptable_errors = [
                'Duplicate column name',
                'Duplicate key name',
                'already exists',
                'Table .* already exists'
            ];

            $is_acceptable = false;
            foreach ($acceptable_errors as $pattern) {
                if (preg_match("/$pattern/i", $e->getMessage())) {
                    $is_acceptable = true;
                    echo "  ⚠ Skipped (already exists): " . substr($statement, 0, 50) . "...\n";
                    break;
                }
            }

            if (!$is_acceptable) {
                $error_count++;
                echo "  ✗ ERROR: " . $e->getMessage() . "\n";
                echo "    Statement: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }

    echo "\n============================================================================\n";
    echo "Migration Summary:\n";
    echo "============================================================================\n";
    echo "Successful operations: $success_count\n";
    echo "Errors encountered: $error_count\n";

    if ($error_count > 0) {
        echo "\n⚠ WARNING: Some errors occurred. Please review the output above.\n";
    } else {
        echo "\n✓ Migration completed successfully!\n";
    }

    // Run verification queries
    echo "\n============================================================================\n";
    echo "Verification:\n";
    echo "============================================================================\n";

    // Check users table
    $stmt = $db_conn->query("SHOW COLUMNS FROM users LIKE 'account_status'");
    if ($stmt->rowCount() > 0) {
        echo "✓ users.account_status column created\n";
    } else {
        echo "✗ users.account_status column NOT found\n";
    }

    // Check members table
    $stmt = $db_conn->query("SHOW COLUMNS FROM members LIKE 'display_name'");
    if ($stmt->rowCount() > 0) {
        echo "✓ members.display_name column created\n";
    } else {
        echo "✗ members.display_name column NOT found\n";
    }

    // Check user_preferences table
    $stmt = $db_conn->query("SHOW TABLES LIKE 'user_preferences'");
    if ($stmt->rowCount() > 0) {
        echo "✓ user_preferences table created\n";

        // Count preferences
        $stmt = $db_conn->query("SELECT COUNT(*) as cnt FROM user_preferences");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        echo "  └─ $count user preference records created\n";
    } else {
        echo "✗ user_preferences table NOT found\n";
    }

    // Check audit_logs table
    $stmt = $db_conn->query("SHOW TABLES LIKE 'audit_logs'");
    if ($stmt->rowCount() > 0) {
        echo "✓ audit_logs table created\n";
    } else {
        echo "✗ audit_logs table NOT found\n";
    }

    // Check leaderboard_stats modifications
    $stmt = $db_conn->query("SHOW COLUMNS FROM leaderboard_stats LIKE 'is_visible'");
    if ($stmt->rowCount() > 0) {
        echo "✓ leaderboard_stats.is_visible column created\n";
    } else {
        echo "⚠ leaderboard_stats.is_visible column not found (table may not exist yet)\n";
    }

    echo "\n============================================================================\n";
    echo "Next Steps:\n";
    echo "============================================================================\n";
    echo "1. Review the migration output above\n";
    echo "2. Check your database to verify changes\n";
    echo "3. Update config.php with Phase 1 settings\n";
    echo "4. Proceed with code updates\n";
    echo "\n";

} catch (Exception $e) {
    echo "\n✗ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Migration failed. Please check your database configuration.\n";
    exit(1);
}
