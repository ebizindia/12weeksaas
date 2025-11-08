# Phase 1 Deployment Checklist

## Pre-Deployment Verification

### 1. File Structure Verification

**New Files Created:**
```bash
✅ migrations/phase1-individual-saas-migration.sql
✅ migrations/run-phase1-migration.php
✅ cls/UserPreferences.php
✅ account-settings.php
✅ templates/account-settings.tpl
✅ admin-add-user.php
✅ admin-reset-password.php
✅ docs/PHASE1-GUIDE.md
✅ docs/TESTING-GUIDE.md
✅ docs/TEST-EXECUTION-REPORT.md
```

**Modified Files:**
```bash
✅ config.php (Phase 1 SaaS settings added)
✅ 12-week-dashboard.php (self-service messaging)
✅ 12-week-progress.php ($is_admin = false)
✅ 12-week-leaderboard.php (privacy-first queries)
✅ templates/sidebar.tpl (Account Settings link)
```

**Disabled Files:**
```bash
✅ 12-week-admin-dashboard.php.disabled
✅ users.php.disabled
✅ mem-regs.php.disabled
```

---

## Deployment Steps

### Step 1: Backup Current System

**Database Backup:**
```bash
mysqldump -u [username] -p [database_name] > backup_before_phase1_$(date +%Y%m%d_%H%M%S).sql
```

**File Backup:**
```bash
tar -czf 12weeksaas_backup_$(date +%Y%m%d_%H%M%S).tar.gz /path/to/12weeksaas/
```

**Verify Backup:**
```bash
# Check file size
ls -lh backup_before_phase1_*.sql
# Verify tar contents
tar -tzf 12weeksaas_backup_*.tar.gz | head -20
```

---

### Step 2: Deploy Code Changes

**Option A: Git Pull (Recommended)**
```bash
cd /path/to/12weeksaas/
git checkout claude/convert-to-saas-individual-011CUuwb1pzKWQpYxBBX1g1y
git pull origin claude/convert-to-saas-individual-011CUuwb1pzKWQpYxBBX1g1y
```

**Option B: Manual File Copy**
```bash
# Copy new/modified files to production
# Ensure file permissions are correct (644 for PHP files, 755 for directories)
```

**Verify Deployment:**
```bash
# Check that config.php has Phase 1 settings
grep "CONST_SAAS_MODE" config.php

# Verify Account Settings file exists
ls -l account-settings.php

# Check sidebar has been updated
grep "Account Settings" templates/sidebar.tpl
```

---

### Step 3: Database Migration

**Run Migration Script:**
```bash
cd /path/to/12weeksaas/
php migrations/run-phase1-migration.php --force
```

**Expected Output:**
```
Starting Phase 1 migration...
✓ Connected to database
✓ Modifying users table...
✓ Modifying members table...
✓ Creating user_preferences table...
✓ Creating audit_logs table...
✓ Modifying leaderboard_stats table...
✓ Creating default preferences for existing users...

Migration completed successfully!

Verification:
✓ user_preferences table exists
✓ users.account_status column exists
✓ members.display_name column exists
✓ All existing users have preferences

Phase 1 migration complete!
```

**Manual Verification:**
```sql
-- Check new tables exist
SHOW TABLES LIKE 'user_preferences';
SHOW TABLES LIKE 'audit_logs';

-- Check new columns in users table
DESCRIBE users;
-- Should see: account_status, account_type, created_by, notes

-- Check new columns in members table
DESCRIBE members;
-- Should see: display_name, leaderboard_visible

-- Verify default preferences were created
SELECT COUNT(*) FROM user_preferences;
-- Should equal number of existing users
```

---

### Step 4: Configuration Verification

**Check config.php Settings:**
```php
// Verify these constants are set correctly
CONST_SAAS_MODE = true
CONST_REQUIRE_SIGNUP = false
CONST_MIN_PASSWORD_LENGTH = 8
CONST_SHOW_ADMIN_DASHBOARD = false
CONST_ENABLE_MEMBER_GROUPS = false
CONST_ENABLE_ADMIN_OVERSIGHT = false
CONST_LEADERBOARD_OPT_IN_DEFAULT = false
```

**Test Configuration:**
```bash
php -r "require 'config.php'; echo CONST_SAAS_MODE ? 'SaaS Mode: ON' : 'SaaS Mode: OFF';"
# Should output: SaaS Mode: ON
```

---

### Step 5: Create First Admin User (If Needed)

If you need to create an initial admin user for testing:

**Access Admin Tool:**
```
http://your-domain.com/admin-add-user.php
```

**Create Test User:**
```
Email: test@example.com
Password: (auto-generated, copy it!)
First Name: Test
Last Name: User
```

**Save the credentials shown after creation!**

---

### Step 6: Functional Testing

**Test 1: Login with New User**
- Go to login page
- Use credentials from Step 5
- Verify successful login
- Check dashboard loads

