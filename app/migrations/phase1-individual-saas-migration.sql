-- ============================================================================
-- Phase 1: Individual SaaS Conversion - Database Migration
-- ============================================================================
-- Description: Transforms the system from org-based to individual user SaaS
-- Date: 2025-11-08
-- ============================================================================

-- Backup recommendation: Take full database backup before running this script
-- Run this script as a single transaction

START TRANSACTION;

-- ============================================================================
-- 1. USERS TABLE MODIFICATIONS
-- ============================================================================
-- Add SaaS-specific fields to users table

ALTER TABLE `users`
ADD COLUMN `account_status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
    COMMENT 'Account status for SaaS management' AFTER `status`,
ADD COLUMN `account_type` ENUM('individual', 'admin') DEFAULT 'individual'
    COMMENT 'User type: individual user or system admin' AFTER `account_status`,
ADD COLUMN `created_by` INT NULL
    COMMENT 'Admin user ID who created this account' AFTER `account_type`,
ADD COLUMN `notes` TEXT NULL
    COMMENT 'Admin notes about this user account' AFTER `created_by`;

-- Update existing users to have proper account_status and account_type
UPDATE `users` SET `account_status` = 'active' WHERE `status` = 1;
UPDATE `users` SET `account_status` = 'inactive' WHERE `status` = 0;

-- Set existing admin users (based on roles table)
UPDATE `users` u
INNER JOIN `user_roles` ur ON u.id = ur.user_id
INNER JOIN `roles` r ON ur.role_id = r.id
SET u.account_type = 'admin'
WHERE r.role IN ('ADMIN', 'admin');

-- ============================================================================
-- 2. MEMBERS TABLE MODIFICATIONS
-- ============================================================================
-- Add privacy and display fields for individual users

ALTER TABLE `members`
MODIFY COLUMN `membership_no` VARCHAR(30) NULL
    COMMENT 'Optional membership number (legacy)',
ADD COLUMN `display_name` VARCHAR(100) NULL
    COMMENT 'Public display name for leaderboard (pseudonym option)' AFTER `lname`,
ADD COLUMN `leaderboard_visible` BOOLEAN DEFAULT 0
    COMMENT 'User opt-in to appear on public leaderboard' AFTER `display_name`;

-- Create index for faster leaderboard queries
CREATE INDEX idx_leaderboard_visible ON `members`(`leaderboard_visible`);

-- ============================================================================
-- 3. LEADERBOARD_STATS TABLE MODIFICATIONS
-- ============================================================================
-- Add privacy controls to leaderboard

-- Check if leaderboard_stats table exists, if not create it
CREATE TABLE IF NOT EXISTS `leaderboard_stats` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `cycle_id` INT NOT NULL,
    `total_points` INT DEFAULT 0,
    `completion_rate` DECIMAL(5,2) DEFAULT 0.00,
    `current_streak` INT DEFAULT 0,
    `rank_position` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_cycle` (`user_id`, `cycle_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`cycle_id`) REFERENCES `cycles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add display_name and visibility columns if they don't exist
SET @dbname = DATABASE();
SET @tablename = 'leaderboard_stats';
SET @columnname_display = 'display_name';
SET @columnname_visible = 'is_visible';

SET @check_display = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = @columnname_display
);

SET @check_visible = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = @columnname_visible
);

SET @sql_display = IF(
    @check_display = 0,
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `display_name` VARCHAR(100) NULL COMMENT "Pseudonym for public display" AFTER `user_id`'),
    'SELECT "Column display_name already exists" AS msg'
);

SET @sql_visible = IF(
    @check_visible = 0,
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `is_visible` BOOLEAN DEFAULT 0 COMMENT "User opt-in to show on leaderboard" AFTER `display_name`'),
    'SELECT "Column is_visible already exists" AS msg'
);

PREPARE stmt_display FROM @sql_display;
EXECUTE stmt_display;
DEALLOCATE PREPARE stmt_display;

PREPARE stmt_visible FROM @sql_visible;
EXECUTE stmt_visible;
DEALLOCATE PREPARE stmt_visible;

-- Create index for leaderboard queries
CREATE INDEX IF NOT EXISTS idx_visible_rank ON `leaderboard_stats`(`is_visible`, `cycle_id`, `total_points`);

-- ============================================================================
-- 4. CREATE USER_PREFERENCES TABLE
-- ============================================================================
-- Store individual user preferences and settings

