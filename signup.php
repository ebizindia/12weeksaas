<?php
/**
 * Signup Page
 *
 * Self-service user registration
 */

require_once 'config.php';
require_once CONST_INCLUDES_DIR . '/ebiz-autoload.php';

use eBizIndia\RateLimiter;
use eBizIndia\CaptchaService;
use eBizIndia\TokenGenerator;
use eBizIndia\EmailService;
use eBizIndia\UserPreferences;

// Check if signup is enabled
if (!defined('CONST_ENABLE_SIGNUP') || !CONST_ENABLE_SIGNUP) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$form_data = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // CSRF protection
        session_start();
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            throw new Exception('Invalid request. Please try again.');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $terms_accepted = isset($_POST['terms_accepted']);
        $recaptcha_token = $_POST['recaptcha_token'] ?? '';

        // Store form data for repopulation on error
        $form_data = [
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name
        ];

        // Rate limiting check
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rate_check = RateLimiter::check(
            $ip_address,
            'signup',
            CONST_SIGNUP_MAX_ATTEMPTS,
            60
        );

        if (!$rate_check['allowed']) {
            throw new Exception($rate_check['message']);
        }

        // Validate reCAPTCHA
        if (CaptchaService::isEnabled()) {
            $captcha_result = CaptchaService::verify($recaptcha_token, 'signup');
            if (!$captcha_result['success']) {
                RateLimiter::recordAttempt($ip_address, 'signup');
                throw new Exception('Bot verification failed. Please try again.');
            }
        }

        // Validate input
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }

        if (empty($first_name) || empty($last_name)) {
            throw new Exception('Please enter your first and last name.');
        }

        if (strlen($password) < CONST_MIN_PASSWORD_LENGTH) {
            throw new Exception('Password must be at least ' . CONST_MIN_PASSWORD_LENGTH . ' characters long.');
        }

        if ($password !== $password_confirm) {
            throw new Exception('Passwords do not match.');
        }

        // Password strength check
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            throw new Exception('Password must contain at least one uppercase letter, one lowercase letter, and one number.');
        }

        if (CONST_REQUIRE_TERMS_ACCEPTANCE && !$terms_accepted) {
            throw new Exception('You must accept the Terms of Service to continue.');
        }

        // Connect to database
        \eBizIndia\PDOConn::connectToDB('mysql');
        $db_conn = \eBizIndia\PDOConn::getConnection();

        // Check if email already exists
        $check_sql = "SELECT id FROM `" . CONST_TBL_PREFIX . "users` WHERE email = :email LIMIT 1";
        $stmt = $db_conn->prepare($check_sql);
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            throw new Exception('An account with this email address already exists. <a href="login.php">Log in</a> or <a href="forgot-password.php">reset your password</a>.');
        }

        // Start transaction
        $db_conn->beginTransaction();

        try {
            // Create user account
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $user_sql = "INSERT INTO `" . CONST_TBL_PREFIX . "users`
                        (email, password, status, account_status, account_type, signup_ip, signup_source, terms_accepted_at, created_at)
                        VALUES (:email, :password, 0, 'inactive', 'individual', :signup_ip, 'web', NOW(), NOW())";

            $stmt = $db_conn->prepare($user_sql);
            $stmt->execute([
                ':email' => $email,
                ':password' => $password_hash,
                ':signup_ip' => $ip_address
            ]);

            $user_id = $db_conn->lastInsertId();

            // Create member profile
            $member_sql = "INSERT INTO `" . CONST_TBL_PREFIX . "members`
                          (user_acnt_id, fname, lname, email, created_at)
                          VALUES (:user_id, :fname, :lname, :email, NOW())";

            $stmt = $db_conn->prepare($member_sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':fname' => $first_name,
                ':lname' => $last_name,
                ':email' => $email
            ]);

            // Create default user preferences
            UserPreferences::createDefaults($user_id);

            // Generate verification token
            $token = TokenGenerator::generateVerificationToken();
            $expires_at = date('Y-m-d H:i:s', strtotime('+' . CONST_EMAIL_VERIFICATION_EXPIRY . ' hours'));

            $verify_sql = "INSERT INTO `" . CONST_TBL_PREFIX . "email_verifications`
                          (user_id, email, token, expires_at, ip_address, created_at)
                          VALUES (:user_id, :email, :token, :expires_at, :ip_address, NOW())";

            $stmt = $db_conn->prepare($verify_sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':email' => $email,
                ':token' => $token,
                ':expires_at' => $expires_at,
                ':ip_address' => $ip_address
            ]);

            // Commit transaction
            $db_conn->commit();

            // Send verification email
            $email_result = EmailService::sendVerificationEmail($user_id, $email, $token);

            if (!$email_result['success']) {
                error_log("Failed to send verification email to $email: " . $email_result['message']);
                // Don't fail signup if email fails - user can resend
            }

            // Clear rate limit on success
            RateLimiter::clear($ip_address, 'signup');

            // Redirect to check email page
            header('Location: check-email.php?email=' . urlencode($email));
            exit;

        } catch (Exception $e) {
            $db_conn->rollBack();
            throw $e;
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
        // Record failed attempt
        if (isset($ip_address)) {
            RateLimiter::recordAttempt($ip_address, 'signup');
        }
    }
}

