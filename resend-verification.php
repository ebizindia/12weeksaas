<?php
/**
 * Resend Verification Email
 *
 * Allows users to request a new verification email
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
            'resend_verification',
            CONST_RESEND_VERIFICATION_MAX_ATTEMPTS,
            60
        );

        if (!$rate_check['allowed']) {
            throw new Exception($rate_check['message']);
        }

        \eBizIndia\PDOConn::connectToDB('mysql');
        $db_conn = \eBizIndia\PDOConn::getConnection();

        // Find user
        $user_sql = "SELECT u.id, u.email, u.email_verified_at, m.fname, m.lname
                    FROM `" . CONST_TBL_PREFIX . "users` u
                    LEFT JOIN `" . CONST_TBL_PREFIX . "members` m ON u.id = m.user_acnt_id
                    WHERE u.email = :email
                    LIMIT 1";

        $stmt = $db_conn->prepare($user_sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Don't reveal if user exists or not (security)
        if (!$user || $user['email_verified_at']) {
            // Show success message anyway
            $success = 'If an unverified account exists with this email, a verification link has been sent.';
            RateLimiter::recordAttempt($email, 'resend_verification');
        } else {
            // User exists and is not verified - send new verification email

            // Invalidate old tokens
            $invalidate_sql = "UPDATE `" . CONST_TBL_PREFIX . "email_verifications`
                             SET verified_at = NOW()
                             WHERE user_id = :user_id AND verified_at IS NULL";

            $stmt = $db_conn->prepare($invalidate_sql);
            $stmt->execute([':user_id' => $user['id']]);

            // Generate new token
            $token = TokenGenerator::generateVerificationToken();
            $expires_at = date('Y-m-d H:i:s', strtotime('+' . CONST_EMAIL_VERIFICATION_EXPIRY . ' hours'));
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            $insert_sql = "INSERT INTO `" . CONST_TBL_PREFIX . "email_verifications`
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

            // Send verification email
            $email_result = EmailService::sendVerificationEmail($user['id'], $email, $token);

            if ($email_result['success']) {
                $success = 'A new verification email has been sent to ' . htmlspecialchars($email);
                RateLimiter::recordAttempt($email, 'resend_verification');
            } else {
                throw new Exception('Failed to send verification email. Please try again later.');
            }
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
        if (isset($email)) {
            RateLimiter::recordAttempt($email, 'resend_verification');
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification Email - <?php echo htmlspecialchars(CONST_APP_NAME); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 60px;
            padding-bottom: 40px;
        }
        .resend-form {
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
        <div class="resend-form">
            <h2 class="text-center mb-4">Resend Verification Email</h2>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
                <p class="text-center">
                    <a href="login.php">Back to Login</a>
                </p>

            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <p class="text-muted mb-4">
                    Enter your email address and we'll send you a new verification link.
                </p>

                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Send Verification Email</button>
                    </div>
                </form>

                <hr class="my-4">

                <p class="text-center mb-0">
                    <a href="login.php">Back to Login</a> •
                    <a href="signup.php">Create New Account</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
