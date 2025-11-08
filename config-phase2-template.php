<?php
/**
 * Phase 2 Configuration Template
 *
 * Copy the settings below to your config.php file
 * Update the values according to your environment
 *
 * IMPORTANT: This is a template file. Do NOT use it directly.
 * Add these settings to your actual config.php file.
 */

// ============================================================================
// PHASE 2: SELF-SERVICE REGISTRATION CONFIGURATION
// ============================================================================

// Registration Settings
define('CONST_ENABLE_SIGNUP', true); // Enable public self-service signup
define('CONST_REQUIRE_EMAIL_VERIFICATION', true); // Require email verification before login
define('CONST_REQUIRE_TERMS_ACCEPTANCE', true); // Require Terms of Service acceptance
define('CONST_ENABLE_ONBOARDING_WIZARD', true); // Show welcome wizard for new users
define('CONST_ONBOARDING_SKIP_ALLOWED', false); // Force onboarding completion

// Email Configuration (SMTP)
// IMPORTANT: Set these values according to your email provider
define('CONST_SMTP_HOST', ''); // REQUIRED: e.g., 'smtp.gmail.com' or 'smtp.sendgrid.net'
define('CONST_SMTP_PORT', 587); // 587 for TLS, 465 for SSL
define('CONST_SMTP_USERNAME', ''); // REQUIRED: Your SMTP username
define('CONST_SMTP_PASSWORD', ''); // REQUIRED: Your SMTP password (use environment variable in production)
define('CONST_SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
define('CONST_SMTP_FROM_EMAIL', ''); // REQUIRED: e.g., 'noreply@yourdomain.com'
define('CONST_SMTP_FROM_NAME', '12-Week Year'); // Sender name in emails

// Token Expiration Settings
define('CONST_EMAIL_VERIFICATION_EXPIRY', 24); // Hours until verification link expires
define('CONST_PASSWORD_RESET_EXPIRY', 1); // Hours until password reset link expires

// Rate Limiting (Prevent Spam and Brute Force)
define('CONST_SIGNUP_MAX_ATTEMPTS', 3); // Max signups per IP per hour
define('CONST_LOGIN_MAX_ATTEMPTS', 5); // Max login attempts per IP per 15 minutes
define('CONST_PASSWORD_RESET_MAX_ATTEMPTS', 3); // Max password reset requests per email per hour
define('CONST_RESEND_VERIFICATION_MAX_ATTEMPTS', 3); // Max verification email resends per hour

// Google reCAPTCHA v3 Configuration
// Get your keys from: https://www.google.com/recaptcha/admin
define('CONST_RECAPTCHA_ENABLED', true); // Enable reCAPTCHA bot protection
define('CONST_RECAPTCHA_SITE_KEY', ''); // REQUIRED if reCAPTCHA enabled
define('CONST_RECAPTCHA_SECRET_KEY', ''); // REQUIRED if reCAPTCHA enabled
define('CONST_RECAPTCHA_THRESHOLD', 0.5); // Score threshold (0.0 = bot, 1.0 = human)

// Application URLs (used in emails)
define('CONST_APP_URL', 'http://localhost'); // REQUIRED: Base URL - UPDATE THIS for production!
define('CONST_APP_NAME', '12-Week Year'); // Application name

// Terms and Privacy URLs
define('CONST_TERMS_OF_SERVICE_URL', CONST_APP_URL . '/terms-of-service.php');
define('CONST_PRIVACY_POLICY_URL', CONST_APP_URL . '/privacy-policy.php');

// ============================================================================
// END PHASE 2 CONFIGURATION
// ============================================================================

/*
================================================================================
CONFIGURATION CHECKLIST
================================================================================

REQUIRED SETTINGS (Must be configured):
☐ CONST_SMTP_HOST - Your email provider's SMTP host
☐ CONST_SMTP_USERNAME - SMTP username
☐ CONST_SMTP_PASSWORD - SMTP password
☐ CONST_SMTP_FROM_EMAIL - Sender email address
☐ CONST_APP_URL - Your application's base URL

OPTIONAL SETTINGS (reCAPTCHA):
☐ CONST_RECAPTCHA_SITE_KEY - Get from Google reCAPTCHA admin
☐ CONST_RECAPTCHA_SECRET_KEY - Get from Google reCAPTCHA admin
  (Or set CONST_RECAPTCHA_ENABLED to false to disable)

CUSTOMIZABLE SETTINGS:
☐ CONST_SMTP_FROM_NAME - Sender name (default: "12-Week Year")
☐ CONST_EMAIL_VERIFICATION_EXPIRY - Hours (default: 24)
☐ CONST_PASSWORD_RESET_EXPIRY - Hours (default: 1)
☐ Rate limiting values (adjust based on your needs)

================================================================================
SMTP PROVIDER EXAMPLES
================================================================================

GMAIL:
------
CONST_SMTP_HOST = 'smtp.gmail.com'
CONST_SMTP_PORT = 587
CONST_SMTP_ENCRYPTION = 'tls'
CONST_SMTP_USERNAME = 'your-email@gmail.com'
CONST_SMTP_PASSWORD = 'your-app-password' // Use app-specific password, not account password
CONST_SMTP_FROM_EMAIL = 'your-email@gmail.com'

SENDGRID:
---------
CONST_SMTP_HOST = 'smtp.sendgrid.net'
CONST_SMTP_PORT = 587
CONST_SMTP_ENCRYPTION = 'tls'
CONST_SMTP_USERNAME = 'apikey' // Literally the string "apikey"
CONST_SMTP_PASSWORD = 'your-sendgrid-api-key'
CONST_SMTP_FROM_EMAIL = 'noreply@yourdomain.com'

MAILGUN:
--------
CONST_SMTP_HOST = 'smtp.mailgun.org'
CONST_SMTP_PORT = 587
CONST_SMTP_ENCRYPTION = 'tls'
CONST_SMTP_USERNAME = 'postmaster@your-domain.mailgun.org'
CONST_SMTP_PASSWORD = 'your-mailgun-smtp-password'
CONST_SMTP_FROM_EMAIL = 'noreply@yourdomain.com'

AMAZON SES:
-----------
CONST_SMTP_HOST = 'email-smtp.us-east-1.amazonaws.com' // Region-specific
CONST_SMTP_PORT = 587
CONST_SMTP_ENCRYPTION = 'tls'
CONST_SMTP_USERNAME = 'your-ses-smtp-username'
CONST_SMTP_PASSWORD = 'your-ses-smtp-password'
CONST_SMTP_FROM_EMAIL = 'noreply@yourdomain.com'

================================================================================
SECURITY NOTES
================================================================================

1. NEVER commit SMTP passwords to git
   - Use environment variables in production
   - Example: define('CONST_SMTP_PASSWORD', getenv('SMTP_PASSWORD'));

2. Ensure Terms of Service and Privacy Policy are customized
   - Edit terms-of-service.php
   - Edit privacy-policy.php
   - Add your jurisdiction and contact information

3. Update CONST_APP_URL for production
   - Use HTTPS in production
   - Example: https://yourdomain.com

4. Configure SSL/TLS on your web server
   - Email links contain absolute URLs
   - Users will be redirected to CONST_APP_URL

5. Test email sending before going live
   - Create a test user
   - Verify emails are delivered
   - Check spam folder if not received

================================================================================
NEXT STEPS AFTER CONFIGURATION
================================================================================

1. Update config.php with settings from this template
2. Run database migration: php migrations/run-phase2-migration.php
3. Test signup flow end-to-end
4. Customize Terms of Service and Privacy Policy
5. Test email delivery
6. Test password reset flow
7. Test onboarding wizard
8. Deploy to production

================================================================================
*/

?>
