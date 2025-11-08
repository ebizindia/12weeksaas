# Phase 1 Testing Guide & Checklist

## Prerequisites

Before starting testing, ensure:
- ✅ Database server is running
- ✅ Database credentials in `config.php` are correct
- ✅ Web server (Apache/Nginx) is configured
- ✅ PHP 7.4+ is installed
- ✅ All Phase 1 files are deployed

---

## Step 1: Database Migration

### Run Migration Script

```bash
# Take database backup first
mysqldump -u 12week_12w -p 2week_12w > backup_before_phase1_$(date +%Y%m%d_%H%M%S).sql

# Run migration
php migrations/run-phase1-migration.php --force
```

### Expected Output

```
============================================================================
Phase 1: Individual SaaS Conversion - Database Migration
============================================================================

⚠ Running with --force flag (skipping confirmation)

✓ Database connection established

Executing migration statements...

  ✓ Modified table: users
  ✓ Modified table: members
  ✓ Created table: user_preferences
  ✓ Created table: audit_logs
  ✓ Modified table: leaderboard_stats
  ✓ Created index: idx_leaderboard_visible
  ✓ Created index: idx_visible_rank
  ✓ Inserted default data
  ✓ Transaction committed

============================================================================
Migration Summary:
============================================================================
Successful operations: 15
Errors encountered: 0

✓ Migration completed successfully!

============================================================================
Verification:
============================================================================
✓ users.account_status column created
✓ members.display_name column created
✓ user_preferences table created
  └─ 0 user preference records created (none yet)
✓ audit_logs table created
✓ leaderboard_stats.is_visible column created

============================================================================
Next Steps:
============================================================================
1. Review the migration output above
2. Check your database to verify changes
3. Update config.php with Phase 1 settings (already done)
4. Proceed with code updates
```

### Verification Queries

```sql
-- Check new tables exist
SHOW TABLES LIKE 'user_preferences';
SHOW TABLES LIKE 'audit_logs';

-- Check new columns in users table
DESCRIBE users;
-- Should show: account_status, account_type, created_by, notes

-- Check new columns in members table
DESCRIBE members;
-- Should show: display_name, leaderboard_visible

-- Check existing data is intact
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM members;
SELECT COUNT(*) FROM goals;
```

---

## Step 2: Create Test Users

### Test User 1: Alice (Individual User)

**Access:** `http://your-domain.com/admin-add-user.php`

**Input:**
- Email: `alice@test.com`
- Password: (auto-generated, e.g., `Abc123XYZ!@#`)
- First Name: `Alice`
- Last Name: `Anderson`

**Expected Result:**
```
✓ User created successfully!

Important: Save these credentials
Name: Alice Anderson
Email: alice@test.com
Password: Abc123XYZ!@#
⚠ This password will not be shown again.
```

**Save credentials:**
```
User 1:
Email: alice@test.com
Password: Abc123XYZ!@#
```

### Test User 2: Bob (Individual User)

**Input:**
- Email: `bob@test.com`
- Password: (auto-generated, e.g., `Xyz789ABC!@#`)
- First Name: `Bob`
- Last Name: `Brown`

**Save credentials:**
```
User 2:
Email: bob@test.com
Password: Xyz789ABC!@#
```

### Test User 3: Carol (Individual User - Optional)

**Input:**
- Email: `carol@test.com`
- Password: (auto-generated)
- First Name: `Carol`
- Last Name: `Chen`

**Save credentials:**
```
User 3:
Email: carol@test.com
Password: [generated password]
```

### Verification

**Check Database:**
```sql
-- Verify users were created
SELECT id, email, account_status, account_type, date_created
FROM users
WHERE email IN ('alice@test.com', 'bob@test.com', 'carol@test.com');

-- Verify member profiles were created
SELECT m.id, m.fname, m.lname, m.email, m.leaderboard_visible, u.id as user_id
FROM members m
JOIN users u ON m.user_acnt_id = u.id
WHERE u.email IN ('alice@test.com', 'bob@test.com', 'carol@test.com');

-- Verify default preferences were created
SELECT COUNT(*) FROM user_preferences;
-- Should be 3 (or more if you had existing users)
```

---

## Step 3: Test User Login & Isolation

### Test 3.1: Login as Alice

1. **Logout** (if logged in as admin)
2. **Navigate to:** `http://your-domain.com/login.php`
3. **Login:**
   - Email: `alice@test.com`
   - Password: `Abc123XYZ!@#`

**Expected Result:**
- ✅ Login successful
- ✅ Redirected to dashboard
- ✅ See welcome message: "Hello Alice! Let's make this week count."