**Test 2: Access Account Settings**
- Click "Account Settings" in sidebar (under Utilities)
- Verify all 4 tabs load: Profile, Password, Privacy, Notifications
- Check that user data is pre-populated

**Test 3: Update Profile**
- Change First Name or Last Name
- Click "Save Changes"
- Verify success message
- Logout and login again
- Verify changes persisted

**Test 4: Change Password**
- Go to Password tab
- Enter current password
- Enter new password (min 8 characters)
- Click "Change Password"
- Logout
- Login with new password
- Verify success

**Test 5: Privacy Settings**
- Go to Privacy tab
- Enable "Show me on the public leaderboard"
- Set Display Name to "TestUser123"
- Save changes
- Check database:
```sql
SELECT leaderboard_visible, display_name FROM members WHERE email = 'test@example.com';
-- Should show: 1, 'TestUser123'
```

**Test 6: Data Isolation**
- Create a second test user via admin-add-user.php
- Login as User 1, create a goal
- Login as User 2
- Verify User 2 cannot see User 1's goal
- Check dashboard, goals, tasks, progress pages

**Test 7: Leaderboard Privacy**
- Create 2 users
- User A: Set leaderboard_visible = 0
- User B: Set leaderboard_visible = 1 with display_name
- View leaderboard
- Verify User A is NOT shown
- Verify User B shows with display_name (not real name)

---

### Step 7: Security Testing

**SQL Injection Tests:**
```
Test Input: ' OR '1'='1
Location: Login form, Account Settings forms
Expected: Input sanitized, no SQL error, no unauthorized access
```

**CSRF Token Verification:**
```
1. Load Account Settings page
2. View page source
3. Look for: <input type="hidden" name="csrf_token" value="...">
4. Try submitting form without token
5. Expected: Form rejected
```

**Session Security:**
```
1. Login as User A
2. Copy session cookie
3. Open incognito window
4. Try to access account-settings.php directly
5. Expected: Redirected to login
```

**Password Security:**
```sql
-- Verify passwords are hashed
SELECT id, email, password FROM users LIMIT 1;
-- Password should start with $2y$ (bcrypt hash)
```

---

### Step 8: Performance Testing

**Page Load Times:**
```
Dashboard: < 2 seconds
Account Settings: < 1.5 seconds
Admin Add User: < 1 second
Leaderboard: < 2.5 seconds
```

**Database Query Count:**
```
Dashboard: < 15 queries
Account Settings: < 10 queries
```

**Check Error Logs:**
```bash
tail -50 includes/system-error-log.txt
# Should have no critical errors
```

---

## Post-Deployment Checklist

### Immediate Verification (Day 1)

- [ ] Database migration completed successfully
- [ ] All new tables created (user_preferences, audit_logs)
- [ ] All modified columns exist in users/members tables
- [ ] Config.php has correct Phase 1 settings
- [ ] Account Settings page is accessible
- [ ] Admin tools are accessible (admin-add-user.php, admin-reset-password.php)
- [ ] Navigation includes "Account Settings" link
- [ ] Login/logout works correctly
- [ ] Password change works
- [ ] Profile update works
- [ ] Privacy settings save correctly

### Security Verification

- [ ] All passwords are bcrypt hashed in database
- [ ] CSRF tokens present on all forms
- [ ] SQL injection attempts blocked
- [ ] Session security working (HttpOnly, Secure flags)
- [ ] User data isolation enforced
- [ ] Admin oversight removed ($is_admin = false everywhere)
- [ ] Leaderboard respects privacy settings

### Functional Verification

- [ ] Users can create goals (isolated to their account)
- [ ] Users can create tasks (isolated to their account)
- [ ] Dashboard shows only user's own data
- [ ] Progress page shows only user's own data
- [ ] Leaderboard shows only opted-in users
- [ ] Display names (pseudonyms) appear correctly
- [ ] Email preferences save and load correctly

### Data Integrity

- [ ] Existing user data preserved after migration
- [ ] All existing users have default preferences created
- [ ] No orphaned records in database
- [ ] Foreign key constraints intact
- [ ] Backup verified and accessible

---

## Rollback Plan

If critical issues are discovered:

### Step 1: Stop Application
```bash
# Rename inc.php to prevent access
mv inc.php inc.php.disabled
# Or use web server config to return 503
```

### Step 2: Restore Database
```sql
DROP TABLE IF EXISTS user_preferences;
DROP TABLE IF EXISTS audit_logs;

-- Restore from backup
mysql -u [username] -p [database_name] < backup_before_phase1_[timestamp].sql
```

### Step 3: Restore Code
```bash
# Restore from git
git checkout main  # or previous stable branch

# Or restore from tar backup
tar -xzf 12weeksaas_backup_[timestamp].tar.gz -C /restore/location/
```

### Step 4: Verify Rollback
```bash
# Check database
mysql -u [username] -p -e "USE [database_name]; SHOW TABLES;"

# Check config
grep "CONST_SAAS_MODE" config.php
# Should show false or be commented out

# Test login
# Access dashboard
```