// Generate CSRF token
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - <?php echo htmlspecialchars(CONST_APP_NAME); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <?php echo CaptchaService::getScriptTag(); ?>
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 40px;
            padding-bottom: 40px;
        }
        .signup-form {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .signup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
            transition: all 0.3s;
        }
        .strength-weak { background-color: #dc3545; width: 33%; }
        .strength-medium { background-color: #ffc107; width: 66%; }
        .strength-strong { background-color: #28a745; width: 100%; }
        .password-requirements {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .password-requirements .met {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="signup-form">
            <div class="signup-header">
                <h2>Create Your Account</h2>
                <p class="text-muted">Start your 12-week journey today</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="signupForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                               value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>"
                               required>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                               value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>"
                               required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" class="form-control" id="password" name="password"
                           minlength="<?php echo CONST_MIN_PASSWORD_LENGTH; ?>" required>
                    <div class="password-strength" id="passwordStrength"></div>
                    <div class="password-requirements" id="passwordRequirements">
                        <small>
                            Password must be at least <?php echo CONST_MIN_PASSWORD_LENGTH; ?> characters and contain:
                            <span id="req-length">• <?php echo CONST_MIN_PASSWORD_LENGTH; ?>+ characters</span>
                            <span id="req-upper">• Uppercase letter</span>
                            <span id="req-lower">• Lowercase letter</span>
                            <span id="req-number">• Number</span>
                        </small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Confirm Password *</label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    <small class="text-danger" id="passwordMatch" style="display:none;">Passwords do not match</small>
                </div>

                <?php if (CONST_REQUIRE_TERMS_ACCEPTANCE): ?>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="terms_accepted" name="terms_accepted" required>
                    <label class="form-check-label" for="terms_accepted">
                        I accept the <a href="<?php echo htmlspecialchars(CONST_TERMS_OF_SERVICE_URL); ?>" target="_blank">Terms of Service</a>
                        and <a href="<?php echo htmlspecialchars(CONST_PRIVACY_POLICY_URL); ?>" target="_blank">Privacy Policy</a> *
                    </label>
                </div>
                <?php endif; ?>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">Create Account</button>
                </div>
            </form>

            <hr class="my-4">

            <p class="text-center mb-0">
                Already have an account? <a href="login.php">Log in</a>
            </p>
        </div>
    </div>

    <script>
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.getElementById('passwordStrength');
        const passwordConfirm = document.getElementById('password_confirm');
        const passwordMatch = document.getElementById('passwordMatch');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            // Check length
            const reqLength = document.getElementById('req-length');
            if (password.length >= <?php echo CONST_MIN_PASSWORD_LENGTH; ?>) {
                strength++;
                reqLength.classList.add('met');
            } else {
                reqLength.classList.remove('met');
            }

            // Check uppercase
            const reqUpper = document.getElementById('req-upper');
            if (/[A-Z]/.test(password)) {
                strength++;
                reqUpper.classList.add('met');
            } else {
                reqUpper.classList.remove('met');
            }

            // Check lowercase
            const reqLower = document.getElementById('req-lower');
            if (/[a-z]/.test(password)) {
                strength++;
                reqLower.classList.add('met');
            } else {
                reqLower.classList.remove('met');
            }

            // Check number
            const reqNumber = document.getElementById('req-number');
            if (/[0-9]/.test(password)) {
                strength++;
                reqNumber.classList.add('met');
            } else {
                reqNumber.classList.remove('met');
            }

            // Update strength indicator
            passwordStrength.className = 'password-strength';
            if (strength >= 4) {
                passwordStrength.classList.add('strength-strong');
            } else if (strength >= 2) {
                passwordStrength.classList.add('strength-medium');
            } else if (strength >= 1) {
                passwordStrength.classList.add('strength-weak');
            }
        });

        // Password match checker
        passwordConfirm.addEventListener('input', function() {
            if (this.value && this.value !== passwordInput.value) {
                passwordMatch.style.display = 'block';
            } else {
                passwordMatch.style.display = 'none';
            }
        });

        <?php if (CaptchaService::isEnabled()): ?>
        // reCAPTCHA v3 integration
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;

            grecaptcha.ready(function() {
                grecaptcha.execute('<?php echo htmlspecialchars(CaptchaService::getSiteKey()); ?>', {action: 'signup'}).then(function(token) {
                    // Add token to form
                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'recaptcha_token';
                    input.value = token;
                    form.appendChild(input);

                    // Submit form
                    form.submit();
                });
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>
