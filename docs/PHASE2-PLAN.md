# Phase 2: Self-Service Registration - Implementation Plan

**Date:** 2025-11-08
**Branch:** `claude/phase2-self-service-signup-011CUuwb1pzKWQpYxBBX1g1y`
**Status:** 📋 Planning (Awaiting Approval)

---

## Executive Summary

Phase 2 transforms the 12-Week Year system from **manual user creation** (Phase 1) to **fully self-service registration** where users can sign up, verify their email, and reset passwords independently.

### What Phase 2 Adds

✅ **Self-Service Signup**
- Public registration page with validation
- Automatic account creation
- Email uniqueness verification
- Password strength enforcement

✅ **Email Verification**
- Verification emails sent on signup
- Token-based email confirmation
- Account activation after verification
- Resend verification option

✅ **Password Reset**
- "Forgot Password" flow
- Secure token-based reset
- Time-limited reset links
- Email notifications

✅ **Email Infrastructure**
- SMTP email sending
- Professional email templates
- Welcome emails
- Transactional email tracking

✅ **User Onboarding**
- Welcome wizard for new users
- Profile completion guide
- First cycle creation tutorial
- Help system

### What Phase 2 Does NOT Include

❌ **Billing/Subscriptions** - Planned for Phase 3
❌ **Trial Management** - Planned for Phase 3
❌ **Usage Limits** - Planned for Phase 3
❌ **Multi-factor Authentication** - Planned for Phase 4
❌ **Social Login** - Planned for Phase 4

---

## Phase 1 → Phase 2 Transition

### Current State (Phase 1)

```
User Creation: Manual via admin-add-user.php
Email Sending: Not implemented
Password Reset: Manual via admin-reset-password.php
Account Activation: Automatic (no verification)
Onboarding: None (users start immediately)
```

### Future State (Phase 2)

```
User Creation: Self-service signup page
Email Sending: Automated SMTP system
Password Reset: Self-service forgot password flow
Account Activation: Email verification required
Onboarding: Welcome wizard + tutorials
```

---

## Database Schema Changes

### New Tables

#### 1. email_verifications

```sql
CREATE TABLE IF NOT EXISTS `email_verifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `verified_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `ip_address` VARCHAR(45) NULL,

    UNIQUE KEY `unique_token` (`token`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_email` (`email`),
    KEY `idx_expires` (`expires_at`),

    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose:** Track email verification tokens and status
**Expiration:** 24 hours
**Security:** One-time use tokens, cryptographically secure

#### 2. password_resets

```sql
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `ip_address` VARCHAR(45) NULL,

    UNIQUE KEY `unique_token` (`token`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_email` (`email`),
    KEY `idx_expires` (`expires_at`),

    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose:** Track password reset tokens
**Expiration:** 1 hour
**Security:** One-time use, token invalidated after use

#### 3. email_log

```sql
CREATE TABLE IF NOT EXISTS `email_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `to_email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `template` VARCHAR(100) NOT NULL,
    `status` ENUM('queued', 'sent', 'failed', 'bounced') DEFAULT 'queued',
    `sent_at` DATETIME NULL,
    `error_message` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,

    KEY `idx_user_id` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_template` (`template`),
    KEY `idx_created` (`created_at`),

    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose:** Track all emails sent (for debugging and compliance)
**Retention:** Useful for troubleshooting delivery issues

#### 4. rate_limits

