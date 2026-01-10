# Leaderboard HTTP 500 Error - Fix Instructions

## Problem
The leaderboard module was returning an HTTP 500 error because the required database tables and stored procedure were missing.

## Solution
The leaderboard feature requires several database tables that were not created. Run the database initialization script to create them.

## Quick Fix

### Option 1: Run the PHP Initialization Script (Recommended)
```bash
php database/init-leaderboard-db.php
```

This script will:
- Create all required database tables
- Set up the CheckUserAchievements stored procedure
- Insert default achievements
- Provide detailed feedback

### Option 2: Import SQL Directly
```bash
mysql -u [username] -p [database_name] < database/leaderboard-schema.sql
```

Replace `[username]` and `[database_name]` with your actual database credentials.

## Required Tables

The following tables will be created:

1. **user_stats** - Stores user statistics per cycle
2. **daily_activity** - Tracks daily activity for streaks
3. **leaderboard_stats** - Stores rankings and performance
4. **achievements** - Achievement definitions
5. **user_achievements** - User's earned achievements
6. **weekly_scores** - Weekly performance scores

Plus one stored procedure:
- **CheckUserAchievements** - Checks and awards achievements

## Verification

After running the initialization:

1. Access the leaderboard page: `/12-week-leaderboard.php`
2. You should see the leaderboard interface (may be empty if no users have activity yet)
3. No more 500 errors

## What Was Wrong

The leaderboard code was trying to:
- Query tables that didn't exist (`leaderboard_stats`, `user_stats`, etc.)
- Call a stored procedure that wasn't created (`CheckUserAchievements`)

This caused PHP/MySQL errors resulting in HTTP 500 responses.

## Additional Notes

- The schema uses `CREATE TABLE IF NOT EXISTS` so it's safe to run multiple times
- Default achievements are automatically inserted
- If you have existing data, it won't be affected
- See `database/README.md` for more detailed documentation
