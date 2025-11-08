# Phase 2 Deployment Guide

**Version:** Phase 2A - Self-Service Registration
**Date:** 2025-11-08
**Branch:** `claude/phase2-self-service-signup-011CUuwb1pzKWQpYxBBX1g1y`

---

## 🎯 What's New in Phase 2

Phase 2 adds complete self-service registration capabilities to your 12-Week Year system:

✅ **Self-Service Signup** - Users create accounts independently
✅ **Email Verification** - Secure token-based email confirmation
✅ **Password Reset** - Self-service "Forgot Password" flow
✅ **Email Infrastructure** - PHPMailer with professional templates
✅ **Onboarding Wizard** - Welcome experience for new users
✅ **Bot Protection** - reCAPTCHA v3 integration
✅ **Rate Limiting** - Prevent spam and brute force attacks

---

## 🚀 Quick Start

### 1. Update Configuration

Copy settings from `config-phase2-template.php` to your `config.php`:

```php
// Minimum required settings
define('CONST_SMTP_HOST', 'smtp.gmail.com');
define('CONST_SMTP_USERNAME', 'your-email@gmail.com');
define('CONST_SMTP_PASSWORD', 'your-app-password');
define('CONST_SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
define('CONST_APP_URL', 'https://yourdomain.com');
```

### 2. Run Database Migration

```bash
cd /path/to/12weeksaas
php migrations/run-phase2-migration.php --force
```

### 3. Test Signup

1. Visit `http://yourdomain.com/signup.php`
2. Create a test account
3. Check email for verification link
4. Click link to verify
5. Complete onboarding wizard

---

## 📋 Pre-Deployment Checklist

### Required Configuration

- [ ] SMTP settings configured in `config.php`
- [ ] `CONST_APP_URL` set to your domain
- [ ] Terms of Service customized (`terms-of-service.php`)
- [ ] Privacy Policy customized (`privacy-policy.php`)
- [ ] Database backup completed

### Optional Configuration

- [ ] reCAPTCHA keys obtained and configured
- [ ] Rate limiting values adjusted
- [ ] Email templates customized
- [ ] Onboarding wizard customized

### Testing

- [ ] Signup flow tested end-to-end
- [ ] Email delivery verified
- [ ] Email verification link works
- [ ] Password reset flow tested
- [ ] Onboarding wizard tested
- [ ] Rate limiting verified
- [ ] reCAPTCHA working (if enabled)

---

## 📁 Files Added/Modified

### New PHP Files (7)
```
signup.php                   - Self-service registration
check-email.php             - Post-signup confirmation
verify-email.php            - Email verification handler
resend-verification.php     - Resend verification email
forgot-password.php         - Request password reset
reset-password.php          - Complete password reset
onboarding-wizard.php       - Welcome wizard
```

### New Classes (4)
```
cls/EmailService.php        - Email sending with PHPMailer
cls/TokenGenerator.php      - Secure token generation
cls/RateLimiter.php        - Rate limiting/spam prevention
cls/CaptchaService.php     - reCAPTCHA v3 integration
```

### New Legal Pages (2)
```
terms-of-service.php       - Terms of Service (customize!)
privacy-policy.php         - Privacy Policy (customize!)
```

### Modified Files (2)
```
templates/login.tpl        - Added "Create Account" link
config.php                 - Phase 2 settings (update manually)
```

### Database Changes
```
4 new tables:
  - email_verifications
  - password_resets
  - email_log
  - rate_limits

7 new columns in users:
  - email_verified_at
  - last_login_at
  - last_login_ip
  - signup_ip
  - signup_source
  - terms_accepted_at
  - onboarding_completed
```

---

## ⚙️ Configuration Details

### SMTP Configuration

**Gmail:**
```php
define('CONST_SMTP_HOST', 'smtp.gmail.com');
define('CONST_SMTP_PORT', 587);
define('CONST_SMTP_USERNAME', 'your-email@gmail.com');
define('CONST_SMTP_PASSWORD', 'your-app-password'); // Not your Gmail password!
define('CONST_SMTP_ENCRYPTION', 'tls');
```

