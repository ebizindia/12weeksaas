# Phase 2 Overview: Self-Service Registration

**Branch:** `claude/phase2-self-service-signup-011CUuwb1pzKWQpYxBBX1g1y`
**Status:** 📋 **Planning Complete - Awaiting Approval**

---

## What's New in Phase 2?

Phase 2 transforms your 12-Week Year system from **manual user creation** to **fully automated self-service registration**.

### Key Features

✅ **Public Signup**
- Users can create accounts themselves
- No admin intervention needed
- Email verification required for security

✅ **Email Verification**
- Verification email sent on signup
- 24-hour token expiration
- Account activated after verification

✅ **Password Reset**
- "Forgot Password" self-service flow
- Secure 1-hour reset tokens
- Email notifications

✅ **Email System**
- Professional email templates
- SMTP configuration
- Delivery tracking

✅ **User Onboarding**
- Welcome wizard for new users
- Profile completion guide
- Interactive tutorial

✅ **Security Enhancements**
- Rate limiting (prevent spam)
- reCAPTCHA v3 (bot protection)
- Secure token generation

---

## Phase 1 vs Phase 2

| Feature | Phase 1 | Phase 2 |
|---------|---------|---------|
| **User Creation** | Manual (admin tool) | Self-service signup |
| **Email Verification** | None | Required |
| **Password Reset** | Admin resets | Self-service |
| **Email System** | Not implemented | Fully automated |
| **Onboarding** | None | Welcome wizard |
| **Bot Protection** | None | reCAPTCHA v3 |

---

## What You'll Get

### 23 New PHP Files

**User-Facing Pages:**
- `signup.php` - Registration form
- `verify-email.php` - Email verification handler
- `resend-verification.php` - Resend verification
- `forgot-password.php` - Request password reset
- `reset-password.php` - Complete password reset
- `onboarding-wizard.php` - Welcome wizard
- `check-email.php` - "Check your email" page

**Core Classes:**
- `cls/EmailService.php` - Email sending
- `cls/RateLimiter.php` - Spam prevention
- `cls/CaptchaService.php` - reCAPTCHA integration
- `cls/TokenGenerator.php` - Secure tokens
- `cls/EmailVerification.php` - Verification logic
- `cls/PasswordReset.php` - Reset logic

### 11 Email Templates

- Verification email
- Welcome email
- Password reset email
- Password changed notification

### Database Changes

**4 New Tables:**
- `email_verifications` - Track verification tokens
- `password_resets` - Track reset tokens
- `email_log` - Email delivery tracking
- `rate_limits` - Spam prevention

**7 New Columns in `users`:**
- `email_verified_at` - Verification timestamp
- `last_login_at` - Last login tracking
- `last_login_ip` - Security monitoring
- `signup_ip` - Fraud detection
- `signup_source` - Analytics (web/admin/api)
- `terms_accepted_at` - Legal compliance
- `onboarding_completed` - UX tracking

---

## Implementation Timeline

### Week 1: Core Features
- **Phase 2A:** Signup + Email Verification
- **Phase 2B:** Password Reset Flow

### Week 2: Security & UX
- **Phase 2C:** Rate Limiting + reCAPTCHA
- **Phase 2D:** Onboarding Wizard

### Week 3: Testing & Polish
- **Phase 2E:** 30 Test Cases + Bug Fixes

**Total Estimated Time:** 3 weeks

---

## Security Features

✅ **Rate Limiting**
- Signup: 3 attempts per IP per hour
- Login: 5 attempts per IP per 15 minutes
- Password Reset: 3 attempts per email per hour

✅ **Token Security**
- Cryptographically secure (64-char hex)
- One-time use only
- Time-limited expiration
- Verification: 24 hours
- Password reset: 1 hour

✅ **Email Security**
- SPF/DKIM/DMARC support
- Disposable email blocking (optional)
- Email verification required

✅ **Bot Protection**
- reCAPTCHA v3 (invisible)
- Score-based detection (threshold: 0.5)
- Applied to signup and critical actions

---

## User Journeys

### New User Signup Flow

```
1. Visit signup.php
2. Fill form (email, password, accept terms)
3. Submit → Account created (inactive)
4. "Check your email" page
5. Receive verification email
6. Click verification link
7. Email verified → Account activated
8. Welcome email sent
9. Login automatically
10. Onboarding wizard shown
11. Complete profile & preferences
12. Create first 12-week cycle
13. Start planning!
```

### Forgot Password Flow

```
1. Login page → "Forgot password?"
2. Enter email
3. "Email sent" message
4. Receive reset email
5. Click reset link
6. Enter new password
7. Password updated
8. Auto logged in
9. Confirmation email sent
10. Back to dashboard
```

---

## Configuration Required

You'll need to provide:

**1. SMTP Email Settings**
```php
SMTP Host: smtp.example.com
SMTP Port: 587
SMTP Username: noreply@yourdomain.com
SMTP Password: ****************
```

**2. reCAPTCHA Keys (Optional)**
```
Site Key: (from Google)
Secret Key: (from Google)
```

**3. Application URL**
```
Base URL: https://yourdomain.com
(Used in email links)
```

**4. Terms of Service**
- Do you have TOS and Privacy Policy pages?
- URLs for these pages

---

## Questions Before We Start

Please answer these questions so I can customize the implementation:

