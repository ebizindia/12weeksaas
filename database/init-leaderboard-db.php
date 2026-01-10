<?php
/**
 * Database Initialization Script for Leaderboard Module
 *
 * This script creates all required tables for the leaderboard and gamification features.
 * Run this script once to set up the database schema.
 */

require_once(__DIR__ . "/../inc.php");

echo "=== 12-Week Year Leaderboard - Database Initialization ===\n\n";

try {
    $conn = \eBizIndia\PDOConn::getInstance();

    // Read the SQL file
    $sql_file = __DIR__ . '/leaderboard-schema.sql';

    if (!file_exists($sql_file)) {
        throw new Exception("SQL schema file not found: {$sql_file}");
    }

    $sql_content = file_get_contents($sql_file);

    // Split the SQL by delimiter changes and regular statements
    $statements = [];
    $current_delimiter = ';';
    $buffer = '';

    // Parse SQL file handling DELIMITER changes
    $lines = explode("\n", $sql_content);
    $in_procedure = false;

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip empty lines and comments
        if (empty($line) || strpos($line, '--') === 0) {
            continue;
        }

        // Check for DELIMITER change
        if (stripos($line, 'DELIMITER') === 0) {
            if (!$in_procedure) {
                // Entering procedure
                $in_procedure = true;
                $current_delimiter = '//';
            } else {
                // Exiting procedure - execute the buffered procedure
                if (!empty(trim($buffer))) {
                    $statements[] = trim($buffer);
                    $buffer = '';
                }
                $in_procedure = false;
                $current_delimiter = ';';
            }
            continue;
        }

        $buffer .= $line . "\n";

        // Check if statement is complete
        if (!$in_procedure && substr(rtrim($line), -1) === $current_delimiter) {
            $statement = trim($buffer);
            if (!empty($statement)) {
                $statements[] = rtrim($statement, $current_delimiter);
                $buffer = '';
            }
        } elseif ($in_procedure && substr(rtrim($line), -2) === '//') {
            // Procedure complete
            $statement = trim($buffer);
            if (!empty($statement)) {
                $statements[] = rtrim($statement, '/');
                $buffer = '';
            }
        }
    }

    // Add any remaining buffer
    if (!empty(trim($buffer))) {
        $statements[] = trim($buffer);
    }

    echo "Found " . count($statements) . " SQL statements to execute.\n\n";

    // Execute each statement
    $success_count = 0;
    $error_count = 0;

    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement)) {
            continue;
        }

        try {
            // Determine what type of statement this is
            $statement_type = 'UNKNOWN';
            if (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE[^`]*`([^`]+)`/', $statement, $matches);
                $statement_type = 'CREATE TABLE: ' . ($matches[1] ?? 'unknown');
            } elseif (stripos($statement, 'INSERT INTO') !== false) {
                preg_match('/INSERT INTO[^`]*`([^`]+)`/', $statement, $matches);
                $statement_type = 'INSERT INTO: ' . ($matches[1] ?? 'unknown');
            } elseif (stripos($statement, 'CREATE PROCEDURE') !== false) {
                preg_match('/CREATE PROCEDURE[^`]*`?([a-zA-Z_]+)`?/', $statement, $matches);
                $statement_type = 'CREATE PROCEDURE: ' . ($matches[1] ?? 'unknown');
            } elseif (stripos($statement, 'DROP PROCEDURE') !== false) {
                preg_match('/DROP PROCEDURE[^`]*`?([a-zA-Z_]+)`?/', $statement, $matches);
                $statement_type = 'DROP PROCEDURE: ' . ($matches[1] ?? 'unknown');
            }

            echo "Executing: {$statement_type}... ";

            $conn->exec($statement);
            echo "✓ SUCCESS\n";
            $success_count++;

        } catch (PDOException $e) {
            echo "✗ FAILED\n";
            echo "  Error: " . $e->getMessage() . "\n";
            $error_count++;

            // Continue with other statements even if one fails
        }
    }

    echo "\n=== Initialization Complete ===\n";
    echo "Successful: {$success_count}\n";
    echo "Failed: {$error_count}\n";

    if ($error_count > 0) {
        echo "\nSome statements failed. Please review the errors above.\n";
        exit(1);
    } else {
        echo "\n✓ All database tables and procedures created successfully!\n";
        echo "\nYou can now access the leaderboard at: /12-week-leaderboard.php\n";
        exit(0);
    }

} catch (Exception $e) {
    echo "\n✗ FATAL ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
