<?php
/**
 * Reset Password Page
 *
 * Complete password reset using token from email
 */

require_once 'config.php';
require_once CONST_INCLUDES_DIR . '/ebiz-autoload.php';

use eBizIndia\EmailService;

$error = '';
$success = false;
$token = $_GET['token'] ?? $_POST['token'] ?? '';
$reset_data = null;

// Validate token
if (!empty($token)) {
    try {
        \eBizIndia\PDOConn::connectToDB('mysql');
        $db_conn = \eBizIndia\PDOConn::getConnection();

        $reset_sql = "SELECT pr.*, u.email, m.fname, m.lname
                     FROM `" . CONST_TBL_PREFIX . "password_resets` pr
                     JOIN `" . CONST_TBL_PREFIX . "users` u ON pr.user_id = u.id
                     LEFT JOIN `" . CONST_TBL_PREFIX . "members` m ON u.id = m.user_acnt_id
                     WHERE pr.token = :token
                     LIMIT 1";

        $stmt = $db_conn->prepare($reset_sql);
        $stmt->execute([':token' => $token]);
        $reset_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reset_data) {
            $error = 'Invalid password reset link.';
        } elseif ($reset_data['used_at']) {
            $error = 'This password reset link has already been used. Please request a new one.';
        } elseif (strtotime($reset_data['expires_at']) < time()) {
            $error = 'This password reset link has expired. Please request a new one.';
        }

    } catch (Exception $e) {
        $error = 'An error occurred. Please try again.';
        error_log("Password reset validation error: " . $e->getMessage());
    }
}

// Handle password reset submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error && $reset_data) {
    try {
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Validate password
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

        // Update password
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $db_conn->beginTransaction();

        try {
            // Update user password
            $update_sql = "UPDATE `" . CONST_TBL_PREFIX . "users`
                          SET password = :password
                          WHERE id = :user_id";

            $stmt = $db_conn->prepare($update_sql);
            $stmt->execute([
                ':password' => $password_hash,
                ':user_id' => $reset_data['user_id']
            ]);

            // Mark reset token as used
            $mark_used_sql = "UPDATE `" . CONST_TBL_PREFIX . "password_resets`
                             SET used_at = NOW()
                             WHERE id = :id";

            $stmt = $db_conn->prepare($mark_used_sql);
            $stmt->execute([':id' => $reset_data['id']]);

            $db_conn->commit();

            // Send confirmation email
            $name = trim(($reset_data['fname'] ?? '') . ' ' . ($reset_data['lname'] ?? ''));
            EmailService::sendPasswordChangedEmail($reset_data['email'], $name);

            $success = true;

            // Auto-login
            session_start();
            $_SESSION['password_reset_success'] = true;

        } catch (Exception $e) {
            $db_conn->rollBack();
            throw $e;
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo htmlspecialchars(CONST_APP_NAME); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 60px;
            padding-bottom: 40px;
        }
        .reset-form {
            max-width: 450px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
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
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-form">
            <?php if ($success): ?>
                <div class="text-center">
                    <div style="font-size: 64px; color: #28a745; margin-bottom: 20px;">✓</div>
                    <h2>Password Reset Successfully!</h2>
                    <div class="alert alert-success mt-4">
                        Your password has been changed. You can now log in with your new password.
                    </div>
                    <div class="d-grid mt-4">
                        <a href="login.php" class="btn btn-primary btn-lg">Log In</a>
                    </div>
                </div>

            <?php elseif ($error): ?>
                <h2 class="text-center mb-4">Reset Password</h2>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <div class="text-center mt-4">
                    <a href="forgot-password.php" class="btn btn-primary">Request New Reset Link</a>
                    <a href="login.php" class="btn btn-outline-secondary">Back to Login</a>
                </div>

            <?php else: ?>
                <h2 class="text-center mb-4">Create New Password</h2>

                <p class="text-muted mb-4">
                    Enter your new password below.
                </p>

                <form method="POST" id="resetForm">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password *</label>
                        <input type="password" class="form-control" id="password" name="password"
                               minlength="<?php echo CONST_MIN_PASSWORD_LENGTH; ?>" required>
                        <div class="password-strength" id="passwordStrength"></div>
                        <small class="text-muted">
                            Must be at least <?php echo CONST_MIN_PASSWORD_LENGTH; ?> characters with uppercase, lowercase, and number
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirm New Password *</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        <small class="text-danger" id="passwordMatch" style="display:none;">Passwords do not match</small>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Reset Password</button>
                    </div>
                </form>

                <hr class="my-4">

                <p class="text-center mb-0">
                    <a href="login.php">Back to Login</a>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.getElementById('passwordStrength');
        const passwordConfirm = document.getElementById('password_confirm');
        const passwordMatch = document.getElementById('passwordMatch');

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;

                if (password.length >= <?php echo CONST_MIN_PASSWORD_LENGTH; ?>) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;

                passwordStrength.className = 'password-strength';
                if (strength >= 4) {
                    passwordStrength.classList.add('strength-strong');
                } else if (strength >= 2) {
                    passwordStrength.classList.add('strength-medium');
                } else if (strength >= 1) {
                    passwordStrength.classList.add('strength-weak');
                }
            });

            passwordConfirm.addEventListener('input', function() {
                if (this.value && this.value !== passwordInput.value) {
                    passwordMatch.style.display = 'block';
                } else {
                    passwordMatch.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
