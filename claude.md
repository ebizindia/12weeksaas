# 12 Week Year SaaS System - Documentation

## System Overview

This is a 12-week goal planning and tracking system built with PHP (custom MVC framework), MySQL, Bootstrap 5, and jQuery. Users sign up individually to manage their own cycles, categories, goals, and tasks.

### Technology Stack
- **Backend:** PHP 7+ with custom MVC architecture
- **Database:** MySQL (via PDO with prepared statements)
- **Frontend:** HTML5, CSS3, Bootstrap 5, jQuery 3.6.0
- **Security:** AES-256 encryption for sensitive data, CSRF protection, XSS headers
- **Additional:** PHPMailer (email), Instamojo (payments), CKEditor 5, FullCalendar

---

## Core Module Analysis

### 1. User Authentication & Registration

**File:** `/home/user/12weeksaas/login.php`, `/home/user/12weeksaas/cls/User.php`

**Current Implementation:**
- User login with email/password authentication
- Password hashing using `password_hash()` with bcrypt
- CSRF token protection on all forms
- Session regeneration on successful login
- "Remember me" functionality with secure cookies
- Password reset via email link
- Role-based access control (ADMIN, REGULAR)

**Member Registration Flow:**
- File: `/home/user/12weeksaas/mem-regs.php`
- Users fill registration form with profile details
- **CRITICAL**: Registration requires **ADMIN APPROVAL** (lines 299-458)
- Admin must manually approve before user gets login access
- Upon approval: member record created, user account created, welcome email sent

**Security Features:**
- SQL injection prevention (PDO prepared statements)
- XSS protection headers
- CSRF tokens on all forms
- Session fixation prevention
- HTTPOnly and SameSite cookies
- IP-based access restrictions (optional)

---

### 2. Cycle Management

**File:** `/home/user/12weeksaas/12-week-manage-cycles.php`

**Current Implementation:**
```php
// Lines 11-16: Admin-only check
$is_admin = ($loggedindata[0]['profile_details']['assigned_roles'][0]['role'] === 'ADMIN');
if (!$is_admin) {
    header("Location: " . CONST_APP_ABSURL . "/");
    exit;
}
```

**⚠️ CRITICAL ISSUE:** Cycle management is **ADMIN-ONLY**

**Features:**
- Create 12-week cycles with name and start date
- Start date must be a Monday (validated client and server-side)
- End date auto-calculated (+83 days = 12 weeks)
- Validation prevents overlapping cycles
- Automatic status determination based on dates
- Each cycle tracks: member count, goals count, current week

**Database Table:** `cycles`
```sql
CREATE TABLE cycles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR,
    start_date DATE,
    end_date DATE,
    created_by INT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

---

### 3. Category Management

**File:** `/home/user/12weeksaas/12-week-manage-categories.php`

**Current Implementation:**
```php
// Lines 11-16: Admin-only check
$is_admin = ($loggedindata[0]['profile_details']['assigned_roles'][0]['role'] === 'ADMIN');
if (!$is_admin) {
    header("Location: " . CONST_APP_ABSURL . "/");
    exit;
}
```

**⚠️ CRITICAL ISSUE:** Category management is **ADMIN-ONLY**

**Features:**
- Create/edit goal categories
- Color-coding (hex color picker)
- Sort ordering (drag & drop with Sortable.js)
- Active/inactive status toggle
- Duplicate name validation
- Delete protection if goals exist
- Categories apply to all users globally

**Database Table:** `categories`
```sql
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR,
    color_code VARCHAR(7),  -- Hex color
    sort_order INT,
    is_active TINYINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

---

### 4. Goals Management (✓ User Self-Service)

**File:** `/home/user/12weeksaas/12-week-goals.php`
**Class:** `/home/user/12weeksaas/cls/TwelveWeekGoals.php`

**Features:**
- Users can create/edit/delete their own goals
- Goals organized by categories
- Goals linked to specific cycle
- Automatic encryption of goal titles (AES-256)
- Security: user_id verification on all operations
- Gamification tracking (goal_created event)
- Task counts displayed per goal
- Week-by-week view of tasks

**Operations:**
- `add_goal` - Create new goal (requires ADD permission)
- `edit_goal` - Update goal (requires EDIT permission)
- `delete_goal` - Delete goal and all tasks (requires DELETE permission)
- `get_tasks` - Fetch tasks for a goal
- `add_task` - Add task to goal
- `update_day_completion` - Mark task day complete

