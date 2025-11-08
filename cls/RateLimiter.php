<?php
/**
 * RateLimiter Class
 *
 * Prevents brute force attacks and spam by limiting:
 * - Signup attempts per IP
 * - Login attempts per IP
 * - Password reset requests per email
 * - Email verification resends
 */

namespace eBizIndia;

use PDO;

class RateLimiter
{
    /**
     * Check if an action is allowed (within rate limit)
     *
     * @param string $identifier Identifier (IP address, email, etc.)
     * @param string $action Action name (signup, login, password_reset, etc.)
     * @param int $max_attempts Maximum allowed attempts
     * @param int $window_minutes Time window in minutes
     * @return array ['allowed' => bool, 'remaining' => int, 'blocked_until' => string|null]
     */
    public static function check($identifier, $action, $max_attempts, $window_minutes)
    {
        try {
            \eBizIndia\PDOConn::connectToDB('mysql');
            $db_conn = \eBizIndia\PDOConn::getConnection();

            $table = CONST_TBL_PREFIX . 'rate_limits';

            // Check if currently blocked
            $check_sql = "SELECT * FROM `$table`
                         WHERE `identifier` = :identifier
                           AND `action` = :action
                         LIMIT 1";

            $stmt = $db_conn->prepare($check_sql);
            $stmt->execute([
                ':identifier' => $identifier,
                ':action' => $action
            ]);

            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            $now = new \DateTime();

            // If blocked, check if block has expired
            if ($record && $record['blocked_until']) {
                $blocked_until = new \DateTime($record['blocked_until']);
                if ($now < $blocked_until) {
                    return [
                        'allowed' => false,
                        'remaining' => 0,
                        'blocked_until' => $record['blocked_until'],
                        'message' => "Too many attempts. Please try again after " . $blocked_until->format('H:i:s')
                    ];
                } else {
                    // Block expired, reset the record
                    self::clear($identifier, $action);
                    $record = null;
                }
            }

            // If no record or window expired, allow
            if (!$record) {
                return [
                    'allowed' => true,
                    'remaining' => $max_attempts - 1,
                    'blocked_until' => null
                ];
            }

            $window_start = new \DateTime($record['window_start']);
            $window_end = clone $window_start;
            $window_end->add(new \DateInterval("PT{$window_minutes}M"));

            // Check if we're still within the window
            if ($now < $window_end) {
                // Within window - check attempts
                if ($record['attempts'] >= $max_attempts) {
                    // Block for remaining window time
                    $block_until = $window_end->format('Y-m-d H:i:s');

                    $update_sql = "UPDATE `$table`
                                  SET `blocked_until` = :blocked_until,
                                      `updated_at` = NOW()
                                  WHERE `id` = :id";

                    $stmt = $db_conn->prepare($update_sql);
                    $stmt->execute([
                        ':blocked_until' => $block_until,
                        ':id' => $record['id']
                    ]);

                    return [
                        'allowed' => false,
                        'remaining' => 0,
                        'blocked_until' => $block_until,
                        'message' => "Too many attempts. Please try again after " . $window_end->format('H:i:s')
                    ];
                }

                // Still have attempts left
                return [
                    'allowed' => true,
                    'remaining' => $max_attempts - $record['attempts'] - 1,
                    'blocked_until' => null
                ];
            } else {
                // Window expired - reset
                self::clear($identifier, $action);
                return [
                    'allowed' => true,
                    'remaining' => $max_attempts - 1,
                    'blocked_until' => null
                ];
            }

        } catch (\Exception $e) {
            // On error, allow the action (fail open)
            error_log("RateLimiter error: " . $e->getMessage());
            return [
                'allowed' => true,
                'remaining' => $max_attempts,
                'blocked_until' => null
            ];
        }
    }

