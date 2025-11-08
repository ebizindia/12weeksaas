# Phase 1: Individual SaaS Mode - Implementation Guide

## Overview

Phase 1 converts the 12-Week Year system from an organization-based platform to an individual user SaaS application. Users now have completely isolated workspaces with privacy-first features.

---

## What Changed

### 🔒 **Privacy & Data Isolation**
- ✅ Each user has a completely isolated workspace
- ✅ No admin access to user's goals/tasks (encrypted data)
- ✅ Leaderboard is opt-in only (privacy-first)
- ✅ Users can use pseudonyms on public leaderboard
- ✅ All queries scoped to logged-in user only

### 👥 **User Management**
- ✅ Manual user creation via admin tool (no self-signup yet)
- ✅ Removed organization/group dependencies
- ✅ Removed admin oversight of individual user data
- ✅ Each user manages their own cycles, goals, and tasks

### ⚙️ **Account Settings**
- ✅ Profile management (name, timezone, date format)
- ✅ Password change with secure validation
- ✅ Privacy controls (leaderboard visibility, display name)
- ✅ Email notification preferences

### 🛠️ **Admin Tools**
- ✅ `admin-add-user.php` - Create new user accounts
- ✅ `admin-reset-password.php` - Reset user passwords
- ✅ Secure password generation
- ✅ Admin-only access controls

---

## Database Changes

### New Tables Created

**`user_preferences`**
```sql
- user_id (FK to users.id)
- date_format
- time_zone
- email_weekly_summary
- email_achievements
- email_reminders
- theme
- items_per_page
```

**`audit_logs`**
```sql
- user_id (FK to users.id)
- action
- ip_address
- user_agent
- metadata (JSON)
- created_at
```

### Modified Tables

**`users` table - New columns:**
- `account_status` - ENUM('active', 'inactive', 'suspended')
- `account_type` - ENUM('individual', 'admin')
- `created_by` - INT (admin who created account)
- `notes` - TEXT (admin notes)

**`members` table - New columns:**
- `display_name` - VARCHAR(100) (pseudonym for leaderboard)
- `leaderboard_visible` - BOOLEAN (opt-in flag)

**`leaderboard_stats` table - New columns:**
- `display_name` - VARCHAR(100)
- `is_visible` - BOOLEAN (synced with members table)

---

## Installation & Setup

### Step 1: Run Database Migration

**Option A: Via PHP Script**
```bash
php migrations/run-phase1-migration.php --force
```

**Option B: Manual SQL Import**
```bash
mysql -u username -p database_name < migrations/phase1-individual-saas-migration.sql
```

**Verification:**
- Check that `user_preferences` table exists
- Check that `users` table has new columns
- Check that `members` table has `display_name` column

### Step 2: Configuration Already Applied

The `config.php` file already has Phase 1 settings:
```php
define('CONST_SAAS_MODE', true);
define('CONST_SHOW_ADMIN_DASHBOARD', false);
define('CONST_ENABLE_MEMBER_GROUPS', false);
define('CONST_ENABLE_ADMIN_OVERSIGHT', false);
define('CONST_LEADERBOARD_OPT_IN_DEFAULT', false);
```

### Step 3: Create Your First User

**Access Admin Tool:**
1. Navigate to: `http://your-domain.com/admin-add-user.php`
2. Fill in user details:
   - Email (used for login)
   - Password (min 8 characters)
   - First Name
   - Last Name
3. Click "Create User"
4. **IMPORTANT:** Copy the displayed password immediately (shown only once)
5. Send credentials to the user securely

**Example:**
```
Email: john@example.com
Password: abc123XYZ!@# (auto-generated)
First Name: John
Last Name: Doe
```

---

## User Experience

### For End Users

#### 1. **Login**
- Use email and password provided by admin
- Access at: `http://your-domain.com/login.php`

#### 2. **First Time Setup**
- Go to **Account Settings** (in sidebar menu)
- Update profile information
- Set timezone and date format preferences
- Change password to something memorable
- Configure email notification preferences

#### 3. **Privacy Settings**
- Decide if you want to appear on leaderboard
- Set a display name (pseudonym) if desired
- Leaderboard is OFF by default for privacy

#### 4. **Using the System**
- **Dashboard**: Overview of current cycle
- **Goals**: Create goals for current 12-week cycle
- **Plan Tasks**: Break goals into weekly tasks
- **Weekly View**: Track daily task completion
- **Progress**: View personal analytics
- **Leaderboard**: See rankings (if opted in)