### Test 3.2: Create Alice's Data

**Create a Cycle:**
1. Go to "Manage Cycles" (if available in menu)
2. Or manually insert via SQL:

```sql
INSERT INTO cycles (name, start_date, end_date, created_by)
VALUES (
    'Alice Q1 2025',
    '2025-01-06',  -- Monday
    '2025-03-31',  -- 84 days later
    (SELECT id FROM users WHERE email = 'alice@test.com')
);
```

**Create Goals for Alice:**
```sql
-- Get Alice's user_id and cycle_id first
SET @alice_user_id = (SELECT id FROM users WHERE email = 'alice@test.com');
SET @alice_cycle_id = (SELECT id FROM cycles WHERE created_by = @alice_user_id LIMIT 1);

-- Assuming category_id=1 exists (Business/Career)
INSERT INTO goals (user_id, cycle_id, category_id, title, is_encrypted, created_at)
VALUES
    (@alice_user_id, @alice_cycle_id, 1, 'Alice Goal 1: Launch Product', 0, NOW()),
    (@alice_user_id, @alice_cycle_id, 1, 'Alice Goal 2: Grow Revenue', 0, NOW());
```

**Create Tasks for Alice:**
```sql
-- Get goal IDs
SET @alice_goal1 = (SELECT id FROM goals WHERE title LIKE 'Alice Goal 1%' LIMIT 1);

INSERT INTO tasks (goal_id, week_number, title, weekly_target, is_encrypted, created_at)
VALUES
    (@alice_goal1, 1, 'Alice Task Week 1', 3, 0, NOW()),
    (@alice_goal1, 2, 'Alice Task Week 2', 3, 0, NOW());
```

**Verify Alice can see her data:**
- ✅ Dashboard shows Alice's cycle
- ✅ Goals page shows Alice's 2 goals
- ✅ Tasks page shows Alice's tasks

### Test 3.3: Login as Bob

1. **Logout**
2. **Login as Bob:**
   - Email: `bob@test.com`
   - Password: `Xyz789ABC!@#`

