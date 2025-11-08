<?php
/**
 * Admin Tool: Reset User Password
 *
 * Phase 1: Individual SaaS Mode
 * Allows system administrators to reset user passwords
 *
 * SECURITY: Only accessible by SYSTEM_ADMIN role or from localhost
 */

require_once 'inc.php';

// Security check: Only SYSTEM_ADMIN or localhost access
$is_admin = isset($loggedindata[0]['profile_details']['assigned_roles'][0]['role']) &&
             $loggedindata[0]['profile_details']['assigned_roles'][0]['role'] === 'ADMIN';

$is_localhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost']);

if (!$is_admin && !$is_localhost && !defined('CONST_SAAS_MODE')) {
    die('<h1>Access Denied</h1><p>This tool is only accessible to system administrators.</p>');
}

$success_message = '';
$error_message = '';
$reset_info = null;
$user_info = null;

// Get user ID from query string
$user_id = (int)($_GET['user_id'] ?? 0);

if ($user_id > 0) {
    try {
        $db_conn = \eBizIndia\PDOConn::getConnection();

        // Get user info
        $user_sql = "SELECT u.id, u.email, u.account_status,
                            m.fname, m.lname
                     FROM `" . CONST_TBL_PREFIX . "users` u
                     LEFT JOIN `" . CONST_TBL_PREFIX . "members` m ON u.id = m.user_acnt_id
                     WHERE u.id = :user_id AND u.account_type = 'individual'";

        $user_stmt = $db_conn->prepare($user_sql);
        $user_stmt->execute([':user_id' => $user_id]);
        $user_info = $user_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_info) {
            $error_message = 'User not found';
            $user_id = 0;
        }

    } catch (Exception $e) {
        $error_message = 'Failed to load user information: ' . $e->getMessage();
        $user_id = 0;
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $new_password = $_POST['new_password'] ?? '';

    if ($user_id <= 0) {
        $error_message = 'Invalid user ID';
    } elseif (strlen($new_password) < CONST_MIN_PASSWORD_LENGTH) {
        $error_message = 'Password must be at least ' . CONST_MIN_PASSWORD_LENGTH . ' characters';
    } else {
        try {
            $db_conn = \eBizIndia\PDOConn::getConnection();

            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update password
            $update_sql = "UPDATE `" . CONST_TBL_PREFIX . "users`
                          SET `password` = :password
                          WHERE `id` = :user_id AND `account_type` = 'individual'";

            $update_stmt = $db_conn->prepare($update_sql);
            $update_stmt->execute([
                ':password' => $hashed_password,
                ':user_id' => $user_id
            ]);

            if ($update_stmt->rowCount() > 0) {
                $success_message = 'Password reset successfully!';
                $reset_info = [
                    'user_id' => $user_id,
                    'email' => $user_info['email'],
                    'password' => $new_password,
                    'name' => trim($user_info['fname'] . ' ' . $user_info['lname'])
                ];
            } else {
                $error_message = 'Failed to reset password. User may not exist.';
            }

        } catch (Exception $e) {
            \eBizIndia\ErrorHandler::logError(['function' => 'admin-reset-password', 'error' => $e->getMessage()], $e);
            $error_message = 'Failed to reset password: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset User Password - Admin Tool</title>
    <link rel="stylesheet" href="<?= CONST_THEMES_CSS_PATH ?>bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .password-generated {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .password-copy {
            cursor: pointer;
            color: #0d6efd;
        }
        .password-copy:hover {
            text-decoration: underline;
        }
        .user-info-box {
            background-color: #e7f3ff;
            border: 1px solid #0d6efd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-key"></i> Reset User Password</h2>
            <a href="admin-add-user.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to User Management
            </a>
        </div>

        <!-- Success Message -->
        <?php if ($success_message && $reset_info): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h5><i class="fas fa-check-circle"></i> <?= $success_message ?></h5>
            <div class="password-generated mt-3">
                <p class="mb-2"><strong>Important: Save these credentials</strong></p>
                <p class="mb-1"><strong>User:</strong> <?= htmlspecialchars($reset_info['name']) ?></p>
                <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($reset_info['email']) ?></p>
                <p class="mb-1">
                    <strong>New Password:</strong>
                    <code id="generated-password"><?= htmlspecialchars($reset_info['password']) ?></code>
                    <span class="password-copy" onclick="copyPassword()">
                        <i class="fas fa-copy"></i> Copy
                    </span>
                </p>
                <p class="text-danger small mb-0">
                    <i class="fas fa-exclamation-triangle"></i>
                    This password will not be shown again. Please save it and send it to the user securely.
                </p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($user_info): ?>
        <!-- User Information -->
        <div class="user-info-box">
            <h5><i class="fas fa-user"></i> User Information</h5>
            <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars(trim($user_info['fname'] . ' ' . $user_info['lname'])) ?></p>
            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user_info['email']) ?></p>
            <p class="mb-0">
                <strong>Status:</strong>
                <?php if ($user_info['account_status'] === 'active'): ?>
                    <span class="badge bg-success">Active</span>
                <?php else: ?>
                    <span class="badge bg-secondary"><?= ucfirst($user_info['account_status']) ?></span>
                <?php endif; ?>
            </p>
        </div>

        <!-- Reset Password Form -->
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-key"></i> Reset Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">

                    <div class="mb-3">
                        <label for="new_password" class="form-label">
                            New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="new_password" name="new_password" required
                                   minlength="<?= CONST_MIN_PASSWORD_LENGTH ?>" value="<?= bin2hex(random_bytes(6)) ?>">
                            <button class="btn btn-outline-secondary" type="button" onclick="generatePassword()">
                                <i class="fas fa-sync"></i> Generate
                            </button>
                        </div>
                        <small class="form-text text-muted">
                            Minimum <?= CONST_MIN_PASSWORD_LENGTH ?> characters. User can change this after login.
                        </small>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This will immediately replace the user's current password.
                        Make sure to save and securely share the new password with the user.
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="admin-add-user.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" name="reset_password" class="btn btn-warning">
                            <i class="fas fa-key"></i> Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- No User Selected -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            No user selected. Please select a user from the <a href="admin-add-user.php">user management page</a>.
        </div>
        <?php endif; ?>
    </div>

    <script src="<?= CONST_THEMES_JAVASCRIPT_PATH ?>jquery-3.3.1.min.js"></script>
    <script src="<?= CONST_THEMES_CSS_PATH ?>../js/bootstrap.bundle.min.js"></script>
    <script>
        function generatePassword() {
            const length = 12;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%";
            let password = "";
            for (let i = 0; i < length; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            document.getElementById('new_password').value = password;
        }

        function copyPassword() {
            const passwordText = document.getElementById('generated-password').textContent;
            navigator.clipboard.writeText(passwordText).then(function() {
                alert('Password copied to clipboard!');
            }, function(err) {
                console.error('Failed to copy password: ', err);
            });
        }
    </script>
</body>
</html>