---

## Admin Workflows

### Create New User Account

1. Go to `admin-add-user.php`
2. Enter user details
3. Copy generated password
4. Share credentials with user via secure channel (email, message)

### Reset User Password

1. Go to `admin-add-user.php`
2. Click "Reset Password" next to user
3. Or directly access: `admin-reset-password.php?user_id=X`
4. Copy new password
5. Share with user securely

### Toggle User Account Status

1. Go to `admin-add-user.php`
2. Click "Toggle Status" to activate/deactivate account
3. Inactive users cannot log in

---

## Security Features

### Implemented Security Measures

✅ **SQL Injection Prevention**
- All queries use prepared statements
- Input validation on all fields

✅ **Password Security**
- Bcrypt hashing (industry standard)
- Minimum 8 character requirement
- Secure password generation option

✅ **CSRF Protection**
- All forms include CSRF tokens
- Token validation on submission

✅ **Data Encryption**
- Goals and tasks encrypted at rest
- AES-256-GCM encryption
- Per-user encryption keys

✅ **Session Security**
- Secure cookie flags (HttpOnly, Secure, SameSite)
- Session regeneration after login
- IP-based validation

✅ **Access Control**
- Row-level security (WHERE user_id = :user_id)
- Role-based access (INDIVIDUAL_USER vs SYSTEM_ADMIN)
- No cross-user data access

---

## File Structure

### New Files Created

```
12weeksaas/
├── account-settings.php              # User account settings page
├── admin-add-user.php                # Admin tool: Create users
├── admin-reset-password.php          # Admin tool: Reset passwords
├── cls/
│   └── UserPreferences.php           # User preferences management class
├── migrations/
│   ├── phase1-individual-saas-migration.sql  # Database migration
│   └── run-phase1-migration.php              # Migration runner
└── templates/
    └── account-settings.tpl          # Account settings template
```

### Modified Files

```
12weeksaas/
├── config.php                        # Added Phase 1 SaaS settings
├── 12-week-dashboard.php             # Removed admin references
├── 12-week-progress.php              # Disabled admin view ($is_admin = false)
├── 12-week-leaderboard.php           # Privacy-first queries (opt-in)
└── templates/
    └── sidebar.tpl                   # Added Account Settings link
```

### Disabled Files

```
12weeksaas/
├── 12-week-admin-dashboard.php.disabled  # Organization admin dashboard
├── users.php.disabled                    # User management by admin
└── mem-regs.php.disabled                 # Member registration approval
```

---

## Feature Flags

Configure these in `config.php`:

```php
// Enable/Disable Features
CONST_SAAS_MODE                    = true   // Enable individual SaaS mode
CONST_REQUIRE_SIGNUP               = false  // Self-service signup (Phase 2)
CONST_SHOW_ADMIN_DASHBOARD         = false  // Hide org admin dashboard
CONST_ENABLE_MEMBER_GROUPS         = false  // Disable organization features
CONST_ENABLE_ADMIN_OVERSIGHT       = false  // No admin access to user data
CONST_ENABLE_LEADERBOARD           = true   // Keep leaderboard (opt-in)
CONST_LEADERBOARD_OPT_IN_DEFAULT   = false  // Default to private
CONST_MIN_PASSWORD_LENGTH          = 8      // Password requirement
CONST_ALLOW_USER_DELETE_ACCOUNT    = false  // Phase 1: Admin managed
```

---

## Testing Checklist

### Database Testing
- [ ] Migration runs successfully
- [ ] All new tables created
- [ ] Existing data preserved
- [ ] Default preferences created for users

### User Isolation Testing
- [ ] Create 2 test users
- [ ] User A creates goals/tasks
- [ ] Login as User B
- [ ] Verify User B cannot see User A's data
- [ ] Check dashboard, goals, tasks, progress

### Account Settings Testing
- [ ] Update profile information
- [ ] Change password successfully
- [ ] Toggle leaderboard visibility
- [ ] Set display name (pseudonym)
- [ ] Update email preferences
- [ ] Verify changes persist after logout/login

### Leaderboard Privacy Testing
- [ ] Create 2 users
- [ ] User A opts OUT of leaderboard
- [ ] User B opts IN with pseudonym
- [ ] View leaderboard
- [ ] Verify User A is hidden
- [ ] Verify User B shows pseudonym, not real name