```sql
CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `identifier` VARCHAR(255) NOT NULL,
    `action` VARCHAR(50) NOT NULL,
    `attempts` INT DEFAULT 1,
    `window_start` DATETIME NOT NULL,
    `blocked_until` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `unique_identifier_action` (`identifier`, `action`),
    KEY `idx_blocked_until` (`blocked_until`),
    KEY `idx_window_start` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose:** Prevent brute force and spam
**Limits:**
- Signup: 3 attempts per IP per hour
- Login: 5 attempts per IP per 15 minutes
- Password Reset: 3 attempts per email per hour

### Modified Tables

#### users table - New columns

```sql
ALTER TABLE `users`
ADD COLUMN `email_verified_at` DATETIME NULL AFTER `email`,
ADD COLUMN `last_login_at` DATETIME NULL,
ADD COLUMN `last_login_ip` VARCHAR(45) NULL,
ADD COLUMN `signup_ip` VARCHAR(45) NULL,
ADD COLUMN `signup_source` VARCHAR(50) DEFAULT 'web' COMMENT 'web, admin, api',
ADD COLUMN `terms_accepted_at` DATETIME NULL,
ADD COLUMN `onboarding_completed` BOOLEAN DEFAULT 0,
ADD KEY `idx_email_verified` (`email_verified_at`),
ADD KEY `idx_last_login` (`last_login_at`);
```

**New Columns:**
- `email_verified_at`: When user verified their email (NULL = not verified)
- `last_login_at`: Last successful login timestamp
- `last_login_ip`: IP address of last login
- `signup_ip`: IP address used during signup
- `signup_source`: How account was created (web/admin/api)
- `terms_accepted_at`: When user accepted Terms of Service
- `onboarding_completed`: Whether user finished welcome wizard

---

## Feature Implementation

### 1. Self-Service Signup

#### File: `signup.php`

**Features:**
- Public registration form (email, password, confirm password, terms acceptance)
- Real-time password strength indicator
- Email format validation
- Duplicate email checking (AJAX)
- Password requirements display (8+ chars, uppercase, lowercase, number)
- reCAPTCHA v3 integration (spam prevention)
- Terms of Service checkbox (required)
- Privacy Policy link

**Flow:**
```
1. User visits signup.php
2. Fills out form (email, password, confirm password)
3. Accepts Terms of Service
4. Clicks "Sign Up"
5. Backend validates:
   - Email format and uniqueness
   - Password strength
   - CAPTCHA score > 0.5
   - Terms acceptance
6. Creates user account (status: inactive, email_verified_at: NULL)
7. Generates verification token
8. Sends verification email
9. Shows "Check your email" page
10. User clicks link in email
11. Email verified, account activated
12. Redirects to login with success message
```

**Security:**
- CSRF protection
- Rate limiting (3 signups per IP per hour)
- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- XSS prevention (input sanitization)
- reCAPTCHA v3 (bot detection)

**Template:** `templates/signup.tpl`

#### File: `verify-email.php`

**Purpose:** Handle email verification link clicks

**Flow:**
```
1. User clicks link: verify-email.php?token=abc123
2. System validates token:
   - Token exists
   - Token not expired (<24 hours old)
   - Token not already used
3. Marks email as verified:
   - Sets users.email_verified_at = NOW()
   - Sets email_verifications.verified_at = NOW()
4. Activates account:
   - Sets users.account_status = 'active'
5. Logs user in automatically (optional)
6. Redirects to onboarding wizard or dashboard
```

**Error Handling:**
- Invalid token → "Invalid verification link"
- Expired token → "Verification link expired" + Resend option
- Already verified → "Email already verified" + Login link

#### File: `resend-verification.php`

**Purpose:** Resend verification email

**Flow:**
```
1. User enters email address
2. System checks:
   - Email exists in users table
   - Email not yet verified
