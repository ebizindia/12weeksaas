<?php
/**
 * Admin Tool: Manual User Creation
 *
 * Phase 1: Individual SaaS Mode
 * Allows system administrators to manually create user accounts
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
$created_user = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    // Validate inputs
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');

    // Validation
    if (!$email) {
        $error_message = 'Invalid email address';
    } elseif (strlen($password) < CONST_MIN_PASSWORD_LENGTH) {
        $error_message = 'Password must be at least ' . CONST_MIN_PASSWORD_LENGTH . ' characters';
    } elseif (empty($fname)) {
        $error_message = 'First name is required';
    } else {
        try {
            $db_conn = \eBizIndia\PDOConn::getConnection();

            // Check if email already exists
            $check_sql = "SELECT COUNT(*) FROM `" . CONST_TBL_PREFIX . "users` WHERE `email` = :email";
            $check_stmt = $db_conn->prepare($check_sql);
            $check_stmt->execute([':email' => $email]);

            if ($check_stmt->fetchColumn() > 0) {
                $error_message = 'Email address already exists';
            } else {
                // Start transaction
                $db_conn->beginTransaction();

                // Create user account
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                $user_sql = "INSERT INTO `" . CONST_TBL_PREFIX . "users`
                            (`email`, `password`, `status`, `account_status`, `account_type`, `created_by`, `date_created`)
                            VALUES (:email, :password, 1, 'active', 'individual', :created_by, NOW())";

                $user_stmt = $db_conn->prepare($user_sql);
                $user_stmt->execute([
                    ':email' => $email,
                    ':password' => $hashed_password,
                    ':created_by' => $loggedindata[0]['id'] ?? null
                ]);

                $user_id = $db_conn->lastInsertId();

                // Create member profile
                $member_sql = "INSERT INTO `" . CONST_TBL_PREFIX . "members`
                              (`user_acnt_id`, `fname`, `lname`, `email`, `active`, `joining_dt`, `leaderboard_visible`)
                              VALUES (:user_id, :fname, :lname, :email, 1, NOW(), 0)";

                $member_stmt = $db_conn->prepare($member_sql);
                $member_stmt->execute([
                    ':user_id' => $user_id,
                    ':fname' => $fname,
                    ':lname' => $lname,
                    ':email' => $email
                ]);

                // Create default preferences
                \eBizIndia\UserPreferences::createDefaults($user_id);

                // Commit transaction
                $db_conn->commit();

                $success_message = "User created successfully!";
                $created_user = [
                    'user_id' => $user_id,
                    'email' => $email,
                    'password' => $password, // Show password once
                    'name' => $fname . ' ' . $lname
                ];
            }

        } catch (Exception $e) {
            if ($db_conn->inTransaction()) {
                $db_conn->rollBack();
            }
            \eBizIndia\ErrorHandler::logError(['function' => 'admin-add-user', 'error' => $e->getMessage()], $e);
            $error_message = 'Failed to create user: ' . $e->getMessage();
        }
    }
}

// Get existing users
try {
    $db_conn = \eBizIndia\PDOConn::getConnection();

    $users_sql = "SELECT u.id, u.email, u.account_status, u.date_created,
                         m.fname, m.lname, m.joining_dt
                  FROM `" . CONST_TBL_PREFIX . "users` u
                  LEFT JOIN `" . CONST_TBL_PREFIX . "members` m ON u.id = m.user_acnt_id
                  WHERE u.account_type = 'individual'
                  ORDER BY u.date_created DESC
                  LIMIT 100";

    $users_stmt = $db_conn->query($users_sql);
    $existing_users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $existing_users = [];
    $error_message = 'Failed to load user list: ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New User - Admin Tool</title>
    <link rel="stylesheet" href="<?= CONST_THEMES_CSS_PATH ?>bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-plus"></i> Create New User Account</h2>
            <a href="12-week-dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Success Message -->
        <?php if ($success_message && $created_user): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h5><i class="fas fa-check-circle"></i> <?= $success_message ?></h5>
            <div class="password-generated mt-3">
                <p class="mb-2"><strong>Important: Save these credentials</strong></p>
                <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($created_user['name']) ?></p>
                <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($created_user['email']) ?></p>
                <p class="mb-1">
                    <strong>Password:</strong>
                    <code id="generated-password"><?= htmlspecialchars($created_user['password']) ?></code>
                    <span class="password-copy" onclick="copyPassword()">
                        <i class="fas fa-copy"></i> Copy
                    </span>
                </p>
                <p class="text-danger small mb-0">
                    <i class="fas fa-exclamation-triangle"></i>
                    This password will not be shown again. Please save it now and send it to the user securely.
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

        <!-- Create User Form -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-plus"></i> New User Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   placeholder="user@example.com">
                            <small class="form-text text-muted">This will be used for login</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="password" name="password" required
                                       minlength="<?= CONST_MIN_PASSWORD_LENGTH ?>" value="<?= bin2hex(random_bytes(6)) ?>">
                                <button class="btn btn-outline-secondary" type="button" onclick="generatePassword()">
                                    <i class="fas fa-sync"></i> Generate
                                </button>
                            </div>
                            <small class="form-text text-muted">Minimum <?= CONST_MIN_PASSWORD_LENGTH ?> characters. User can change this after first login.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fname" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fname" name="fname" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lname" name="lname">
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                        <button type="submit" name="create_user" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Existing Users List -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-users"></i> Existing Users (<?= count($existing_users) ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($existing_users)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No users found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($existing_users as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars(trim($u['fname'] . ' ' . $u['lname'])) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if ($u['account_status'] === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= ucfirst($u['account_status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('Y-m-d', strtotime($u['date_created'])) ?></td>
                                <td>
                                    <a href="admin-reset-password.php?user_id=<?= $u['id'] ?>"
                                       class="btn btn-sm btn-warning" title="Reset Password">
                                        <i class="fas fa-key"></i>
                                    </a>
                                    <a href="admin-toggle-status.php?user_id=<?= $u['id'] ?>"
                                       class="btn btn-sm btn-secondary" title="Toggle Status">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
            document.getElementById('password').value = password;
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
