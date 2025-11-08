<?php
/**
 * Check Email Page
 *
 * Shown after signup - instructs user to check email
 */

require_once 'config.php';

$email = $_GET['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Your Email - <?php echo htmlspecialchars(CONST_APP_NAME); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 60px;
            padding-bottom: 40px;
        }
        .check-email-box {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .email-icon {
            font-size: 64px;
            color: #007bff;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="check-email-box">
            <div class="email-icon">✉️</div>
            <h2>Check Your Email</h2>
            <p class="lead">We've sent a verification link to:</p>
            <?php if ($email): ?>
                <p><strong><?php echo htmlspecialchars($email); ?></strong></p>
            <?php endif; ?>

            <div class="alert alert-info mt-4">
                <strong>Next Steps:</strong>
                <ol class="text-start mb-0">
                    <li>Open your email inbox</li>
                    <li>Find the email from <?php echo htmlspecialchars(CONST_APP_NAME); ?></li>
                    <li>Click the verification link</li>
                    <li>Your account will be activated</li>
                </ol>
            </div>

            <p class="text-muted mt-3">
                <small>The verification link will expire in <?php echo CONST_EMAIL_VERIFICATION_EXPIRY; ?> hours.</small>
            </p>

            <hr class="my-4">

            <p class="mb-0">
                Didn't receive the email? <a href="resend-verification.php">Resend verification email</a>
            </p>
            <p class="mt-2">
                <a href="login.php">Back to Login</a>
            </p>
        </div>
    </div>
</body>
</html>