### Admin Tools Testing
- [ ] Create new user via admin-add-user.php
- [ ] Verify user can login with generated password
- [ ] Reset user password via admin-reset-password.php
- [ ] Verify new password works
- [ ] Toggle user status (active/inactive)
- [ ] Verify inactive user cannot login

---

## Troubleshooting

### Migration Issues

**Error: Database connection failed**
- Check database credentials in `config.php`
- Verify MySQL server is running
- Check user has CREATE/ALTER permissions

**Error: Column already exists**
- Migration is idempotent, safe to re-run
- Or manually skip conflicting statements

### Login Issues

**Cannot login after creating user**
- Verify account_status = 'active' in database
- Check password was copied correctly
- Try resetting password via admin tool

### Data Not Showing

**User sees no goals/tasks**
- Check if user has created a cycle first
- Verify current date falls within cycle dates
- Check database for user_id = X in goals table

### Leaderboard Issues

**User not appearing on leaderboard**
- Check `members.leaderboard_visible = 1`
- Check `leaderboard_stats.is_visible = 1`
- Verify user has activity in current cycle

---

## Known Limitations (Phase 1)

⚠️ **No Self-Service Signup**
- Users must be created manually by admin
- Planned for Phase 2

⚠️ **No Billing/Subscriptions**
- All users have full access
- Payment integration planned for Phase 3

⚠️ **No Email Verification**
- Email addresses not verified on creation
- Planned for Phase 2

⚠️ **No Account Deletion**
- Users cannot delete their own accounts
- Admin must handle deletion requests

---

## Next Steps (Future Phases)

### Phase 2: Self-Service Registration
- Public signup page
- Email verification
- Password reset flow
- Onboarding wizard

### Phase 3: Subscription & Billing
- Stripe integration
- Free/Pro/Premium tiers
- Trial period management
- Usage limits

### Phase 4: Enhanced Features
- Goal templates library
- Advanced analytics
- Export to PDF
- Weekly email summaries

---

## Support & Maintenance

### Backup Recommendations

**Before Running Migration:**
```bash
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
```

**Regular Backups:**
- Daily database backups
- Weekly full system backups
- Test restore procedures

### Monitoring

**Key Metrics to Track:**
- Active users count
- Daily login count
- Goals created per week
- Task completion rates
- Error log entries

### Log Files

**Error Logs:**
- `includes/system-error-log.txt` - Application errors
- Check regularly for issues

**Audit Logs:**
- `audit_logs` table - User actions
- Track security events

---

## Contact & Resources

**Documentation:**
- This guide: `docs/PHASE1-GUIDE.md`
- Migration script: `migrations/phase1-individual-saas-migration.sql`

**Code References:**
- User settings: `cls/UserPreferences.php`
- Account page: `account-settings.php`
- Admin tools: `admin-add-user.php`, `admin-reset-password.php`

**Git Commits:**
- Phase 1 Database & Admin Removal: `b7025e2`
- Phase 1 Account Settings & Tools: `c24c613`

---

## FAQ

**Q: Can users see each other's goals?**
A: No. All data is completely isolated. Even admins cannot view encrypted goals/tasks.

**Q: How do I disable the leaderboard entirely?**
A: Set `CONST_ENABLE_LEADERBOARD = false` in config.php

**Q: Can users sign up themselves?**
A: Not in Phase 1. Phase 2 will add self-service registration.

**Q: Where is user data encrypted?**
A: Goals and tasks are encrypted using AES-256-GCM. Encryption keys are derived from system secret + user ID.

**Q: How do I add menu items?**
A: Menu items are stored in database tables (menucategories, menus, menu_perms). Use database tools to add/modify.

**Q: Can I migrate existing organization users?**
A: Yes. Run the migration script. Existing users will be converted to individual accounts.

---

## Changelog

### Version: Phase 1 (2025-11-08)

**Added:**
- Individual user isolation and privacy controls
- Account settings page with 4 tabs (Profile, Password, Privacy, Notifications)
- Admin tools for manual user creation and password reset
- User preferences system (timezone, date format, email settings)
- Leaderboard opt-in with pseudonym support
- Database migration scripts

**Changed:**
- All queries scoped to logged-in user only
- Leaderboard requires explicit opt-in (privacy-first)
- Progress reports show personal data only
- Dashboard messaging changed to self-service

**Removed:**
- Admin oversight of user data
- Organization/group management features
- Member registration approval workflow
- Admin dashboard for viewing all users

**Deprecated:**
- `12-week-admin-dashboard.php`
- `users.php`
- `mem-regs.php`

---

*End of Phase 1 Implementation Guide*