**Database Table:** `goals`
```sql
CREATE TABLE goals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,              -- User ownership
    cycle_id INT,             -- Links to cycle
    category_id INT,          -- Links to category
    title TEXT,               -- ENCRYPTED
    is_encrypted TINYINT,
    encryption_key_id VARCHAR,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (cycle_id) REFERENCES cycles(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
)
```

**Security:** Line 210 - verifies goal belongs to user before operations
```php
$goalExists = \eBizIndia\TwelveWeekGoals::getGoal($goal_id, $user_id);
```

---

### 5. Task Planning (✓ User Self-Service)

**File:** `/home/user/12weeksaas/12-week-plan-tasks.php`
**Class:** `/home/user/12weeksaas/cls/TwelveWeekTasks.php`

**Features:**
- Users can create/edit/delete their own tasks
- Tasks organized by goals and weeks (1-12)
- Weekly target setting (1-7 days per week)
- Task title encryption (AES-256)
- Copy tasks from one week to another
- Security: ownership verification via goal
- Gamification tracking (task_planned event)

**Operations:**
- `add_task` - Create task for specific week
- `edit_task` - Update task description
- `delete_task` - Remove task
- `copy_tasks` - Duplicate tasks to another week

**Database Table:** `tasks`
```sql
CREATE TABLE tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    goal_id INT,
    week_number INT(1-12),
    title TEXT,               -- ENCRYPTED
    weekly_target INT,        -- Days per week target (1-7)
    mon TINYINT,              -- Daily completion flags
    tue TINYINT,
    wed TINYINT,
    thu TINYINT,
    fri TINYINT,
    sat TINYINT,
    sun TINYINT,
    is_encrypted TINYINT,
    encryption_key_id VARCHAR,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (goal_id) REFERENCES goals(id) ON DELETE CASCADE
)
```

**Security:** Line 68 - verifies task ownership through goal
```php
$task_check = \eBizIndia\TwelveWeekTasks::getTask($task_id, $user_id);
```

---

### 6. Weekly Tracking (✓ User Self-Service)

**File:** `/home/user/12weeksaas/12-week-weekly.php`

**Features:**
- View current week's tasks
- Check off daily completion (Mon-Sun)
- Quick task addition
- Task organized by category and goal
- Visual progress indicators
- Week navigation (prev/next)
- Current week highlighting
- Real-time AJAX updates

**Daily Tracking:**
- 7 checkboxes per task (Mon-Sun)
- AJAX update on checkbox change
- Automatic weekly score calculation
- Gamification: task_completed event
- Streak tracking

**Weekly Statistics:**
```php
$week_stats = [
    'total_tasks' => 0,
    'total_checkboxes' => 0,
    'completed_checkboxes' => 0,
    'completion_percentage' => 0
];
```

---

### 7. Progress & Analytics (✓ User Self-Service)

**File:** `/home/user/12weeksaas/12-week-progress.php`

**Features:**
- Overall cycle progress percentage
- Weekly scores trend
- Category-wise performance breakdown
- Completion trends graph
- Goal/task statistics
- Admin: Member rankings and leaderboard
- Users: Personal stats only
- Cycle selector to view past cycles

**Analytics Data:**
- Current week calculation
- Cycle progress (% through cycle)
- Weekly scores (12 weeks)
- Category performance (completion rate by category)
- Goal statistics (total goals, avg tasks per goal)
- Task statistics (completion rates, fully completed, not started)
- Member rankings (admin only)

**Database Tables:** `weekly_scores`, `user_stats`, `leaderboard_stats`

---

### 8. Gamification System

**File:** `/home/user/12weeksaas/cls/Gamification.php`

**Features:**
- Points and achievements system
- Daily activity tracking
- Streak calculation (consecutive active days)
- Leaderboard rankings
- User statistics per cycle
- Achievement unlocking
- Recent achievements display

**Activity Types:**
- `task_completed` - Increments total tasks completed
- `goal_created` - Increments total goals created
- `task_planned` - Increments total tasks planned
- `week_completed` - Marks week as complete
- `perfect_week` - Increments perfect weeks counter

**Tables:**
```sql
user_stats (user_id, cycle_id, total_tasks_completed, total_goals_created,
            current_streak, longest_streak, perfect_weeks)
daily_activity (user_id, cycle_id, activity_date, tasks_completed, goals_created)
leaderboard_stats (user_id, cycle_id, total_points, completion_rate, rank_position)
user_achievements (user_id, achievement_id, earned_date)
weekly_scores (user_id, cycle_id, week_number, score_percentage, total_checkboxes, completed_checkboxes)
```

