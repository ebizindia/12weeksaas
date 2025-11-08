-- ===================================================================
-- Waitlist Table Migration
-- ===================================================================
-- Description: Creates waitlist table for landing page signups
-- Date: 2025-11-08
-- ===================================================================

CREATE TABLE IF NOT EXISTS `waitlist` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `company` VARCHAR(255) NULL,
    `title` VARCHAR(255) NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `status` ENUM('pending', 'invited', 'converted', 'declined') DEFAULT 'pending',
    `invited_at` DATETIME NULL,
    `converted_at` DATETIME NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `unique_email` (`email`),
    KEY `idx_status` (`status`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- Migration Complete
-- ===================================================================