    /**
     * Record an attempt
     *
     * @param string $identifier Identifier (IP, email, etc.)
     * @param string $action Action name
     * @return bool Success
     */
    public static function recordAttempt($identifier, $action)
    {
        try {
            \eBizIndia\PDOConn::connectToDB('mysql');
            $db_conn = \eBizIndia\PDOConn::getConnection();

            $table = CONST_TBL_PREFIX . 'rate_limits';

            // Check if record exists
            $check_sql = "SELECT * FROM `$table`
                         WHERE `identifier` = :identifier
                           AND `action` = :action
                         LIMIT 1";

            $stmt = $db_conn->prepare($check_sql);
            $stmt->execute([
                ':identifier' => $identifier,
                ':action' => $action
            ]);

            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($record) {
                // Increment attempts
                $update_sql = "UPDATE `$table`
                              SET `attempts` = `attempts` + 1,
                                  `updated_at` = NOW()
                              WHERE `id` = :id";

                $stmt = $db_conn->prepare($update_sql);
                $stmt->execute([':id' => $record['id']]);
            } else {
                // Create new record
                $insert_sql = "INSERT INTO `$table`
                              (`identifier`, `action`, `attempts`, `window_start`)
                              VALUES (:identifier, :action, 1, NOW())";

                $stmt = $db_conn->prepare($insert_sql);
                $stmt->execute([
                    ':identifier' => $identifier,
                    ':action' => $action
                ]);
            }

            return true;

        } catch (\Exception $e) {
            error_log("RateLimiter recordAttempt error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Block an identifier for a specific time
     *
     * @param string $identifier Identifier
     * @param string $action Action name
     * @param int $minutes Minutes to block
     * @return bool Success
     */
    public static function block($identifier, $action, $minutes)
    {
        try {
            \eBizIndia\PDOConn::connectToDB('mysql');
            $db_conn = \eBizIndia\PDOConn::getConnection();

            $table = CONST_TBL_PREFIX . 'rate_limits';

            $blocked_until = new \DateTime();
            $blocked_until->add(new \DateInterval("PT{$minutes}M"));

            $sql = "INSERT INTO `$table`
                    (`identifier`, `action`, `attempts`, `window_start`, `blocked_until`)
                    VALUES (:identifier, :action, 999, NOW(), :blocked_until)
                    ON DUPLICATE KEY UPDATE
                    `blocked_until` = :blocked_until,
                    `attempts` = 999,
                    `updated_at` = NOW()";

            $stmt = $db_conn->prepare($sql);
            $stmt->execute([
                ':identifier' => $identifier,
                ':action' => $action,
                ':blocked_until' => $blocked_until->format('Y-m-d H:i:s')
            ]);

            return true;

        } catch (\Exception $e) {
            error_log("RateLimiter block error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear rate limit for an identifier (after successful action)
     *
     * @param string $identifier Identifier
     * @param string $action Action name
     * @return bool Success
     */
    public static function clear($identifier, $action)
    {
        try {
            \eBizIndia\PDOConn::connectToDB('mysql');
            $db_conn = \eBizIndia\PDOConn::getConnection();

            $table = CONST_TBL_PREFIX . 'rate_limits';

            $sql = "DELETE FROM `$table`
                    WHERE `identifier` = :identifier
                      AND `action` = :action";

            $stmt = $db_conn->prepare($sql);
            $stmt->execute([
                ':identifier' => $identifier,
                ':action' => $action
            ]);

            return true;

        } catch (\Exception $e) {
            error_log("RateLimiter clear error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get remaining attempts
     *
     * @param string $identifier Identifier
     * @param string $action Action name
     * @param int $max_attempts Maximum attempts
     * @return int Remaining attempts
     */
    public static function getRemainingAttempts($identifier, $action, $max_attempts)
    {
        $result = self::check($identifier, $action, $max_attempts, 60);
        return $result['remaining'];
    }

    /**
     * Clean up old rate limit records (cron job)
     * Delete records older than 24 hours with no block
     *
     * @return int Number of deleted records
     */
    public static function cleanup()
    {
        try {
            \eBizIndia\PDOConn::connectToDB('mysql');
            $db_conn = \eBizIndia\PDOConn::getConnection();

            $table = CONST_TBL_PREFIX . 'rate_limits';

            $sql = "DELETE FROM `$table`
                    WHERE `window_start` < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                      AND `blocked_until` IS NULL";

            $stmt = $db_conn->prepare($sql);
            $stmt->execute();

            return $stmt->rowCount();

        } catch (\Exception $e) {
            error_log("RateLimiter cleanup error: " . $e->getMessage());
            return 0;
        }
    }
}