**SendGrid:**
```php
define('CONST_SMTP_HOST', 'smtp.sendgrid.net');
define('CONST_SMTP_PORT', 587);
define('CONST_SMTP_USERNAME', 'apikey'); // Literally "apikey"
define('CONST_SMTP_PASSWORD', 'your-sendgrid-api-key');
define('CONST_SMTP_ENCRYPTION', 'tls');
```

**Mailgun:**
```php
define('CONST_SMTP_HOST', 'smtp.mailgun.org');
define('CONST_SMTP_PORT', 587);
define('CONST_SMTP_USERNAME', 'postmaster@your-domain.mailgun.org');
define('CONST_SMTP_PASSWORD', 'your-smtp-password');
define('CONST_SMTP_ENCRYPTION', 'tls');
```

### reCAPTCHA Setup

1. Visit https://www.google.com/recaptcha/admin
2. Register your site (select reCAPTCHA v3)
3. Get site key and secret key
4. Add to config.php:

```php
define('CONST_RECAPTCHA_SITE_KEY', 'your-site-key');
define('CONST_RECAPTCHA_SECRET_KEY', 'your-secret-key');
```

Or disable reCAPTCHA:
```php
define('CONST_RECAPTCHA_ENABLED', false);
```

---

## 🔧 Database Migration

### Migration Process

```bash
# 1. Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# 2. Run migration
php migrations/run-phase2-migration.php --force

# 3. Verify migration
mysql -u username -p database_name -e "SHOW TABLES LIKE 'email_%'"
```

### Migration Details

The migration will:
- Create 4 new tables
- Add 7 columns to users table
- Set existing users as verified (`email_verified_at = created_at`)
- Mark existing users with `signup_source = 'admin'`

### Rollback

If needed, rollback by:
```sql
DROP TABLE email_verifications, password_resets, email_log, rate_limits;

ALTER TABLE users
  DROP COLUMN email_verified_at,
  DROP COLUMN last_login_at,
  DROP COLUMN last_login_ip,
  DROP COLUMN signup_ip,
  DROP COLUMN signup_source,
  DROP COLUMN terms_accepted_at,
  DROP COLUMN onboarding_completed;
```

---

## 🧪 Testing Guide

### Test 1: Signup Flow

1. Visit `/signup.php`
2. Fill out form with test email
3. Submit form
4. Verify redirect to check-email.php
5. Check inbox for verification email
6. Click verification link
7. Verify redirect to login or onboarding
8. Log in with new credentials

**Expected Results:**
- Account created with `status=0`, `account_status='inactive'`
- Verification email sent
- Token stored in `email_verifications` table
- After verification: `email_verified_at` set, `account_status='active'`

### Test 2: Email Verification

**Valid Token:**
```sql
SELECT * FROM email_verifications WHERE token = 'test-token';
```
- Should activate account
- Should set `verified_at` timestamp
- Should send welcome email

**Expired Token:**
- Set `expires_at` to past date
- Click link
- Should show error and resend option

**Already Used Token:**
- Click same link twice
- Should show "already verified" message

### Test 3: Password Reset

1. Visit `/forgot-password.php`
2. Enter registered email
3. Submit form
4. Check inbox for reset email
5. Click reset link
6. Enter new password
7. Submit form
8. Verify password changed
9. Log in with new password

**Expected Results:**
- Reset email sent
- Token stored in `password_resets` table
- After reset: `used_at` timestamp set
- Password hash updated in users table
- Confirmation email sent

### Test 4: Rate Limiting

**Signup Rate Limit:**
- Attempt 4 signups from same IP within 1 hour
- 4th attempt should be blocked
- Error message: "Too many attempts..."

**Password Reset Rate Limit:**
- Request 4 password resets for same email within 1 hour
- 4th attempt should be blocked

### Test 5: reCAPTCHA (if enabled)

1. Open browser console
2. Visit signup page
3. Submit form
4. Check network tab for reCAPTCHA request
5. Verify token sent to server
6. Check server logs for score (should be > 0.5)

### Test 6: Onboarding Wizard

1. Complete signup and verification
2. Log in for first time
3. Should redirect to onboarding wizard
4. Complete all 3 steps
5. Should mark `onboarding_completed = 1`
6. Should redirect to dashboard
7. Log out and log in again
8. Should NOT show wizard again

