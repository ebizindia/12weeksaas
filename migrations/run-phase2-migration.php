<?php
/**
 * Phase 2 Database Migration Runner
 *
 * This script runs the Phase 2 self-service registration migration
 *
 * IMPORTANT:
 * - Take a full database backup before running this
 * - Run this script only once
 * - Check the results carefully
 * - Phase 1 must be completed before running Phase 2
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
echo "Phase 2: Self-Service Registration - Database Migration\n";
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
    $migration_file = __DIR__ . '/phase2-self-service-migration.sql';

    if (!file_exists($migration_file)) {
        throw new Exception("Migration file not found: $migration_file");
    }

    $sql = file_get_contents($migration_file);

    echo "✓ Migration file loaded\n\n";

    echo "Executing migration...\n\n";

    // Execute the entire SQL file (it uses prepared statements for safety)
    try {
        $db_conn->exec($sql);
        echo "✓ Migration SQL executed successfully\n\n";
    } catch (PDOException $e) {
        // If bulk execution fails, try statement by statement
        echo "⚠ Bulk execution failed, trying statement by statement...\n\n";

        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        // Split by semicolon
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if (empty($statement)) continue;
            if (stripos($statement, 'SELECT 1') !== false) continue;

            try {
                $db_conn->exec($statement);

                // Show progress
                if (stripos($statement, 'CREATE TABLE') !== false) {
                    preg_match('/CREATE TABLE(?:\s+IF NOT EXISTS)?\s+`?(\w+)`?/i', $statement, $matches);
                    $table = $matches[1] ?? 'unknown';
                    echo "  ✓ Created table: $table\n";
                } elseif (stripos($statement, 'ALTER TABLE') !== false && stripos($statement, 'ADD COLUMN') !== false) {
                    echo "  ✓ Added column to users table\n";
                } elseif (stripos($statement, 'UPDATE') !== false && stripos($statement, 'users') !== false) {
                    $affected = $db_conn->query("SELECT ROW_COUNT()")->fetchColumn();
                    echo "  ✓ Updated $affected existing users\n";
                }
            } catch (PDOException $e) {
                // Check for acceptable errors
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
                        echo "  ⚠ Skipped (already exists)\n";
                        break;
                    }
                }

                if (!$is_acceptable) {
                    throw $e;
                }
            }
        }
    }

    // Run verification queries
    echo "\n============================================================================\n";
    echo "Verification:\n";
    echo "============================================================================\n";

    // Check new tables
    $tables_to_check = ['email_verifications', 'password_resets', 'email_log', 'rate_limits'];
    foreach ($tables_to_check as $table) {
        $stmt = $db_conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ $table table created\n";
        } else {
            echo "✗ $table table NOT found\n";
        }
    }

    // Check new columns in users table
    $columns_to_check = [
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'signup_ip',
        'signup_source',
        'terms_accepted_at',
        'onboarding_completed'
    ];

    foreach ($columns_to_check as $column) {
        $stmt = $db_conn->query("SHOW COLUMNS FROM users LIKE '$column'");
        if ($stmt->rowCount() > 0) {
            echo "✓ users.$column column created\n";
        } else {
            echo "✗ users.$column column NOT found\n";
        }
    }

    // Check if existing users were updated
    $stmt = $db_conn->query("SELECT COUNT(*) as cnt FROM users WHERE email_verified_at IS NOT NULL");
    $verified_count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    echo "\n✓ $verified_count existing user(s) marked as verified\n";

    echo "\n============================================================================\n";
    echo "Migration Complete!\n";
    echo "============================================================================\n";
    echo "\nNext Steps:\n";
    echo "1. Update config.php with Phase 2 settings (SMTP, reCAPTCHA)\n";
    echo "2. Deploy Phase 2 code files\n";
    echo "3. Test signup, email verification, and password reset\n";
    echo "\n";

} catch (Exception $e) {
    echo "\n✗ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Migration failed. Please check your database configuration.\n";
    exit(1);
}
