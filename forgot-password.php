<?php
/**
 * Forgot Password Page
 *
 * Request password reset email
 */

require_once 'config.php';
require_once CONST_INCLUDES_DIR . '/ebiz-autoload.php';

use eBizIndia\RateLimiter;
use eBizIndia\TokenGenerator;
use eBizIndia\EmailService;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }

        // Rate limiting
        $rate_check = RateLimiter::check(
            $email,
            'password_reset',
            CONST_PASSWORD_RESET_MAX_ATTEMPTS,
            60
        );

        if (!$rate_check['allowed']) {
            throw new Exception($rate_check['message']);
        }

        \eBizIndia\PDOConn::connectToDB('mysql');
        $db_conn = \eBizIndia\PDOConn::getConnection();

        // Find user
        $user_sql = "SELECT u.id, u.email, m.fname, m.lname
                    FROM `" . CONST_TBL_PREFIX . "users` u
                    LEFT JOIN `" . CONST_TBL_PREFIX . "members` m ON u.id = m.user_acnt_id
                    WHERE u.email = :email AND u.account_status = 'active'
                    LIMIT 1";

        $stmt = $db_conn->prepare($user_sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Don't reveal if user exists or not (security - prevent email enumeration)
        $success = 'If an account exists with this email, a password reset link has been sent.';

        if ($user) {
            // Generate reset token
            $token = TokenGenerator::generatePasswordResetToken();
            $expires_at = date('Y-m-d H:i:s', strtotime('+' . CONST_PASSWORD_RESET_EXPIRY . ' hours'));
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            $insert_sql = "INSERT INTO `" . CONST_TBL_PREFIX . "password_resets`
                          (user_id, email, token, expires_at, ip_address, created_at)
                          VALUES (:user_id, :email, :token, :expires_at, :ip_address, NOW())";

            $stmt = $db_conn->prepare($insert_sql);
            $stmt->execute([
                ':user_id' => $user['id'],
                ':email' => $email,
                ':token' => $token,
                ':expires_at' => $expires_at,
                ':ip_address' => $ip_address
            ]);

            // Send reset email
            $name = trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''));
            EmailService::sendPasswordResetEmail($email, $token, $name);
        }

        RateLimiter::recordAttempt($email, 'password_reset');

    } catch (Exception $e) {
        $error = $e->getMessage();
        if (isset($email)) {
            RateLimiter::recordAttempt($email, 'password_reset');
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo htmlspecialchars(CONST_APP_NAME); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 60px;
            padding-bottom: 40px;
        }
        .forgot-form {
            max-width: 450px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="forgot-form">
            <h2 class="text-center mb-4">Reset Your Password</h2>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <p class="text-muted">
                    Check your email for a link to reset your password. The link will expire in <?php echo CONST_PASSWORD_RESET_EXPIRY; ?> hour<?php echo CONST_PASSWORD_RESET_EXPIRY > 1 ? 's' : ''; ?>.
                </p>
                <p class="text-center mt-4">
                    <a href="login.php">Back to Login</a>
                </p>

            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <p class="text-muted mb-4">
                    Enter your email address and we'll send you a link to reset your password.
                </p>

                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Send Reset Link</button>
                    </div>
                </form>

                <hr class="my-4">

                <p class="text-center mb-0">
                    <a href="login.php">Back to Login</a> •
                    <a href="signup.php">Create Account</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
