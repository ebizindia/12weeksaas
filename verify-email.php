<?php
/**
 * Email Verification Handler
 *
 * Verifies email address using token from email link
 */

require_once 'config.php';
require_once CONST_INCLUDES_DIR . '/ebiz-autoload.php';

use eBizIndia\EmailService;

$success = false;
$error = '';
$message = '';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Invalid verification link.';
} else {
    try {
        \eBizIndia\PDOConn::connectToDB('mysql');
        $db_conn = \eBizIndia\PDOConn::getConnection();

        // Find verification record
        $verify_sql = "SELECT ev.*, u.email, m.fname, m.lname
                      FROM `" . CONST_TBL_PREFIX . "email_verifications` ev
                      JOIN `" . CONST_TBL_PREFIX . "users` u ON ev.user_id = u.id
                      LEFT JOIN `" . CONST_TBL_PREFIX . "members` m ON u.id = m.user_acnt_id
                      WHERE ev.token = :token
                      LIMIT 1";

        $stmt = $db_conn->prepare($verify_sql);
        $stmt->execute([':token' => $token]);
        $verification = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$verification) {
            $error = 'Invalid verification link. The link may have already been used or does not exist.';
        } elseif ($verification['verified_at']) {
            // Already verified
            $message = 'Your email has already been verified. You can log in now.';
            $success = true;
        } elseif (strtotime($verification['expires_at']) < time()) {
            // Expired
            $error = 'This verification link has expired. Please request a new verification email.';
        } else {
            // Valid token - verify the email
            $db_conn->beginTransaction();

            try {
                // Mark verification as complete
                $update_verify_sql = "UPDATE `" . CONST_TBL_PREFIX . "email_verifications`
                                     SET verified_at = NOW()
                                     WHERE id = :id";

                $stmt = $db_conn->prepare($update_verify_sql);
                $stmt->execute([':id' => $verification['id']]);

                // Update user account
                $update_user_sql = "UPDATE `" . CONST_TBL_PREFIX . "users`
                                   SET email_verified_at = NOW(),
                                       account_status = 'active',
                                       status = 1
                                   WHERE id = :user_id";

                $stmt = $db_conn->prepare($update_user_sql);
                $stmt->execute([':user_id' => $verification['user_id']]);

                $db_conn->commit();

                // Send welcome email
                $name = trim(($verification['fname'] ?? '') . ' ' . ($verification['lname'] ?? ''));
                EmailService::sendWelcomeEmail(
                    $verification['user_id'],
                    $verification['email'],
                    $name
                );

                $success = true;
                $message = 'Your email has been verified successfully! Your account is now active.';

                // Auto-login (optional)
                session_start();
                $_SESSION['verification_success'] = true;
                $_SESSION['verified_email'] = $verification['email'];

            } catch (Exception $e) {
                $db_conn->rollBack();
                throw $e;
            }
        }

    } catch (Exception $e) {
        $error = 'An error occurred during verification. Please try again or contact support.';
        error_log("Email verification error: " . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - <?php echo htmlspecialchars(CONST_APP_NAME); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 60px;
            padding-bottom: 40px;
        }
        .verification-box {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .success-icon {
            color: #28a745;
        }
        .error-icon {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verification-box">
            <?php if ($success): ?>
                <div class="icon success-icon">✓</div>
                <h2>Email Verified!</h2>
                <p class="lead"><?php echo htmlspecialchars($message); ?></p>

                <div class="alert alert-success mt-4">
                    <strong>Welcome to <?php echo htmlspecialchars(CONST_APP_NAME); ?>!</strong>
                    <p class="mb-0">You can now log in and start planning your first 12-week cycle.</p>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <a href="login.php" class="btn btn-primary btn-lg">Log In</a>
                </div>

            <?php elseif ($error): ?>
                <div class="icon error-icon">✗</div>
                <h2>Verification Failed</h2>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>

                <div class="mt-4">
                    <a href="resend-verification.php" class="btn btn-primary">Request New Verification Email</a>
                    <a href="login.php" class="btn btn-outline-secondary">Back to Login</a>
                </div>

            <?php else: ?>
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Verifying your email...</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