---

## Security Architecture

### Data Encryption

**File:** `/home/user/12weeksaas/cls/Encryption.php`

**Implementation:**
- Goals and tasks are encrypted at rest
- Uses AES-256-CBC or AES-256-GCM
- Shared key encryption per module
- Key derivation: `system_secret + module_salt + date`
- Automatic encryption/decryption in data classes

```php
// Example from TwelveWeekGoals.php
$encrypted = Encryption::encryptShared($data['title'], 'twelve_week_goals');
$decrypted = Encryption::decryptShared($row['title'], 'twelve_week_goals');
```

### Access Control

**Permission System:**
- `VIEW` - Can view module
- `ADD` - Can create records
- `EDIT` - Can update records
- `DELETE` - Can delete records
- `ALL` - Full access

**Implementation (inc.php:217-237):**
```php
$allowed_menu_perms = [];
foreach($adminmenulist as $cat) {
    foreach($cat['menus'] as $menu) {
        if($menu['menupage'] == $page) {
            if($menu['availableByDefault'] == '1')
                $allowed_menu_perms = ['ALL'];
            else
                $allowed_menu_perms = $menu['perms'];
            break;
        }
    }
}
```

**Row-Level Security:**
- All queries include `user_id` verification
- Goals: `WHERE g.user_id = :user_id`
- Tasks: Verified through goal ownership
- Progress: User sees only their own data (unless admin)

---

## Critical Issues Identified

### 🔴 Issue #1: Cycles are Admin-Only (CRITICAL)

**Location:** `12-week-manage-cycles.php:12-16`

**Problem:**
Users CANNOT create their own 12-week cycles. Only administrators can create cycles.

**Current Implementation:**
```php
$is_admin = ($loggedindata[0]['profile_details']['assigned_roles'][0]['role'] === 'ADMIN');
if (!$is_admin) {
    header("Location: " . CONST_APP_ABSURL . "/");
    exit;
}
```

**Impact:**
- Users depend on admin to create cycles
- Users cannot set their own cycle start dates
- Violates requirement: "They set their own cycles, categories, goals and tasks"
- Users must wait for admin to create cycles before they can start planning

**Expected Behavior:**
Each user should be able to:
- Create their own 12-week cycles
- Choose their own start dates
- Run multiple cycles independently
- Have cycles isolated per user

**Recommended Solution:**
- Add `user_id` column to `cycles` table
- Remove admin-only check
- Modify queries to filter `WHERE user_id = :user_id`
- Show only user's own cycles in dropdown
- Allow personal cycle management

---

### 🔴 Issue #2: Categories are Admin-Only (CRITICAL)

**Location:** `12-week-manage-categories.php:12-16`

**Problem:**
Users CANNOT create their own goal categories. Only administrators can create categories, and these categories are global (shared across all users).

**Current Implementation:**
```php
$is_admin = ($loggedindata[0]['profile_details']['assigned_roles'][0]['role'] === 'ADMIN');
if (!$is_admin) {
    header("Location: " . CONST_APP_ABSURL . "/");
    exit;
}
```

**Impact:**
- Users cannot customize categories for their needs
- All users share the same categories
- Admin must create categories for all users
- Violates requirement: "They set their own cycles, categories, goals and tasks"
- Cannot accommodate different user types (business owner, student, etc.)

**Expected Behavior:**
Each user should be able to:
- Create their own goal categories
- Choose category colors
- Set category sort order
- Enable/disable categories
- Categories should be user-specific

**Recommended Solution:**
- Add `user_id` column to `categories` table
- Remove admin-only check
- Modify queries to filter `WHERE user_id = :user_id`
- Pre-seed default categories for new users (optional)
- Allow per-user category customization

---

### 🟡 Issue #3: Registration Requires Admin Approval (MAJOR)

**Location:** `mem-regs.php:299-458`

**Problem:**
New users cannot use the system immediately after registration. Admin must manually approve each registration before user gets login access.

**Current Flow:**
1. User fills registration form
2. Record saved with status 'New'
3. Admin receives notification
4. Admin manually approves
5. System creates user account and sends credentials
6. User can now login

**Impact:**
- Not self-service for individual users
- Delays user onboarding
- Requires admin intervention
- Scaling issue for SaaS model

**Expected Behavior:**
Users should be able to:
- Register and immediately login
- Set their own password during registration
- Start using the system without approval
- (Optional) Email verification for security