3. Generates new token
4. Invalidates old tokens
5. Sends new verification email
6. Shows "Email sent" message
```

**Rate Limiting:** 3 resends per email per hour

---

### 2. Password Reset Flow

#### File: `forgot-password.php`

**Purpose:** Request password reset

**Flow:**
```
1. User visits forgot-password.php
2. Enters email address
3. Clicks "Reset Password"
4. System:
   - Checks if email exists (don't reveal if not)
   - Generates reset token
   - Sends reset email
   - Always shows "If email exists, reset link sent"
5. User clicks link in email: reset-password.php?token=xyz789
6. User enters new password
7. Password updated, user logged in
```

**Security:**
- Token expires in 1 hour
- One-time use (invalidated after use)
- Rate limiting (3 requests per email per hour)
- Don't reveal if email exists (prevents enumeration)

#### File: `reset-password.php`

**Purpose:** Complete password reset

**Flow:**
```
1. User clicks link with token
2. System validates token:
   - Exists and not used
   - Not expired (<1 hour old)
3. Shows password reset form
4. User enters new password (twice)
5. System:
   - Validates password strength
   - Hashes password
   - Updates users.password
   - Marks token as used
   - Logs user in
   - Redirects to dashboard
```

**Template:** `templates/reset-password.tpl`

---

### 3. Email System

#### File: `cls/EmailService.php`

**Purpose:** Centralized email sending service

**Methods:**
```php
class EmailService {
    // Send verification email
    public static function sendVerificationEmail($user_id, $email, $token)

    // Send welcome email (after verification)
    public static function sendWelcomeEmail($user_id, $email, $name)

    // Send password reset email
    public static function sendPasswordResetEmail($email, $token)

    // Send password changed notification
    public static function sendPasswordChangedEmail($email, $name)

    // Generic send method
    private static function send($to, $subject, $template, $data)

    // Log email to database
    private static function logEmail($user_id, $to, $subject, $template, $status)
}
```

**Configuration (config.php):**
```php
define('CONST_SMTP_HOST', 'smtp.example.com');
define('CONST_SMTP_PORT', 587);
define('CONST_SMTP_USERNAME', 'noreply@yourdomain.com');
define('CONST_SMTP_PASSWORD', 'your-smtp-password');
define('CONST_SMTP_ENCRYPTION', 'tls'); // tls or ssl
define('CONST_SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
define('CONST_SMTP_FROM_NAME', '12-Week Year');
```

**Library:** PHPMailer (already widely used, or use PHP's mail() with proper headers)

#### Email Templates

**templates/emails/verification.html**
```html
Subject: Verify your email address

Hi {{name}},

Welcome to 12-Week Year! Please verify your email address to activate your account.

Click here to verify: {{verification_link}}

This link will expire in 24 hours.

If you didn't sign up for an account, you can safely ignore this email.

Best regards,
12-Week Year Team
```

**templates/emails/welcome.html**
```html
Subject: Welcome to 12-Week Year!

Hi {{name}},

Your email has been verified and your account is now active!

Ready to get started? Log in to create your first 12-week cycle:
{{login_link}}

Need help? Check out our getting started guide:
{{help_link}}

Best regards,
12-Week Year Team
```

**templates/emails/password-reset.html**
```html
Subject: Reset your password

Hi {{name}},

We received a request to reset your password. Click the link below to create a new password:

{{reset_link}}

This link will expire in 1 hour.

If you didn't request a password reset, you can safely ignore this email.

Best regards,
12-Week Year Team
```

**templates/emails/password-changed.html**
```html
Subject: Your password was changed

Hi {{name}},

This is a confirmation that your password was successfully changed.

If you didn't make this change, please contact us immediately.

Best regards,
12-Week Year Team
```

---

### 4. User Onboarding

#### File: `onboarding-wizard.php`

**Purpose:** Welcome new users and guide them through setup

**Steps:**

**Step 1: Welcome**
```
- Welcome message
- Brief overview of 12-Week Year
- "Let's get started" button
```

**Step 2: Complete Profile**
```
- First Name, Last Name (pre-filled from signup)
- Timezone selection
- Date format preference
- Avatar upload (optional)
```

**Step 3: Privacy Settings**
```
- Leaderboard visibility (opt-in)
- Display name for leaderboard
- Email preferences
```

**Step 4: Create First Cycle**
```
- Explain what a 12-week cycle is
- Button: "Create My First Cycle"
- Redirects to cycle creation page
```

**Step 5: Quick Tutorial**
```
- Interactive overlay tutorial
- Shows: Dashboard, Goals, Tasks, Progress
- "Got it!" dismisses tutorial
```

**Database:**
- Mark `users.onboarding_completed = 1` when finished
- Skip wizard on subsequent logins

**Template:** `templates/onboarding-wizard.tpl`

---

### 5. Rate Limiting & Security

#### File: `cls/RateLimiter.php`

**Purpose:** Prevent brute force and spam

**Methods:**
```php
class RateLimiter {
    // Check if action is allowed
    public static function check($identifier, $action, $max_attempts, $window_minutes)

    // Record attempt
    public static function recordAttempt($identifier, $action)

    // Block identifier for specific time
    public static function block($identifier, $action, $minutes)

    // Clear attempts (on success)
    public static function clear($identifier, $action)

    // Get remaining attempts
    public static function getRemainingAttempts($identifier, $action, $max_attempts)
}
```

**Usage Examples:**
```php
// Signup rate limiting
RateLimiter::check($_SERVER['REMOTE_ADDR'], 'signup', 3, 60); // 3 per hour

// Login rate limiting
RateLimiter::check($_SERVER['REMOTE_ADDR'], 'login', 5, 15); // 5 per 15 min

// Password reset rate limiting
RateLimiter::check($email, 'password_reset', 3, 60); // 3 per hour

// Email verification resend
RateLimiter::check($email, 'resend_verification', 3, 60); // 3 per hour
```

#### File: `cls/CaptchaService.php`

**Purpose:** Google reCAPTCHA v3 integration

**Methods:**
```php
class CaptchaService {
    // Verify reCAPTCHA token
    public static function verify($token, $action)

    // Get score (0.0 to 1.0)
    // 0.0 = bot, 1.0 = human
    // Threshold: 0.5 (configurable)
}
```

**Configuration:**
```php
define('CONST_RECAPTCHA_SITE_KEY', 'your-site-key');
define('CONST_RECAPTCHA_SECRET_KEY', 'your-secret-key');
define('CONST_RECAPTCHA_ENABLED', true);
define('CONST_RECAPTCHA_THRESHOLD', 0.5);
```

---

## Updated User Journey

### New User Registration Flow

```
1. Visit homepage / signup.php
   ↓
2. Fill signup form (email, password, terms)
   ↓
3. Submit form → Account created (inactive)
   ↓
4. "Check your email" page shown
   ↓
5. User receives verification email
   ↓
6. Click verification link
   ↓
7. Email verified → Account activated
   ↓
8. Welcome email sent
   ↓
9. Redirected to login or auto-logged in
   ↓
10. First login → Onboarding wizard shown
    ↓
11. Complete profile, privacy settings
    ↓
12. Create first 12-week cycle
    ↓
13. Dashboard with tutorial overlay
    ↓
14. User starts planning!
```

### Forgot Password Flow

```
1. Visit login page → "Forgot password?" link
   ↓
2. Enter email address
   ↓
3. Submit → "Email sent" message (always)
   ↓
4. User receives reset email (if account exists)
   ↓
5. Click reset link
   ↓
6. Enter new password (twice)
   ↓
7. Password updated → Auto logged in
   ↓
8. Confirmation email sent
   ↓
9. Redirected to dashboard
```

---

## Configuration Changes

### config.php additions

```php
// ===================================================================
// Phase 2: Self-Service Registration Configuration
// ===================================================================

// Registration
define('CONST_ENABLE_SIGNUP', true);              // Enable public signup
define('CONST_REQUIRE_EMAIL_VERIFICATION', true); // Require email verification
define('CONST_REQUIRE_TERMS_ACCEPTANCE', true);   // Require TOS acceptance
define('CONST_MIN_PASSWORD_LENGTH', 8);           // Already in Phase 1

// Email Configuration
define('CONST_SMTP_HOST', 'smtp.example.com');
define('CONST_SMTP_PORT', 587);
define('CONST_SMTP_USERNAME', 'noreply@yourdomain.com');
define('CONST_SMTP_PASSWORD', '');                // Set via environment variable
define('CONST_SMTP_ENCRYPTION', 'tls');
define('CONST_SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
define('CONST_SMTP_FROM_NAME', '12-Week Year');

// Token Expiration
define('CONST_EMAIL_VERIFICATION_EXPIRY', 24);    // Hours
define('CONST_PASSWORD_RESET_EXPIRY', 1);         // Hours

// Rate Limiting
define('CONST_SIGNUP_MAX_ATTEMPTS', 3);           // Per IP per hour
define('CONST_LOGIN_MAX_ATTEMPTS', 5);            // Per IP per 15 min
define('CONST_PASSWORD_RESET_MAX_ATTEMPTS', 3);   // Per email per hour

// reCAPTCHA
define('CONST_RECAPTCHA_ENABLED', true);
define('CONST_RECAPTCHA_SITE_KEY', '');           // Your site key
define('CONST_RECAPTCHA_SECRET_KEY', '');         // Your secret key
define('CONST_RECAPTCHA_THRESHOLD', 0.5);         // Score threshold

// Onboarding
define('CONST_ENABLE_ONBOARDING_WIZARD', true);
define('CONST_ONBOARDING_SKIP_ALLOWED', false);   // Force completion

// URLs
define('CONST_APP_URL', 'https://yourdomain.com'); // Base URL for emails
```

---

## Files to Create

### PHP Files (12 new files)

```
signup.php                          - Registration form
verify-email.php                    - Email verification handler
resend-verification.php             - Resend verification email
forgot-password.php                 - Request password reset
reset-password.php                  - Complete password reset
onboarding-wizard.php               - Welcome wizard for new users
check-email.php                     - "Check your email" page

cls/EmailService.php                - Email sending service
cls/RateLimiter.php                 - Rate limiting service
cls/CaptchaService.php              - reCAPTCHA integration
cls/TokenGenerator.php              - Secure token generation
cls/EmailVerification.php           - Email verification logic
cls/PasswordReset.php               - Password reset logic
```

### Template Files (11 new templates)

```
templates/signup.tpl                - Registration form UI
templates/check-email.tpl           - Email sent confirmation
templates/verify-email.tpl          - Email verified success
templates/resend-verification.tpl   - Resend form
templates/forgot-password.tpl       - Forgot password form
templates/reset-password.tpl        - Reset password form
templates/onboarding-wizard.tpl     - Onboarding wizard UI

templates/emails/verification.html  - Email verification email
templates/emails/welcome.html       - Welcome email
templates/emails/password-reset.html - Password reset email
templates/emails/password-changed.html - Password changed notification
```

### Migration Files

```
migrations/phase2-self-service-migration.sql     - Database changes
migrations/run-phase2-migration.php              - Migration runner
```

### Documentation

```
docs/PHASE2-IMPLEMENTATION-GUIDE.md  - Implementation details
docs/PHASE2-TESTING-GUIDE.md         - Testing procedures
docs/EMAIL-TEMPLATES-GUIDE.md        - Email customization
```

---

## Updated Navigation

### Public Pages (Not Logged In)

```
Homepage (index.php or signup.php)
  ↓
Login (login.php)                    [Existing]
Signup (signup.php)                  [NEW]
Forgot Password (forgot-password.php) [NEW]
```

### Login Page Updates

**Current:**
```html
<form action="login.php" method="post">
    <!-- Email and password fields -->
    <button type="submit">Login</button>
</form>
```

**Updated:**
```html
<form action="login.php" method="post">
    <!-- Email and password fields -->
    <button type="submit">Login</button>

    <div class="text-center mt-3">
        <a href="forgot-password.php">Forgot your password?</a>
    </div>

    <div class="text-center mt-3">
        Don't have an account?
        <a href="signup.php"><strong>Sign up</strong></a>
    </div>
</form>
```

---

## Security Considerations

### Email Security

✅ **SPF, DKIM, DMARC**
- Configure DNS records for email authentication
- Prevents emails from going to spam

✅ **Disposable Email Detection**
- Block temporary email services (optional)
- Use blocklist: mailinator.com, guerrillamail.com, etc.

✅ **Email Verification Required**
- Users cannot access app until email verified
- Prevents fake signups

### Token Security

✅ **Cryptographically Secure Tokens**
```php
bin2hex(random_bytes(32)); // 64-character hex token
```

✅ **One-Time Use**
- Tokens invalidated after use
- Prevents replay attacks

✅ **Time-Limited**
- Verification: 24 hours
- Password reset: 1 hour
- Expired tokens rejected

### Rate Limiting

✅ **Prevent Brute Force**
- Login: 5 attempts per 15 minutes
- Password reset: 3 attempts per hour
- Signup: 3 attempts per hour

✅ **Identifier Types**
- IP address (signup, login)
- Email address (password reset, resend verification)

### CAPTCHA

✅ **reCAPTCHA v3**
- Invisible to users (no challenges)
- Returns score 0.0 to 1.0
- Threshold: 0.5 (configurable)
- Applied to: signup, login (optional), password reset

---

## Testing Requirements

### Phase 2 Test Cases (30 new tests)

#### Signup Flow (8 tests)
1. Valid signup creates account
2. Email uniqueness enforced
3. Password strength validated
4. Terms acceptance required
5. CAPTCHA validation works
6. Rate limiting prevents spam
7. SQL injection prevented
8. XSS attacks blocked

#### Email Verification (6 tests)
9. Verification email sent on signup
10. Valid token activates account
11. Expired token rejected
12. Invalid token rejected
13. Already verified token handled
14. Resend verification works

#### Password Reset (8 tests)
15. Reset email sent for valid email
16. No email sent for invalid email (security)
17. Reset token works
18. Expired reset token rejected
19. Used token cannot be reused
20. Password successfully updated
21. Confirmation email sent
22. Rate limiting prevents abuse

#### Onboarding (4 tests)
23. Wizard shown on first login
24. Profile completion works
25. Privacy settings saved
26. Onboarding marked complete

#### Email System (4 tests)
27. Emails logged to database
28. SMTP configuration correct
29. Email templates render correctly
30. Email delivery confirmed

---

## Rollback Plan

If Phase 2 causes issues:

### Option 1: Feature Flag Disable

```php
// config.php
define('CONST_ENABLE_SIGNUP', false); // Disable signup
define('CONST_REQUIRE_EMAIL_VERIFICATION', false); // Disable verification
```

Users created in Phase 2 will still work, but new signups disabled.

### Option 2: Database Rollback

```sql
DROP TABLE IF EXISTS email_verifications;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS email_log;
DROP TABLE IF EXISTS rate_limits;

ALTER TABLE users
DROP COLUMN email_verified_at,
DROP COLUMN last_login_at,
DROP COLUMN last_login_ip,
DROP COLUMN signup_ip,
DROP COLUMN signup_source,
DROP COLUMN terms_accepted_at,
DROP COLUMN onboarding_completed;
```

### Option 3: Git Revert

```bash
git checkout claude/convert-to-saas-individual-011CUuwb1pzKWQpYxBBX1g1y
# Phase 1 restored
```

---

## Dependencies

### PHP Extensions Required

```
- php-mbstring (multibyte string support)
- php-openssl (secure token generation)
- php-curl (reCAPTCHA API calls)
```

### Optional Libraries

**PHPMailer** (Recommended for SMTP)
```bash
composer require phpmailer/phpmailer
```

Or use PHP's built-in `mail()` function with proper headers.

---

## Performance Considerations

### Database Indexing

All new tables have appropriate indexes:
- Token lookups: UNIQUE KEY on token columns
- User lookups: KEY on user_id, email
- Expiration queries: KEY on expires_at
- Status queries: KEY on status fields

### Email Queue (Future)

Phase 2 sends emails synchronously (blocking).

For high volume (Phase 3+), consider:
- Email queue table
- Background job processor (cron)
- Service like SendGrid, Amazon SES

### Token Cleanup

Old tokens should be cleaned up:

```sql
-- Delete expired verification tokens (run daily)
DELETE FROM email_verifications
WHERE expires_at < NOW() - INTERVAL 7 DAY;

-- Delete old password reset tokens (run daily)
DELETE FROM password_resets
WHERE expires_at < NOW() - INTERVAL 7 DAY;

-- Clean up old rate limit records (run hourly)
DELETE FROM rate_limits
WHERE window_start < NOW() - INTERVAL 24 HOUR
  AND blocked_until IS NULL;
```

Create: `cron/cleanup-tokens.php`

---

## Implementation Phases

### Phase 2A: Core Signup (Week 1)
- Database migrations
- Signup page
- Email verification
- Email service setup

### Phase 2B: Password Reset (Week 1)
- Forgot password flow
- Reset password page
- Email templates

### Phase 2C: Security & Polish (Week 2)
- Rate limiting
- reCAPTCHA integration
- Error handling
- UI polish

### Phase 2D: Onboarding (Week 2)
- Welcome wizard
- Tutorial system
- Documentation

### Phase 2E: Testing (Week 3)
- Execute 30 test cases
- Bug fixes
- Performance optimization
- Documentation

**Total Estimated Time:** 3 weeks

---

## Success Criteria

Phase 2 is successful when:

✅ Users can sign up independently
✅ Email verification works (>95% delivery rate)
✅ Password reset works reliably
✅ No spam signups (CAPTCHA blocks bots)
✅ No brute force attacks succeed (rate limiting)
✅ Onboarding wizard guides new users
✅ All 30 test cases pass
✅ No security vulnerabilities
✅ Email delivery is reliable
✅ Performance acceptable (<3 sec page loads)

---

## Next Steps After Phase 2

Once Phase 2 is deployed and stable:

**Phase 3: Subscription & Billing**
- Stripe integration
- Free/Pro/Premium tiers
- 14-day free trial
- Subscription management
- Usage limits and quotas
- Billing portal

**Phase 4: Enhanced Features**
- Advanced analytics
- Goal templates library
- PDF export
- API access
- Zapier integration
- Mobile apps

---

## Questions for Review

Before implementation begins, please confirm:

1. **Email Provider:** Do you have SMTP credentials? (Gmail, SendGrid, Mailgun, etc.)
2. **reCAPTCHA:** Do you want reCAPTCHA v3 or skip for now?
3. **Terms of Service:** Do you have TOS and Privacy Policy pages?
4. **Onboarding:** Do you want the welcome wizard, or skip to Phase 3?
5. **Email Templates:** Do you want custom branded email templates?
6. **Domain:** What domain will be used for email links? (e.g., app.yourdomain.com)

---

## Approval Checklist

Please review and approve:

- [ ] Database schema changes (4 new tables, 7 new columns)
- [ ] New features (signup, email verification, password reset, onboarding)
- [ ] Security measures (rate limiting, CAPTCHA, token expiration)
- [ ] Email system implementation
- [ ] Files to create (23 new PHP files, 11 templates)
- [ ] Configuration requirements
- [ ] Testing plan (30 test cases)
- [ ] Timeline (3 weeks estimated)

---

**Status:** 📋 **Awaiting Your Approval to Begin Implementation**

Once approved, I will proceed with Phase 2A (Core Signup) implementation.

---

*Phase 2 Plan Created: 2025-11-08*
*Branch: claude/phase2-self-service-signup-011CUuwb1pzKWQpYxBBX1g1y*
*Ready to implement upon approval*