**Expected Result:**
- ✅ Login successful
- ✅ Dashboard shows NO cycle (Bob hasn't created one yet)
- ✅ Empty state messages

### Test 3.4: Create Bob's Data

**Create Cycle for Bob:**
```sql
INSERT INTO cycles (name, start_date, end_date, created_by)
VALUES (
    'Bob Q1 2025',
    '2025-01-06',
    '2025-03-31',
    (SELECT id FROM users WHERE email = 'bob@test.com')
);
```

**Create Goals for Bob:**
```sql
SET @bob_user_id = (SELECT id FROM users WHERE email = 'bob@test.com');
SET @bob_cycle_id = (SELECT id FROM cycles WHERE created_by = @bob_user_id LIMIT 1);

INSERT INTO goals (user_id, cycle_id, category_id, title, is_encrypted, created_at)
VALUES
    (@bob_user_id, @bob_cycle_id, 1, 'Bob Goal 1: Learn Piano', 0, NOW()),
    (@bob_user_id, @bob_cycle_id, 1, 'Bob Goal 2: Run Marathon', 0, NOW());
```

### Test 3.5: Verify Data Isolation ⚠️ CRITICAL TEST

**While logged in as Bob:**

1. **Check Dashboard:**
   - ✅ Should see Bob's cycle only
   - ❌ Should NOT see Alice's cycle

2. **Check Goals Page:**
   - ✅ Should see Bob's 2 goals
   - ❌ Should NOT see Alice's goals

3. **Check Progress Page:**
   - ✅ Should see Bob's analytics only
   - ❌ Should NOT see Alice's data

4. **Try Direct URL Access (Security Test):**
   ```
   # Get Alice's goal ID from database
   # Try accessing it while logged in as Bob
   http://your-domain.com/12-week-plan-tasks.php?goal_id=[alice_goal_id]
   ```

   **Expected Result:**
   - ❌ Should show error or empty page
   - ✅ Should NOT show Alice's tasks

5. **Database Verification:**
   ```sql
   -- Verify Bob's session only has access to his own data
   -- Check that queries filter by user_id

   -- This should return 0 if isolation is working
   SELECT COUNT(*)
   FROM goals
   WHERE user_id = (SELECT id FROM users WHERE email = 'alice@test.com')
   AND user_id != (SELECT id FROM users WHERE email = 'bob@test.com');
   ```

**✅ PASS Criteria:**
- Bob sees ONLY his own goals, tasks, and progress
- Bob CANNOT access Alice's data via UI or direct URLs
- All queries in logs show `WHERE user_id = [bob_id]`

**❌ FAIL Criteria:**
- Bob can see Alice's goals/tasks
- Direct URL access to Alice's data works
- Database queries missing user_id filter

---

## Step 4: Test Account Settings

### Test 4.1: Profile Settings (as Alice)

**Login as Alice** → **Go to Account Settings**

**Test Profile Tab:**

1. **Update Name:**
   - Change First Name to: `Alicia`
   - Click "Save Profile"
   - **Expected:** Success message, name updated

2. **Change Timezone:**
   - Select: `America/Los_Angeles` (Pacific Time)
   - Click "Save Profile"
   - **Expected:** Success message

3. **Change Date Format:**
   - Select: `MM-DD-YYYY`
   - Click "Save Profile"
   - **Expected:** Dates now display in new format

**Verification:**
```sql
SELECT fname, time_zone, date_format
FROM members m
JOIN user_preferences up ON m.user_acnt_id = up.user_id
WHERE m.email = 'alice@test.com';

-- Expected:
-- fname: Alicia
-- time_zone: America/Los_Angeles
-- date_format: m-d-Y
```

### Test 4.2: Password Change (as Alice)

**Go to Password Tab:**

1. **Input:**
   - Current Password: `Abc123XYZ!@#`
   - New Password: `NewPassword123!`
   - Confirm Password: `NewPassword123!`

2. **Click "Change Password"**

**Expected Result:**
- ✅ Success message: "Password changed successfully"
- ✅ Password fields cleared

**Verification:**
1. **Logout**
2. **Try login with OLD password:** `Abc123XYZ!@#`
   - ❌ Should FAIL
3. **Login with NEW password:** `NewPassword123!`
   - ✅ Should SUCCEED

**Update saved credentials:**
```
User 1 (Updated):
Email: alice@test.com
Password: NewPassword123!
```

### Test 4.3: Privacy Settings (as Alice)

**Go to Privacy Tab:**

1. **Enable Leaderboard:**
   - Check "Show me on the leaderboard"
   - Leave Display Name blank (use real name)
   - Click "Save Privacy Settings"
   - **Expected:** Success message

2. **Set Pseudonym:**
   - Uncheck "Show me on leaderboard"
   - Enter Display Name: `ThePianoPlayer`
   - Check "Show me on leaderboard"
   - Click "Save Privacy Settings"
   - **Expected:** Success message

**Verification:**
```sql
SELECT leaderboard_visible, display_name
FROM members
WHERE email = 'alice@test.com';

-- Expected:
-- leaderboard_visible: 1
-- display_name: ThePianoPlayer
```

### Test 4.4: Notification Preferences (as Alice)

**Go to Notifications Tab:**

1. **Toggle Settings:**
   - Uncheck "Weekly Progress Summary"
   - Check "Achievement Notifications"
   - Check "Daily Task Reminders"
   - Click "Save Notification Preferences"

**Expected:** Success message

**Verification:**
```sql
SELECT email_weekly_summary, email_achievements, email_reminders
FROM user_preferences
WHERE user_id = (SELECT id FROM users WHERE email = 'alice@test.com');

-- Expected:
-- email_weekly_summary: 0
-- email_achievements: 1
-- email_reminders: 1
```

---

## Step 5: Test Leaderboard Privacy

### Test 5.1: Setup Privacy States

**Alice (logged in as alice@test.com):**
- ✅ Leaderboard visible: YES
- ✅ Display name: `ThePianoPlayer`

```sql
UPDATE members
SET leaderboard_visible = 1, display_name = 'ThePianoPlayer'
WHERE email = 'alice@test.com';
```

**Bob (while logged in as bob@test.com):**
- Account Settings → Privacy Tab
- ❌ Leaderboard visible: NO (leave unchecked)
- Display name: (leave blank)
- Save

```sql
UPDATE members
SET leaderboard_visible = 0, display_name = NULL
WHERE email = 'bob@test.com';
```

**Carol (if created):**
- ✅ Leaderboard visible: YES
- ✅ Display name: `GoalGetter2025`

```sql
UPDATE members
SET leaderboard_visible = 1, display_name = 'GoalGetter2025'
WHERE email = 'carol@test.com';
```

### Test 5.2: Create Activity for Leaderboard

**Add some stats:**
```sql
-- Create leaderboard stats for users
INSERT INTO leaderboard_stats (user_id, cycle_id, total_points, completion_rate, current_streak, is_visible, display_name)
VALUES
-- Alice (visible with pseudonym)
(
    (SELECT id FROM users WHERE email = 'alice@test.com'),
    (SELECT id FROM cycles WHERE created_by = (SELECT id FROM users WHERE email = 'alice@test.com') LIMIT 1),
    1250,
    85.5,
    7,
    1,
    'ThePianoPlayer'
),
-- Bob (NOT visible)
(
    (SELECT id FROM users WHERE email = 'bob@test.com'),
    (SELECT id FROM cycles WHERE created_by = (SELECT id FROM users WHERE email = 'bob@test.com') LIMIT 1),
    980,
    72.3,
    3,
    0,
    NULL
),
-- Carol (visible with pseudonym)
(
    (SELECT id FROM users WHERE email = 'carol@test.com'),
    (SELECT id FROM cycles WHERE created_by = (SELECT id FROM users WHERE email = 'carol@test.com') LIMIT 1),
    1100,
    78.9,
    5,
    1,
    'GoalGetter2025'
);
```

### Test 5.3: View Leaderboard

**Navigate to Leaderboard page (as any user)**

**Expected Results:**

✅ **Should See:**
- Rank 1: `ThePianoPlayer` - 1250 points, 85.5% completion (Alice with pseudonym)
- Rank 2: `GoalGetter2025` - 1100 points, 78.9% completion (Carol with pseudonym)

❌ **Should NOT See:**
- Bob (leaderboard_visible = 0)
- Real names "Alice Anderson" or "Carol Chen"
- Bob's stats (980 points, 72.3%)

✅ **Verify:**
- Total participants shown: 2 (not 3, because Bob is hidden)
- Names are pseudonyms, not real names
- No personal goal details visible

### Test 5.4: Privacy Toggle Test

**Login as Bob:**
1. Go to Account Settings → Privacy
2. **Enable leaderboard:**
   - Check "Show me on the leaderboard"
   - Set Display Name: `MarathonRunner`
   - Save

3. **Refresh Leaderboard:**
   - ✅ Should now show `MarathonRunner` in rankings
   - ✅ Total participants: 3

4. **Disable again:**
   - Uncheck "Show me on leaderboard"
   - Save

5. **Refresh Leaderboard:**
   - ❌ `MarathonRunner` should disappear
   - ✅ Total participants back to: 2

---

## Step 6: Test Admin Tools

### Test 6.1: Password Reset

**Access:** `http://your-domain.com/admin-reset-password.php?user_id=X`

(Replace X with Alice's user_id)

**Test Steps:**

1. **Page displays:**
   - ✅ Alice's name: "Alicia Anderson"
   - ✅ Alice's email: alice@test.com
   - ✅ Account status: Active

2. **Generate new password:**
   - Click "Generate" button
   - Example generated: `Qwerty987!@#`

3. **Click "Reset Password"**

**Expected Result:**
```
✓ Password reset successfully!

Important: Save these credentials
User: Alicia Anderson
Email: alice@test.com
New Password: Qwerty987!@#
⚠ This password will not be shown again.
```

**Verification:**
1. Logout
2. Login as Alice with NEW password: `Qwerty987!@#`
   - ✅ Should succeed

3. Try OLD password: `NewPassword123!`
   - ❌ Should fail

### Test 6.2: User Status Toggle

**Test Deactivation:**

```sql
-- Deactivate Bob's account
UPDATE users
SET account_status = 'inactive'
WHERE email = 'bob@test.com';
```

**Verification:**
1. Logout
2. Try logging in as Bob
   - ❌ Login should FAIL
   - ✅ Error message: "Account suspended" or similar

**Reactivate:**
```sql
UPDATE users
SET account_status = 'active'
WHERE email = 'bob@test.com';
```

**Verification:**
1. Try logging in as Bob again
   - ✅ Should now succeed

---

## Step 7: Security Testing

### Test 7.1: SQL Injection Protection

**Test Input:**
```
Email: admin' OR '1'='1
Password: anything
```

**Expected Result:**
- ❌ Login fails
- ✅ No SQL error displayed
- ✅ No database data exposed

### Test 7.2: CSRF Protection

**Test without CSRF token:**

1. Open browser console
2. Submit account settings form with token removed
3. **Expected:** Request rejected with error

### Test 7.3: Direct URL Access

**While logged in as Bob, try:**

```
http://your-domain.com/12-week-goals.php?user_id=X
```
(Where X = Alice's user_id)

**Expected Result:**
- ✅ Still shows only Bob's goals
- ✅ user_id parameter ignored
- ✅ System uses session user_id only

---

## Step 8: Cross-Browser Testing

Test on:
- ✅ Chrome/Edge
- ✅ Firefox
- ✅ Safari (if available)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

**Features to test:**
- Login/logout
- Account settings (all tabs)
- Goal/task creation
- Leaderboard view
- Responsive design on mobile

---

## Test Results Checklist

### ✅ Database Migration
- [ ] Migration runs successfully
- [ ] All new tables created
- [ ] All new columns added
- [ ] Existing data preserved
- [ ] Indexes created

### ✅ User Creation
- [ ] Can create users via admin tool
- [ ] Email validation works
- [ ] Password generation works
- [ ] Duplicate email prevention works
- [ ] User appears in list

### ✅ User Login
- [ ] Can login with correct credentials
- [ ] Cannot login with wrong password
- [ ] Cannot login with deactivated account
- [ ] Session maintained across pages
- [ ] Logout works correctly

### ✅ Data Isolation
- [ ] User A cannot see User B's goals
- [ ] User A cannot see User B's tasks
- [ ] User A cannot see User B's progress
- [ ] Direct URL access blocked
- [ ] Database queries have user_id filter

### ✅ Account Settings
- [ ] Profile update works
- [ ] Password change works
- [ ] Old password required for change
- [ ] Timezone setting applies
- [ ] Date format setting applies

### ✅ Privacy Controls
- [ ] Leaderboard opt-in works
- [ ] Opt-out hides user from leaderboard
- [ ] Display name (pseudonym) works
- [ ] Real name not shown when pseudonym set

### ✅ Leaderboard
- [ ] Only opt-in users visible
- [ ] Pseudonyms displayed correctly
- [ ] Real names hidden
- [ ] No personal goal details shown
- [ ] Participant count correct

### ✅ Admin Tools
- [ ] Can create users manually
- [ ] Password displayed once
- [ ] Can reset passwords
- [ ] Can toggle account status
- [ ] User list displays correctly

### ✅ Security
- [ ] SQL injection blocked
- [ ] CSRF protection works
- [ ] Passwords hashed in database
- [ ] Session hijacking prevented
- [ ] XSS protection active

---

## Common Issues & Solutions

### Issue: Migration fails with "Column already exists"

**Solution:**
- Migration is idempotent, safe to re-run
- Or comment out failing ALTER statements

### Issue: User cannot login after creation

**Check:**
```sql
SELECT id, email, account_status, status
FROM users
WHERE email = 'user@test.com';
```

**Fix:**
```sql
UPDATE users
SET account_status = 'active', status = 1
WHERE email = 'user@test.com';
```

### Issue: Leaderboard shows everyone despite opt-out

**Check:**
```sql
SELECT email, leaderboard_visible
FROM members;
```

**Fix:**
```sql
-- Sync visibility
UPDATE leaderboard_stats ls
JOIN members m ON ls.user_id = m.user_acnt_id
SET ls.is_visible = m.leaderboard_visible;
```

### Issue: Account settings changes not persisting

**Check:**
```sql
SELECT * FROM user_preferences
WHERE user_id = X;
```

**Fix:**
```sql
-- Create if missing
INSERT INTO user_preferences (user_id) VALUES (X);
```

---

## Performance Testing

### Test with Multiple Users

Create 10-20 test users and:
- [ ] Check dashboard load time
- [ ] Check goals page performance
- [ ] Check leaderboard query speed
- [ ] Monitor database query count

### Expected Performance

- Dashboard: < 500ms
- Goals page: < 300ms
- Leaderboard: < 400ms
- Account settings: < 200ms

---

## Final Verification

After all tests pass:

1. **Review Error Logs:**
   ```bash
   tail -100 includes/system-error-log.txt
   ```
   - Should have no critical errors

2. **Check Audit Logs:**
   ```sql
   SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 50;
   ```
   - Should show login/logout events

3. **Verify Data Integrity:**
   ```sql
   -- All users should have preferences
   SELECT COUNT(*) FROM users;
   SELECT COUNT(*) FROM user_preferences;
   -- Counts should match

   -- All goals should belong to valid users
   SELECT COUNT(*) FROM goals g
   LEFT JOIN users u ON g.user_id = u.id
   WHERE u.id IS NULL;
   -- Should return 0
   ```

4. **Backup Database:**
   ```bash
   mysqldump -u username -p database_name > phase1_tested_$(date +%Y%m%d).sql
   ```

---

## ✅ Testing Complete!

When all tests pass, Phase 1 is ready for production deployment!

**Next Steps:**
- Deploy to production server
- Monitor for first week
- Gather user feedback
- Plan Phase 2 features

---

*Generated: 2025-11-08*
*Phase 1: Individual SaaS Mode*
*Testing Guide Version 1.0*
