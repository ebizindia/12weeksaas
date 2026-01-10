-- Database schema for 12-Week Year Leaderboard and Gamification Module
-- This file creates all tables required for the leaderboard functionality

-- Table: user_stats
-- Stores user statistics per cycle
CREATE TABLE IF NOT EXISTS `user_stats` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `cycle_id` INT(11) NOT NULL,
  `total_tasks_completed` INT(11) DEFAULT 0,
  `total_goals_created` INT(11) DEFAULT 0,
  `total_tasks_planned` INT(11) DEFAULT 0,
  `weeks_completed` INT(11) DEFAULT 0,
  `perfect_weeks` INT(11) DEFAULT 0,
  `total_points` INT(11) DEFAULT 0,
  `current_streak` INT(11) DEFAULT 0,
  `longest_streak` INT(11) DEFAULT 0,
  `last_activity_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_cycle_unique` (`user_id`, `cycle_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_cycle_id` (`cycle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: daily_activity
-- Tracks daily user activity for streak calculation
CREATE TABLE IF NOT EXISTS `daily_activity` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `cycle_id` INT(11) NOT NULL,
  `activity_date` DATE NOT NULL,
  `tasks_completed` INT(11) DEFAULT 0,
  `goals_created` INT(11) DEFAULT 0,
  `tasks_planned` INT(11) DEFAULT 0,
  `was_active` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_cycle_date_unique` (`user_id`, `cycle_id`, `activity_date`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_cycle_id` (`cycle_id`),
  KEY `idx_activity_date` (`activity_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: leaderboard_stats
-- Stores leaderboard rankings and stats
CREATE TABLE IF NOT EXISTS `leaderboard_stats` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `cycle_id` INT(11) NOT NULL,
  `total_points` INT(11) DEFAULT 0,
  `completion_rate` DECIMAL(5,2) DEFAULT 0.00,
  `current_streak` INT(11) DEFAULT 0,
  `achievements_count` INT(11) DEFAULT 0,
  `rank_position` INT(11) DEFAULT NULL,
  `is_visible` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_cycle_unique` (`user_id`, `cycle_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_cycle_id` (`cycle_id`),
  KEY `idx_rank_position` (`rank_position`),
  KEY `idx_total_points` (`total_points`),
  KEY `idx_completion_rate` (`completion_rate`),
  KEY `idx_current_streak` (`current_streak`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: achievements
-- Stores achievement definitions
CREATE TABLE IF NOT EXISTS `achievements` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `icon` VARCHAR(100) DEFAULT NULL,
  `points` INT(11) DEFAULT 0,
  `requirement_type` VARCHAR(50) DEFAULT NULL,
  `requirement_value` INT(11) DEFAULT NULL,
  `badge_color` VARCHAR(50) DEFAULT 'primary',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_requirement_type` (`requirement_type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: user_achievements
-- Tracks user-earned achievements
CREATE TABLE IF NOT EXISTS `user_achievements` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `achievement_id` INT(11) NOT NULL,
  `cycle_id` INT(11) DEFAULT NULL,
  `earned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_achievement_cycle_unique` (`user_id`, `achievement_id`, `cycle_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_achievement_id` (`achievement_id`),
  KEY `idx_cycle_id` (`cycle_id`),
  KEY `idx_earned_at` (`earned_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: weekly_scores
-- Stores weekly performance scores
CREATE TABLE IF NOT EXISTS `weekly_scores` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `cycle_id` INT(11) NOT NULL,
  `week_number` INT(11) NOT NULL,
  `total_checkboxes` INT(11) DEFAULT 0,
  `completed_checkboxes` INT(11) DEFAULT 0,
  `score_percentage` DECIMAL(5,2) DEFAULT 0.00,
  `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_cycle_week_unique` (`user_id`, `cycle_id`, `week_number`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_cycle_id` (`cycle_id`),
  KEY `idx_week_number` (`week_number`),
  KEY `idx_score_percentage` (`score_percentage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default achievements
INSERT INTO `achievements` (`name`, `description`, `icon`, `points`, `requirement_type`, `requirement_value`, `badge_color`) VALUES
('First Steps', 'Complete your first task', 'star', 10, 'task_completed', 1, 'primary'),
('Goal Setter', 'Create your first goal', 'target', 15, 'goal_created', 1, 'success'),
('Task Master', 'Complete 10 tasks', 'trophy', 50, 'task_completed', 10, 'warning'),
('Streak Starter', 'Maintain a 3-day streak', 'fire', 30, 'streak', 3, 'danger'),
('Week Warrior', 'Complete a perfect week (100%)', 'calendar-check', 100, 'perfect_week', 1, 'info'),
('Consistency King', 'Maintain a 7-day streak', 'crown', 100, 'streak', 7, 'warning'),
('Goal Crusher', 'Complete 5 goals', 'rocket', 75, 'goal_created', 5, 'success'),
('Marathon Runner', 'Maintain a 30-day streak', 'running', 500, 'streak', 30, 'danger'),
('Perfectionist', 'Complete 3 perfect weeks', 'gem', 300, 'perfect_week', 3, 'info')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`);

-- Stored Procedure: CheckUserAchievements
-- Checks and awards new achievements for a user
DROP PROCEDURE IF EXISTS `CheckUserAchievements`;

DELIMITER //

CREATE PROCEDURE `CheckUserAchievements`(
  IN p_user_id INT,
  IN p_cycle_id INT
)
BEGIN
  -- Check task completion achievements
  INSERT IGNORE INTO user_achievements (user_id, achievement_id, cycle_id)
  SELECT p_user_id, a.id, p_cycle_id
  FROM achievements a
  JOIN user_stats us ON us.user_id = p_user_id AND us.cycle_id = p_cycle_id
  WHERE a.requirement_type = 'task_completed'
    AND us.total_tasks_completed >= a.requirement_value
    AND NOT EXISTS (
      SELECT 1 FROM user_achievements ua
      WHERE ua.user_id = p_user_id
        AND ua.achievement_id = a.id
        AND (ua.cycle_id = p_cycle_id OR ua.cycle_id IS NULL)
    );

  -- Check goal creation achievements
  INSERT IGNORE INTO user_achievements (user_id, achievement_id, cycle_id)
  SELECT p_user_id, a.id, p_cycle_id
  FROM achievements a
  JOIN user_stats us ON us.user_id = p_user_id AND us.cycle_id = p_cycle_id
  WHERE a.requirement_type = 'goal_created'
    AND us.total_goals_created >= a.requirement_value
    AND NOT EXISTS (
      SELECT 1 FROM user_achievements ua
      WHERE ua.user_id = p_user_id
        AND ua.achievement_id = a.id
        AND (ua.cycle_id = p_cycle_id OR ua.cycle_id IS NULL)
    );

  -- Check streak achievements
  INSERT IGNORE INTO user_achievements (user_id, achievement_id, cycle_id)
  SELECT p_user_id, a.id, p_cycle_id
  FROM achievements a
  JOIN user_stats us ON us.user_id = p_user_id AND us.cycle_id = p_cycle_id
  WHERE a.requirement_type = 'streak'
    AND us.current_streak >= a.requirement_value
    AND NOT EXISTS (
      SELECT 1 FROM user_achievements ua
      WHERE ua.user_id = p_user_id
        AND ua.achievement_id = a.id
        AND (ua.cycle_id = p_cycle_id OR ua.cycle_id IS NULL)
    );

  -- Check perfect week achievements
  INSERT IGNORE INTO user_achievements (user_id, achievement_id, cycle_id)
  SELECT p_user_id, a.id, p_cycle_id
  FROM achievements a
  JOIN user_stats us ON us.user_id = p_user_id AND us.cycle_id = p_cycle_id
  WHERE a.requirement_type = 'perfect_week'
    AND us.perfect_weeks >= a.requirement_value
    AND NOT EXISTS (
      SELECT 1 FROM user_achievements ua
      WHERE ua.user_id = p_user_id
        AND ua.achievement_id = a.id
        AND (ua.cycle_id = p_cycle_id OR ua.cycle_id IS NULL)
    );

  -- Update user's total points with achievement points
  UPDATE user_stats us
  SET us.total_points = (
    SELECT COALESCE(SUM(a.points), 0)
    FROM user_achievements ua
    JOIN achievements a ON ua.achievement_id = a.id
    WHERE ua.user_id = p_user_id
      AND ua.cycle_id = p_cycle_id
  )
  WHERE us.user_id = p_user_id AND us.cycle_id = p_cycle_id;

END //

DELIMITER ;
