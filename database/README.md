# Leaderboard Database Setup

This directory contains the database schema and initialization scripts for the 12-Week Year Leaderboard and Gamification module.

## Quick Start

To set up the leaderboard database tables, run the initialization script from your terminal:

```bash
php database/init-leaderboard-db.php
```

This script will:
- Create all required database tables
- Set up the stored procedure for achievement checking
- Insert default achievements
- Provide detailed feedback on the initialization process

## Database Tables

The leaderboard module requires the following tables:

### 1. `user_stats`
Stores user statistics for each cycle including:
- Task and goal counts
- Completed weeks
- Perfect weeks
- Current and longest streaks
- Total points

### 2. `daily_activity`
Tracks daily user activity for streak calculation:
- Tasks completed per day
- Goals created per day
- Tasks planned per day
- Activity flags for streak tracking

### 3. `leaderboard_stats`
Stores leaderboard rankings and performance metrics:
- Total points
- Completion rate
- Current streak
- Achievement count
- Rank position
- Visibility flag

### 4. `achievements`
Defines available achievements:
- Achievement names and descriptions
- Point values
- Requirement types and values
- Badge colors and icons

### 5. `user_achievements`
Tracks which achievements users have earned:
- User-achievement mappings
- Cycle-specific achievements
- Timestamp when earned

### 6. `weekly_scores`
Stores weekly performance scores:
- Total and completed checkboxes
- Score percentages
- Week numbers per cycle

## Manual Setup

If you prefer to run the SQL directly, you can use:

```bash
mysql -u your_username -p your_database < database/leaderboard-schema.sql
```

## Stored Procedures

### `CheckUserAchievements(user_id, cycle_id)`

This stored procedure checks if a user has earned any new achievements based on their current statistics. It:
- Checks task completion achievements
- Checks goal creation achievements
- Checks streak achievements
- Checks perfect week achievements
- Updates user's total points based on earned achievements

## Default Achievements

The following achievements are created by default:

1. **First Steps** (10 points) - Complete your first task
2. **Goal Setter** (15 points) - Create your first goal
3. **Task Master** (50 points) - Complete 10 tasks
4. **Streak Starter** (30 points) - Maintain a 3-day streak
5. **Week Warrior** (100 points) - Complete a perfect week (100%)
6. **Consistency King** (100 points) - Maintain a 7-day streak
7. **Goal Crusher** (75 points) - Complete 5 goals
8. **Marathon Runner** (500 points) - Maintain a 30-day streak
9. **Perfectionist** (300 points) - Complete 3 perfect weeks

## Troubleshooting

### Permission Denied
Make sure the PHP script has read permissions:
```bash
chmod +r database/leaderboard-schema.sql
chmod +x database/init-leaderboard-db.php
```

### Connection Errors
Verify that your database connection is properly configured in `inc.php` and that the database user has CREATE and INSERT privileges.

### Existing Tables
The schema uses `CREATE TABLE IF NOT EXISTS` so running it multiple times is safe. Existing tables won't be modified, but new tables will be created.

## Support

If you encounter issues during setup, check the PHP error logs or enable error reporting in the initialization script for more detailed diagnostics.
