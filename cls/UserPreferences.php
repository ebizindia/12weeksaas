<?php
namespace eBizIndia;

use \PDO;
use \Exception;

/**
 * UserPreferences Class
 *
 * Manages individual user preferences and settings for SaaS mode
 *
 * Phase 1: Individual SaaS Conversion
 * Stores user-specific preferences like date format, timezone, email notifications, etc.
 */
class UserPreferences
{
    /**
     * Get user preferences
     *
     * @param int $user_id User ID
     * @return array User preferences or defaults
     */
    public static function get($user_id)
    {
        $user_id = (int)$user_id;

        if (empty($user_id)) {
            return self::getDefaults();
        }

        try {
            $db_conn = PDOConn::getConnection();

            $sql = "SELECT * FROM `" . CONST_TBL_PREFIX . "user_preferences`
                    WHERE `user_id` = :user_id LIMIT 1";

            $stmt = $db_conn->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);

            $preferences = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($preferences) {
                return $preferences;
            } else {
                // No preferences found, create defaults
                self::createDefaults($user_id);
                return self::get($user_id); // Recursive call to get newly created prefs
            }

        } catch (Exception $e) {
            logErrorInFile(time(), 'UserPreferences::get', $e->getMessage());
            return self::getDefaults();
        }
    }

    /**
     * Update user preferences
     *
     * @param int $user_id User ID
     * @param array $preferences Preferences to update
     * @return bool Success status
     */
    public static function update($user_id, $preferences)
    {
        $user_id = (int)$user_id;

        if (empty($user_id)) {
            return false;
        }

        try {
            $db_conn = PDOConn::getConnection();

            // Build UPDATE query dynamically based on provided preferences
            $allowed_fields = [
                'date_format',
                'time_zone',
                'email_weekly_summary',
                'email_achievements',
                'email_reminders',
                'theme',
                'items_per_page'
            ];

            $update_fields = [];
            $params = [':user_id' => $user_id];

            foreach ($allowed_fields as $field) {
                if (isset($preferences[$field])) {
                    $update_fields[] = "`$field` = :$field";
                    $params[":$field"] = $preferences[$field];
                }
            }

            if (empty($update_fields)) {
                return false; // Nothing to update
            }

            // Use INSERT ... ON DUPLICATE KEY UPDATE for upsert behavior
            $sql = "INSERT INTO `" . CONST_TBL_PREFIX . "user_preferences`
                    (`user_id`, " . implode(', ', array_map(function($f) {
                        return "`" . str_replace('`', '', $f) . "`";
                    }, array_keys(array_filter($params, function($k) {
                        return $k !== ':user_id';
                    }, ARRAY_FILTER_USE_KEY)))) . ")
                    VALUES (:user_id, " . implode(', ', array_map(function($k) {
                        return $k;
                    }, array_filter(array_keys($params), function($k) {
                        return $k !== ':user_id';
                    }))) . ")
                    ON DUPLICATE KEY UPDATE " . implode(', ', $update_fields);

            $stmt = $db_conn->prepare($sql);
            $result = $stmt->execute($params);

            return $result;

        } catch (Exception $e) {
            logErrorInFile(time(), 'UserPreferences::update', $e->getMessage());
            return false;
        }
    }

    /**
     * Create default preferences for a user
     *
     * @param int $user_id User ID
     * @return bool Success status
     */
    public static function createDefaults($user_id)
    {
        $user_id = (int)$user_id;

        if (empty($user_id)) {
            return false;
        }

        try {
            $db_conn = PDOConn::getConnection();

            $sql = "INSERT INTO `" . CONST_TBL_PREFIX . "user_preferences`
                    (`user_id`) VALUES (:user_id)
                    ON DUPLICATE KEY UPDATE `user_id` = `user_id`"; // No-op if exists

            $stmt = $db_conn->prepare($sql);
            $result = $stmt->execute([':user_id' => $user_id]);

            return $result;

        } catch (Exception $e) {
            logErrorInFile(time(), 'UserPreferences::createDefaults', $e->getMessage());
            return false;
        }
    }

    /**
     * Get default preferences (fallback)
     *
     * @return array Default preferences
     */
    public static function getDefaults()
    {
        return [
            'user_id' => 0,
            'date_format' => 'd-m-Y',
            'time_zone' => CONST_TIME_ZONE ?? 'Asia/Kolkata',
            'email_weekly_summary' => 1,
            'email_achievements' => 1,
            'email_reminders' => 1,
            'theme' => 'light',
            'items_per_page' => CONST_RECORDS_PER_PAGE ?? 24,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Update leaderboard visibility preference
     *
     * @param int $user_id User ID
     * @param bool $visible Show on leaderboard
     * @param string|null $display_name Display name for leaderboard (null = use real name)
     * @return bool Success status
     */
    public static function updateLeaderboardSettings($user_id, $visible, $display_name = null)
    {
        $user_id = (int)$user_id;
        $visible = $visible ? 1 : 0;

        if (empty($user_id)) {
            return false;
        }

        try {
            $db_conn = PDOConn::getConnection();

            // Update members table
            $sql = "UPDATE `" . CONST_TBL_PREFIX . "members`
                    SET `leaderboard_visible` = :visible,
                        `display_name` = :display_name
                    WHERE `user_acnt_id` = :user_id";

            $stmt = $db_conn->prepare($sql);
            $result = $stmt->execute([
                ':visible' => $visible,
                ':display_name' => $display_name,
                ':user_id' => $user_id
            ]);

            // Also update leaderboard_stats table if it exists
            $sql_check = "SHOW TABLES LIKE '" . CONST_TBL_PREFIX . "leaderboard_stats'";
            $stmt_check = $db_conn->query($sql_check);

            if ($stmt_check->rowCount() > 0) {
                $sql_lb = "UPDATE `" . CONST_TBL_PREFIX . "leaderboard_stats`
                           SET `is_visible` = :visible,
                               `display_name` = :display_name
                           WHERE `user_id` = :user_id";

                $stmt_lb = $db_conn->prepare($sql_lb);
                $stmt_lb->execute([
                    ':visible' => $visible,
                    ':display_name' => $display_name,
                    ':user_id' => $user_id
                ]);
            }

            return $result;

        } catch (Exception $e) {
            logErrorInFile(time(), 'UserPreferences::updateLeaderboardSettings', $e->getMessage());
            return false;
        }
    }

    /**
     * Get leaderboard settings for a user
     *
     * @param int $user_id User ID
     * @return array Leaderboard settings (visible, display_name)
     */
    public static function getLeaderboardSettings($user_id)
    {
        $user_id = (int)$user_id;

        if (empty($user_id)) {
            return [
                'leaderboard_visible' => CONST_LEADERBOARD_OPT_IN_DEFAULT ?? false,
                'display_name' => null
            ];
        }

        try {
            $db_conn = PDOConn::getConnection();

            $sql = "SELECT `leaderboard_visible`, `display_name`
                    FROM `" . CONST_TBL_PREFIX . "members`
                    WHERE `user_acnt_id` = :user_id LIMIT 1";

            $stmt = $db_conn->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);

            $settings = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($settings) {
                return [
                    'leaderboard_visible' => (bool)$settings['leaderboard_visible'],
                    'display_name' => $settings['display_name']
                ];
            } else {
                return [
                    'leaderboard_visible' => CONST_LEADERBOARD_OPT_IN_DEFAULT ?? false,
                    'display_name' => null
                ];
            }

        } catch (Exception $e) {
            logErrorInFile(time(), 'UserPreferences::getLeaderboardSettings', $e->getMessage());
            return [
                'leaderboard_visible' => false,
                'display_name' => null
            ];
        }
    }

    /**
     * Delete user preferences (for account deletion)
     *
     * @param int $user_id User ID
     * @return bool Success status
     */
    public static function delete($user_id)
    {
        $user_id = (int)$user_id;

        if (empty($user_id)) {
            return false;
        }

        try {
            $db_conn = PDOConn::getConnection();

            $sql = "DELETE FROM `" . CONST_TBL_PREFIX . "user_preferences`
                    WHERE `user_id` = :user_id";

            $stmt = $db_conn->prepare($sql);
            $result = $stmt->execute([':user_id' => $user_id]);

            return $result;

        } catch (Exception $e) {
            logErrorInFile(time(), 'UserPreferences::delete', $e->getMessage());
            return false;
        }
    }
}