### 1. Email Provider
**Question:** What SMTP service will you use?
- [ ] Gmail / Google Workspace
- [ ] SendGrid
- [ ] Mailgun
- [ ] Amazon SES
- [ ] Other (please specify)

**Status:** Waiting for answer

### 2. reCAPTCHA
**Question:** Do you want Google reCAPTCHA v3 for bot protection?
- [ ] Yes, enable reCAPTCHA (I'll get keys from Google)
- [ ] No, skip for now (can add later)

**Status:** Waiting for answer

### 3. Terms of Service
**Question:** Do you have Terms of Service and Privacy Policy pages?
- [ ] Yes, already created (provide URLs)
- [ ] No, need to create them first
- [ ] Skip for now (can add later)

**Status:** Waiting for answer

### 4. Onboarding Wizard
**Question:** Do you want the welcome wizard for new users?
- [ ] Yes, implement full onboarding (recommended)
- [ ] No, skip to dashboard directly
- [ ] Simple version only (profile + create cycle)

**Status:** Waiting for answer

### 5. Email Branding
**Question:** Do you want custom-designed email templates?
- [ ] Yes, I'll provide brand colors and logo
- [ ] No, use simple text emails
- [ ] Use default templates for now, customize later

**Status:** Waiting for answer

---

## Testing Plan

### 30 New Test Cases

**Signup Flow (8 tests)**
- Valid signup creates account
- Email uniqueness enforced
- Password strength validated
- Terms acceptance required
- CAPTCHA validation
- Rate limiting works
- SQL injection blocked
- XSS prevented

**Email Verification (6 tests)**
- Verification email sent
- Valid token activates account
- Expired token rejected
- Invalid token rejected
- Already verified handled
- Resend works

**Password Reset (8 tests)**
- Reset email sent
- No info leak for invalid emails
- Reset token works
- Expired token rejected
- Used token blocked
- Password updated
- Confirmation email sent
- Rate limiting works

**Onboarding (4 tests)**
- Wizard shown on first login
- Profile completion saves
- Privacy settings saved
- Onboarding marked complete

**Email System (4 tests)**
- Emails logged correctly
- SMTP works
- Templates render
- Delivery confirmed

---

## What Phase 2 Does NOT Include

These are planned for future phases:

❌ **Billing & Subscriptions** (Phase 3)
- Stripe integration
- Free/Pro/Premium tiers
- Trial management

❌ **Advanced Features** (Phase 4)
- Multi-factor authentication
- Social login (Google, Facebook)
- API access
- Mobile apps

---

## Rollback Plan

If Phase 2 has issues, you can:

**Option 1: Disable Features**
```php
// Turn off signup temporarily
define('CONST_ENABLE_SIGNUP', false);
```

**Option 2: Revert Database**
```sql
-- Drop Phase 2 tables
-- Remove Phase 2 columns
```

**Option 3: Switch Branches**
```bash
git checkout claude/convert-to-saas-individual-011CUuwb1pzKWQpYxBBX1g1y
# Back to Phase 1
```

---

## Success Criteria

Phase 2 succeeds when:

✅ Users can signup independently (no admin needed)
✅ Email verification works (>95% delivery)
✅ Password reset works reliably
✅ No spam signups (bots blocked)
✅ No brute force attacks succeed
✅ Onboarding guides new users
✅ All 30 tests pass
✅ No security vulnerabilities
✅ Performance acceptable (<3 sec)

---

## Next Steps

### Step 1: Review Plan (You)
Read the complete plan: **docs/PHASE2-PLAN.md**

### Step 2: Answer Questions (You)
Provide answers to the 5 questions above

### Step 3: Approval (You)
Approve the plan or request changes

### Step 4: Implementation (Me)
Once approved, I'll implement Phase 2A-2E

### Step 5: Testing (Together)
Execute 30 test cases and verify

### Step 6: Deploy (You)
Deploy to production

---

## Documentation

**Main Plan:** `docs/PHASE2-PLAN.md` (comprehensive, 1,100 lines)
**This Overview:** `PHASE2-OVERVIEW.md` (quick summary)

**After Implementation:**
- `docs/PHASE2-IMPLEMENTATION-GUIDE.md`
- `docs/PHASE2-TESTING-GUIDE.md`
- `docs/EMAIL-TEMPLATES-GUIDE.md`

---

## Current Status

✅ **Phase 1:** Complete and deployed
✅ **Phase 2 Branch:** Created
✅ **Phase 2 Plan:** Complete and documented
⏳ **Phase 2 Implementation:** Awaiting your approval

**Branch:** `claude/phase2-self-service-signup-011CUuwb1pzKWQpYxBBX1g1y`

**Git Status:** Clean, all committed and pushed

---

## Your Decision

Please review and let me know:

1. **Approve:** "Please proceed with Phase 2" → I'll start implementation
2. **Modify:** "Change X, Y, Z" → I'll update the plan
3. **Delay:** "Wait, let's deploy Phase 1 first" → We'll pause Phase 2

---

**Ready to proceed when you are!** 🚀

---

*Phase 2 Plan Created: 2025-11-08*
*Status: Awaiting Approval*
*Estimated Timeline: 3 weeks*
*Branch: claude/phase2-self-service-signup-011CUuwb1pzKWQpYxBBX1g1y*