CREATE TABLE IF NOT EXISTS `user_preferences` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `date_format` VARCHAR(20) DEFAULT 'd-m-Y' COMMENT 'Preferred date format',
    `time_zone` VARCHAR(50) DEFAULT 'Asia/Kolkata' COMMENT 'User timezone',
    `email_weekly_summary` BOOLEAN DEFAULT 1 COMMENT 'Send weekly progress email',
    `email_achievements` BOOLEAN DEFAULT 1 COMMENT 'Send achievement notifications',
    `email_reminders` BOOLEAN DEFAULT 1 COMMENT 'Send daily task reminders',
    `theme` VARCHAR(20) DEFAULT 'light' COMMENT 'UI theme preference',
    `items_per_page` INT DEFAULT 24 COMMENT 'Pagination preference',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_pref` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create default preferences for existing users
INSERT INTO `user_preferences` (`user_id`)
SELECT `id` FROM `users`
WHERE NOT EXISTS (
    SELECT 1 FROM `user_preferences` WHERE `user_preferences`.`user_id` = `users`.`id`
);

-- ============================================================================
-- 5. CREATE AUDIT_LOGS TABLE (Optional but recommended)
-- ============================================================================
-- Track important user actions for security and debugging

CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL COMMENT 'Action performed (e.g., login, goal_created)',
    `ip_address` VARCHAR(45) NULL COMMENT 'User IP address',
    `user_agent` TEXT NULL COMMENT 'Browser user agent',
    `metadata` JSON NULL COMMENT 'Additional context as JSON',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_user_action` (`user_id`, `action`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 6. UPDATE CYCLES TABLE (Ensure user isolation)
-- ============================================================================
-- Add index to improve performance on user-specific queries

CREATE INDEX IF NOT EXISTS idx_user_cycles ON `cycles`(`created_by`, `start_date`);

-- ============================================================================
-- 7. UPDATE GOALS TABLE (Ensure user isolation)
-- ============================================================================
-- Add index for user-specific goal queries

CREATE INDEX IF NOT EXISTS idx_user_goals ON `goals`(`user_id`, `cycle_id`);

-- ============================================================================
-- 8. UPDATE TASKS TABLE (Ensure user isolation via goals)
-- ============================================================================
-- Add index for performance

CREATE INDEX IF NOT EXISTS idx_goal_tasks ON `tasks`(`goal_id`, `week_number`);

-- ============================================================================
-- 9. SYNC LEADERBOARD VISIBILITY FROM MEMBERS TABLE
-- ============================================================================
-- Sync visibility settings between members and leaderboard_stats

UPDATE `leaderboard_stats` ls
INNER JOIN `members` m ON ls.user_id = m.user_acnt_id
SET ls.is_visible = m.leaderboard_visible,
    ls.display_name = m.display_name;

-- ============================================================================
-- MIGRATION COMPLETE
-- ============================================================================

COMMIT;

-- ============================================================================
-- POST-MIGRATION VERIFICATION QUERIES
-- ============================================================================
-- Run these queries to verify the migration was successful:

-- 1. Check users table structure
-- SELECT * FROM users LIMIT 1;

-- 2. Check members table structure
-- SELECT * FROM members LIMIT 1;

-- 3. Check user_preferences created
-- SELECT COUNT(*) as total_prefs FROM user_preferences;

-- 4. Check leaderboard_stats structure
-- SELECT * FROM leaderboard_stats LIMIT 1;

-- 5. Verify all existing users have preferences
-- SELECT u.id, u.email, up.id as pref_id
-- FROM users u
-- LEFT JOIN user_preferences up ON u.id = up.user_id
-- WHERE up.id IS NULL;
-- (Should return 0 rows)

-- ============================================================================
-- ROLLBACK INSTRUCTIONS (if needed)
-- ============================================================================
-- If you need to rollback this migration:
/*
START TRANSACTION;

ALTER TABLE `users`
    DROP COLUMN `account_status`,
    DROP COLUMN `account_type`,
    DROP COLUMN `created_by`,
    DROP COLUMN `notes`;

ALTER TABLE `members`
    DROP COLUMN `display_name`,
    DROP COLUMN `leaderboard_visible`,
    DROP INDEX `idx_leaderboard_visible`;

-- Note: Only drop these columns if you added them
-- ALTER TABLE `leaderboard_stats`
--     DROP COLUMN `display_name`,
--     DROP COLUMN `is_visible`,
--     DROP INDEX `idx_visible_rank`;

DROP TABLE IF EXISTS `user_preferences`;
DROP TABLE IF EXISTS `audit_logs`;

COMMIT;
*/