---

## Monitoring Plan

### Daily Checks (First Week)

**Check Error Logs:**
```bash
tail -100 includes/system-error-log.txt | grep -i "error\|fatal\|warning"
```

**Check User Activity:**
```sql
SELECT DATE(created_at) as date, COUNT(*) as actions
FROM audit_logs
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at);
```

**Check Failed Logins:**
```sql
SELECT COUNT(*) as failed_attempts
FROM audit_logs
WHERE action = 'login_failed'
  AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);
```

### Weekly Checks

**User Growth:**
```sql
SELECT
    COUNT(*) as total_users,
    SUM(account_status = 'active') as active_users,
    SUM(account_status = 'inactive') as inactive_users
FROM users
WHERE account_type = 'individual';
```

**Feature Adoption:**
```sql
-- Leaderboard opt-in rate
SELECT
    COUNT(*) as total_users,
    SUM(leaderboard_visible) as opted_in,
    ROUND(SUM(leaderboard_visible) / COUNT(*) * 100, 2) as opt_in_percentage
FROM members;

-- Display name usage
SELECT COUNT(*) as users_with_pseudonyms
FROM members
WHERE display_name IS NOT NULL AND display_name != '';
```

**Data Quality:**
```sql
-- Check for users without preferences
SELECT u.id, u.email
FROM users u
LEFT JOIN user_preferences up ON u.id = up.user_id
WHERE up.id IS NULL AND u.account_type = 'individual';
-- Should return 0 rows
```

---

## Common Issues and Solutions

### Issue 1: Migration Fails

**Error:** "Column already exists"
```sql
-- Check if migration was partially run
SHOW COLUMNS FROM users LIKE 'account_status';

-- If exists, migration may be safe to re-run
-- Or manually skip conflicting statements
```

### Issue 2: Account Settings Page Blank

**Troubleshooting:**
```bash
# Check PHP error log
tail -50 /var/log/php-error.log

# Check file permissions
ls -l account-settings.php
# Should be: -rw-r--r-- (644)

# Check template exists
ls -l templates/account-settings.tpl
```

### Issue 3: Users Can't Login

**Troubleshooting:**
```sql
-- Check user status
SELECT id, email, account_status FROM users WHERE email = 'user@example.com';
-- account_status should be 'active'

-- Check password hash
SELECT LENGTH(password) FROM users WHERE email = 'user@example.com';
-- Should be 60 (bcrypt hash length)
```

### Issue 4: Leaderboard Shows All Users

**Fix:**
```php
// Check 12-week-leaderboard.php line ~50
// Should have: AND ls.is_visible = 1

// Update database
UPDATE leaderboard_stats
SET is_visible = 0
WHERE user_id IN (
    SELECT user_acnt_id FROM members WHERE leaderboard_visible = 0
);
```

---

## Success Criteria

Phase 1 is successfully deployed when:

✅ **Database**
- All migrations run without errors
- New tables created (user_preferences, audit_logs)
- All columns added to existing tables
- Data integrity maintained

✅ **Functionality**
- Users can login with individual accounts
- Account Settings page accessible and functional
- Profile updates save correctly
- Password changes work
- Privacy settings apply to leaderboard
- Data isolation enforced (users only see own data)

✅ **Security**
- Passwords bcrypt hashed
- CSRF tokens on all forms
- SQL injection prevention verified
- Session security working
- No admin oversight of user data

✅ **Admin Tools**
- Admin can create new users
- Admin can reset passwords
- User list displays correctly
- Password generation works

✅ **User Experience**
- Navigation includes Account Settings
- All pages load in < 3 seconds
- No JavaScript errors in console
- Forms validate input correctly
- Success/error messages display appropriately

---

## Next Steps After Phase 1

Once Phase 1 is stable and tested:

1. **Gather User Feedback**
   - Survey initial users
   - Track feature usage
   - Identify pain points

2. **Plan Phase 2**
   - Self-service signup
   - Email verification
   - Password reset flow
   - Onboarding wizard

3. **Performance Optimization**
   - Database indexing review
   - Query optimization
   - Caching strategy

4. **Documentation**
   - User manual
   - Video tutorials
   - FAQ based on user questions

---

## Support Contacts

**Documentation:**
- Implementation Guide: `docs/PHASE1-GUIDE.md`
- Testing Guide: `docs/TESTING-GUIDE.md`
- This Checklist: `docs/DEPLOYMENT-CHECKLIST.md`

**Key Files:**
- Migration Script: `migrations/phase1-individual-saas-migration.sql`
- Config: `config.php`
- Admin Tools: `admin-add-user.php`, `admin-reset-password.php`

**Git Branch:**
- Branch: `claude/convert-to-saas-individual-011CUuwb1pzKWQpYxBBX1g1y`
- Commits: Phase 1 Database, Account Settings, Navigation & Docs

---

*Last Updated: 2025-11-08*
*Phase: 1 - Individual SaaS Mode*
*Status: Ready for Deployment*