---

## 🛡️ Security Features

### Implemented Security

✅ **CSRF Protection** - All forms include CSRF tokens
✅ **Rate Limiting** - Prevents brute force and spam
✅ **Email Enumeration Prevention** - Generic messages for invalid emails
✅ **Password Hashing** - bcrypt with unique salts
✅ **Secure Tokens** - Cryptographically random (64 chars)
✅ **Token Expiration** - Time-limited verification/reset links
✅ **One-Time Use Tokens** - Tokens invalidated after use
✅ **IP Logging** - Audit trail for security events
✅ **Input Validation** - All user input sanitized
✅ **SQL Injection Prevention** - Prepared statements
✅ **XSS Prevention** - Output escaping

### Security Best Practices

1. **Use HTTPS in production**
   - Update CONST_APP_URL to https://
   - Configure SSL certificate
   - Force HTTPS redirects

2. **Secure SMTP Credentials**
   - Use environment variables
   - Don't commit passwords to git
   - Rotate credentials regularly

3. **Monitor Rate Limits**
   - Check `rate_limits` table regularly
   - Adjust limits based on usage
   - Block suspicious IPs

4. **Review Audit Logs**
   - Check `audit_logs` for unusual patterns
   - Monitor failed login attempts
   - Track signup sources

---

## 📧 Email Templates

### Email Types

1. **Verification Email**
   - Template: Simple text with button
   - Expiry: 24 hours (configurable)
   - Auto-sends on signup

2. **Welcome Email**
   - Sent after email verification
   - Includes login link
   - Guides to dashboard

3. **Password Reset Email**
   - Expiry: 1 hour (configurable)
   - One-time use link
   - Security notice included

4. **Password Changed Email**
   - Confirmation after reset
   - Security alert
   - Contact info if not authorized

### Customizing Templates

Email templates are in `cls/EmailService.php`:

```php
private static function getDefaultTemplate($template, $data)
{
    switch ($template) {
        case 'verification':
            // Customize HTML here
            return "...";
    }
}
```

For external template files, create:
```
templates/emails/verification.html
templates/emails/welcome.html
templates/emails/password-reset.html
templates/emails/password-changed.html
```

---

## 🐛 Troubleshooting

### Emails Not Sending

**Check SMTP Settings:**
```php
// Test SMTP connection
use eBizIndia\EmailService;
$result = EmailService::sendVerificationEmail(1, 'test@example.com', 'test-token');
print_r($result);
```

**Check Email Logs:**
```sql
SELECT * FROM email_log ORDER BY created_at DESC LIMIT 10;
```

**Common Issues:**
- Wrong SMTP password (use app-specific password for Gmail)
- Port blocked by firewall (try 465 instead of 587)
- From email not verified (some providers require verification)
- SSL/TLS mismatch

### Verification Links Not Working

**Check Token:**
```sql
SELECT * FROM email_verifications WHERE email = 'user@example.com';
```

**Check Expiry:**
- Tokens expire in 24 hours by default
- Check `expires_at` column
- Resend if expired

**Check URL:**
- Verify CONST_APP_URL is correct
- Should match your domain exactly
- Include https:// or http://

### Rate Limiting Too Strict

**Check Current Limits:**
```sql
SELECT * FROM rate_limits WHERE identifier = '192.168.1.1';
```

**Clear Rate Limit:**
```sql
DELETE FROM rate_limits WHERE identifier = '192.168.1.1' AND action = 'signup';
```

**Adjust Limits:**
```php
define('CONST_SIGNUP_MAX_ATTEMPTS', 5); // Increase from 3
```

### Onboarding Not Showing

**Check User Record:**
```sql
SELECT onboarding_completed FROM users WHERE id = 123;
```

**Force Onboarding:**
```sql
UPDATE users SET onboarding_completed = 0 WHERE id = 123;
```

---

## 📊 Monitoring

### Database Queries

**New Signups Today:**
```sql
SELECT COUNT(*) as new_signups
FROM users
WHERE DATE(created_at) = CURDATE()
  AND signup_source = 'web';
```