**Recommended Solution:**
- Add direct registration flow
- Create user account immediately upon registration
- Set `status='active'` and `active='y'` automatically
- Send verification email (optional)
- Remove admin approval requirement
- Keep approval flow as optional admin feature

---

### 🟡 Issue #4: No Active Cycle Check on Goals/Tasks (MAJOR)

**Location:** Multiple files

**Problem:**
When users try to create goals or tasks, they see error "No active cycle found. Please contact your administrator."

**Current Implementation:**
```php
// 12-week-goals.php:18-23
$current_cycle = \eBizIndia\getCurrentCycleByDate();
if (!$current_cycle) {
    $error_message = "No active 12-week cycle found. Please contact your administrator to create a new cycle.";
}
```

**Impact:**
- Users blocked from creating goals
- Users cannot start planning without admin
- Error message tells users to contact admin (not self-service)

**Root Cause:**
- Users cannot create cycles (Issue #1)
- `getCurrentCycleByDate()` returns null if no active cycle exists
- Function looks for system-wide cycle, not user-specific

**Recommended Solution:**
- After fixing Issue #1, modify to get user's active cycle
- Prompt user to create their first cycle
- Guide new users through cycle creation
- Allow multiple active cycles per user

---

### 🟢 Issue #5: Global vs. User-Specific Data Confusion (MINOR)

**Problem:**
System mixes global (admin-managed) and user-specific data without clear separation.

**Current State:**
- **Global (Admin):** Cycles, Categories
- **User-Specific:** Goals, Tasks, Progress

**Expected for Individual Use:**
- **User-Specific:** Cycles, Categories, Goals, Tasks, Progress
- **Global (Optional):** Templates, Tutorials

**Recommendation:**
- Add `user_id` to cycles and categories tables
- Add `is_template` flag for shared resources
- Clear data ownership model
- User sees only their own data

---

### 🟢 Issue #6: Missing User Onboarding Flow (MINOR)

**Problem:**
No guided setup for new users.

**Current Experience:**
1. Admin approves registration
2. User receives login credentials
3. User logs in and sees empty dashboard
4. Error: "No active cycle found"
5. User stuck

**Expected Experience:**
1. User registers
2. Email verification (optional)
3. Login
4. Welcome wizard:
   - Create first cycle
   - Set up categories
   - Create first goal
   - Add tasks
5. Tutorial/help available

**Recommendation:**
- Create onboarding wizard component
- Detect first-time users
- Guide through setup steps
- Provide default templates
- Help documentation

---

## Module Self-Service Capability Matrix

| Module | Can User Operate Alone? | Admin Required? | Notes |
|--------|------------------------|-----------------|-------|
| **Registration** | ❌ NO | ✅ YES | Admin approval required |
| **Cycle Management** | ❌ NO | ✅ YES | Admin-only access |
| **Category Management** | ❌ NO | ✅ YES | Admin-only access |
| **Goals Management** | ✅ YES | ❌ NO | Fully self-service |
| **Task Planning** | ✅ YES | ❌ NO | Fully self-service |
| **Weekly Tracking** | ✅ YES | ❌ NO | Fully self-service |
| **Progress Reports** | ✅ YES | ❌ NO | Fully self-service |
| **Gamification** | ✅ YES | ❌ NO | Automatic, no intervention |

**Summary:**
- ✅ Users can operate: 5/8 modules independently
- ❌ Admin required: 3/8 modules (Registration, Cycles, Categories)

---

## Database Schema

### Core Tables

```sql
-- User Authentication
users (
    id INT PRIMARY KEY,
    username VARCHAR (email),
    password VARCHAR (hashed),
    profile_type ENUM('member', 'emp'),
    profile_id INT,
    status TINYINT,
    created_at TIMESTAMP
)

-- Member Profiles
members (
    id INT PRIMARY KEY,
    membership_no VARCHAR,
    name VARCHAR,
    email VARCHAR,
    mobile VARCHAR,
    gender ENUM('M','F'),
    dob DATE,
    active ENUM('y','n')
)

-- Roles & Permissions
roles (
    role_id INT PRIMARY KEY,
    role_name VARCHAR ('ADMIN', 'REGULAR'),
    role_for VARCHAR
)

user_roles (
    user_id INT,
    role_id INT
)

-- 12-Week System Tables
cycles (
    id INT PRIMARY KEY,
    name VARCHAR,
    start_date DATE,
    end_date DATE,
    created_by INT,  -- ⚠️ Should be user_id for user-specific
    created_at TIMESTAMP
)

categories (
    id INT PRIMARY KEY,
    name VARCHAR,
    color_code VARCHAR(7),
    sort_order INT,
    is_active TINYINT,
    created_at TIMESTAMP
    -- ⚠️ Missing: user_id for user-specific categories
)

goals (
    id INT PRIMARY KEY,
    user_id INT,  -- ✅ User-specific
    cycle_id INT,
    category_id INT,
    title TEXT (ENCRYPTED),
    is_encrypted TINYINT,
    encryption_key_id VARCHAR,
    created_at TIMESTAMP
)

tasks (
    id INT PRIMARY KEY,
    goal_id INT,
    week_number INT(1-12),
    title TEXT (ENCRYPTED),
    weekly_target INT,
    mon, tue, wed, thu, fri, sat, sun TINYINT,
    is_encrypted TINYINT,
    created_at TIMESTAMP
)

-- Gamification Tables
user_stats (
    id INT PRIMARY KEY,
    user_id INT,
    cycle_id INT,
    total_tasks_completed INT,
    total_goals_created INT,
    current_streak INT,
    longest_streak INT,
    perfect_weeks INT,
    last_activity_date DATE
)

daily_activity (
    id INT PRIMARY KEY,
    user_id INT,
    cycle_id INT,
    activity_date DATE,
    tasks_completed INT,
    goals_created INT,
    was_active TINYINT
)

leaderboard_stats (
    id INT PRIMARY KEY,
    user_id INT,
    cycle_id INT,
    total_points INT,
    completion_rate DECIMAL,
    current_streak INT,
    achievements_count INT,
    rank_position INT,
    is_visible TINYINT
)

weekly_scores (
    id INT PRIMARY KEY,
    user_id INT,
    cycle_id INT,
    week_number INT,
    total_checkboxes INT,
    completed_checkboxes INT,
    score_percentage DECIMAL,
    calculated_at TIMESTAMP
)
```

---

## File Structure

```
/home/user/12weeksaas/
├── cls/                          # PHP Classes (35 files)
│   ├── TwelveWeekGoals.php      # Goals CRUD with encryption
│   ├── TwelveWeekTasks.php      # Tasks CRUD with encryption
│   ├── Gamification.php         # Gamification logic
│   ├── User.php                 # Authentication & authorization
│   ├── Member.php               # Member profile management
│   ├── Encryption.php           # AES-256 encryption
│   ├── PDOConn.php             # Database connection
│   └── [other classes...]
│
├── templates/                    # Smarty-like templates (90+ files)
│   ├── main-template.tpl        # Base layout
│   ├── navbar.tpl              # Navigation
│   ├── sidebar.tpl             # Left menu
│   ├── 12-week-goals.tpl       # Goals page
│   ├── 12-week-weekly.tpl      # Weekly tracking
│   └── [other templates...]
│
├── custom-js/                   # Custom JavaScript (24 files)
│   ├── week-goals-12.js        # Goals management
│   ├── 12-week-manage-cycles.js # Cycle management
│   └── [other JS files...]
│
├── includes/                    # Helper functions
│   ├── general-func.php        # Utility functions
│   ├── script-provider.php     # JS/CSS loading
│   └── sess-init.php           # Session handling
│
├── Page Files (78 PHP files)
│   ├── login.php               # Authentication
│   ├── index.php               # Dashboard
│   ├── 12-week-goals.php       # Goals management
│   ├── 12-week-plan-tasks.php  # Task planning
│   ├── 12-week-weekly.php      # Weekly tracking
│   ├── 12-week-progress.php    # Progress analytics
│   ├── 12-week-manage-cycles.php # Cycle management (ADMIN)
│   ├── 12-week-manage-categories.php # Categories (ADMIN)
│   ├── mem-regs.php            # Member registration (ADMIN)
│   └── [other pages...]
│
└── config-sample.php            # Configuration template
```

---

## User Workflows

### Current User Journey (Blocked)

1. **Sign Up**
   - Fill registration form
   - ❌ Wait for admin approval
   - Receive welcome email with password

2. **First Login**
   - Login with credentials
   - See empty dashboard
   - ❌ Error: "No active cycle found. Please contact your administrator."
   - **BLOCKED** - Cannot proceed

3. **If Admin Creates Cycle & Categories**
   - Navigate to "My Goals"
   - Select category from global list
   - ✅ Create goal
   - ✅ Add tasks
   - ✅ Track weekly
   - ✅ View progress

### Expected User Journey (Self-Service)

1. **Sign Up**
   - Fill registration form
   - ✅ Email verification (optional)
   - ✅ Set password
   - ✅ Immediate access

2. **Onboarding Wizard**
   - Welcome screen
   - ✅ Create first 12-week cycle
   - ✅ Set up categories (or use defaults)
   - ✅ Create first goal
   - ✅ Add tasks
   - Tutorial/Help available

3. **Regular Use**
   - ✅ Create/manage cycles
   - ✅ Customize categories
   - ✅ Set goals
   - ✅ Plan tasks
   - ✅ Track daily
   - ✅ View progress
   - ✅ Earn achievements

---

## Recommendations Summary

### Phase 1: Critical Fixes (Required for Self-Service)

1. **Make Cycles User-Specific**
   - Add `user_id` to cycles table
   - Remove admin-only restriction
   - Modify all cycle queries to filter by user_id
   - Update UI to show only user's cycles

2. **Make Categories User-Specific**
   - Add `user_id` to categories table
   - Remove admin-only restriction
   - Modify all category queries to filter by user_id
   - Pre-seed default categories for new users

3. **Enable Direct Registration**
   - Create self-service registration flow
   - Auto-create user account on registration
   - Send verification email
   - Remove mandatory admin approval

### Phase 2: User Experience Improvements

4. **Add Onboarding Wizard**
   - Detect first-time users
   - Guide through initial setup
   - Provide default templates
   - Help documentation

5. **Improve Error Messages**
   - Change "contact administrator" to actionable guidance
   - Add "Create your first cycle" button
   - Contextual help

6. **Add User Dashboard**
   - Quick stats overview
   - Recent activity
   - Quick actions
   - Motivational elements

### Phase 3: Optional Enhancements

7. **Template System**
   - Admin-created category templates
   - Shareable cycle templates
   - Community templates

8. **Advanced Features**
   - Cycle archiving
   - Category import/export
   - Data backup
   - Mobile app

---

## Security Considerations

### Current Security Strengths ✅

1. **Authentication**
   - Strong password hashing (bcrypt)
   - CSRF protection on all forms
   - Session fixation prevention
   - XSS headers
   - HTTPOnly & SameSite cookies

2. **Data Protection**
   - Goals and tasks encrypted at rest (AES-256)
   - SQL injection prevention (prepared statements)
   - Row-level security (user_id checks)

3. **Access Control**
   - Role-based permissions
   - Menu-level access control
   - Operation-level permissions (VIEW, ADD, EDIT, DELETE)

### Security Concerns for Multi-Tenancy ⚠️

When making cycles and categories user-specific:

1. **Data Isolation**
   - Ensure all queries include `WHERE user_id = :user_id`
   - Prevent cross-user data access
   - Test authorization thoroughly

2. **Encryption Keys**
   - Consider per-user encryption keys
   - Secure key storage
   - Key rotation strategy

3. **Performance**
   - Index user_id columns
   - Optimize queries for multi-user
   - Consider data partitioning for scale

---

## Testing Checklist

Before deploying changes:

### Functionality Tests
- [ ] User can register without admin approval
- [ ] User can create their first cycle
- [ ] User can create custom categories
- [ ] User can create goals
- [ ] User can add tasks
- [ ] User can track daily completion
- [ ] User can view progress
- [ ] User sees only their own data

### Security Tests
- [ ] User A cannot access User B's cycles
- [ ] User A cannot access User B's categories
- [ ] User A cannot access User B's goals
- [ ] User A cannot access User B's tasks
- [ ] Admin can still view all users' data (if needed)
- [ ] SQL injection attempts fail
- [ ] XSS attempts blocked
- [ ] CSRF protection working

### Edge Cases
- [ ] New user with no cycles
- [ ] New user with no categories
- [ ] Deleting active cycle
- [ ] Deleting category with goals
- [ ] Multiple active cycles
- [ ] Date calculations across timezones

---

## Conclusion

The 12-week year system has a **solid technical foundation** with good security practices, encryption, and a clean architecture. The code quality is generally high with proper separation of concerns.

However, it currently **does NOT support individual self-service use** due to three critical dependencies:

1. ❌ Admin must create cycles
2. ❌ Admin must create categories
3. ❌ Admin must approve registrations

**To make this a true self-service SaaS:**
- Add `user_id` to cycles and categories tables
- Remove admin-only restrictions on these modules
- Enable direct user registration
- Add onboarding wizard for new users

**Estimated Effort:** 2-3 days development + testing

The existing goals, tasks, weekly tracking, and progress modules are already fully self-service and work well. With the recommended changes, users will be able to operate the entire system independently.