**Email Verification Rate:**
```sql
SELECT
  COUNT(*) as total_signups,
  SUM(email_verified_at IS NOT NULL) as verified,
  ROUND(SUM(email_verified_at IS NOT NULL) / COUNT(*) * 100, 2) as verification_rate
FROM users
WHERE signup_source = 'web';
```

**Password Reset Requests:**
```sql
SELECT COUNT(*) as reset_requests
FROM password_resets
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY);
```

**Email Delivery Rate:**
```sql
SELECT
  status,
  COUNT(*) as count,
  ROUND(COUNT(*) / (SELECT COUNT(*) FROM email_log) * 100, 2) as percentage
FROM email_log
GROUP BY status;
```

**Rate Limit Blocks:**
```sql
SELECT
  action,
  COUNT(DISTINCT identifier) as blocked_ips
FROM rate_limits
WHERE blocked_until > NOW()
GROUP BY action;
```

### Cleanup Tasks

**Run Periodically (Cron Job):**

```bash
# Clean up old rate limits (run hourly)
mysql -u username -p database_name -e "
  DELETE FROM rate_limits
  WHERE window_start < DATE_SUB(NOW(), INTERVAL 24 HOUR)
    AND blocked_until IS NULL;
"

# Clean up old verification tokens (run daily)
mysql -u username -p database_name -e "
  DELETE FROM email_verifications
  WHERE expires_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
"

# Clean up old reset tokens (run daily)
mysql -u username -p database_name -e "
  DELETE FROM password_resets
  WHERE expires_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
"
```

---

## 🎓 User Guide

### For End Users

**Creating an Account:**
1. Visit the signup page
2. Enter your email, name, and password
3. Accept Terms of Service
4. Click "Create Account"
5. Check your email for verification link
6. Click the link to verify
7. Complete the welcome wizard
8. Start planning!

**Resetting Password:**
1. Click "Forgot Password" on login page
2. Enter your email
3. Check inbox for reset link
4. Click link and enter new password
5. Log in with new password

**Privacy Settings:**
1. Log in to your account
2. Go to Account Settings
3. Click Privacy tab
4. Choose leaderboard visibility
5. Set display name (optional)

---

## 📞 Support

### Common Questions

**Q: How do I disable signup?**
```php
define('CONST_ENABLE_SIGNUP', false);
```

**Q: How do I disable email verification?**
```php
define('CONST_REQUIRE_EMAIL_VERIFICATION', false);
```

**Q: How do I skip onboarding?**
```php
define('CONST_ENABLE_ONBOARDING_WIZARD', false);
```

**Q: How do I change token expiry?**
```php
define('CONST_EMAIL_VERIFICATION_EXPIRY', 48); // 48 hours
define('CONST_PASSWORD_RESET_EXPIRY', 2); // 2 hours
```

### Getting Help

**Documentation:**
- Phase 2 Plan: `docs/PHASE2-PLAN.md`
- Phase 2 Overview: `PHASE2-OVERVIEW.md`
- This Guide: `PHASE2-DEPLOYMENT-GUIDE.md`

**Testing:**
- Create test accounts with temporary email services
- Check email_log table for delivery issues
- Review rate_limits table for blocked IPs

---

## ✅ Post-Deployment Checklist

After deploying Phase 2:

- [ ] Verify signup page is accessible
- [ ] Test complete signup flow
- [ ] Verify emails are delivered
- [ ] Test password reset flow
- [ ] Check onboarding wizard works
- [ ] Verify rate limiting prevents spam
- [ ] Test with multiple browsers/devices
- [ ] Monitor email_log for delivery issues
- [ ] Review rate_limits for blocked IPs
- [ ] Set up cleanup cron jobs
- [ ] Monitor database performance
- [ ] Update documentation for users
- [ ] Train support team
- [ ] Plan Phase 3 (billing/subscriptions)

---

**Phase 2 is ready for deployment!** 🚀

Follow this guide step-by-step to ensure a smooth rollout.

---

*Last Updated: 2025-11-08*
*Version: Phase 2A*
*Branch: claude/phase2-self-service-signup-011CUuwb1pzKWQpYxBBX1g1y*
