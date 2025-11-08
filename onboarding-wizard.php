<?php
/**
 * Onboarding Wizard
 *
 * Simple welcome wizard for new users
 * - Welcome message
 * - Complete profile
 * - Create first cycle
 */

require_once 'config.php';
require_once CONST_INCLUDES_DIR . '/ebiz-autoload.php';
require_once CONST_INCLUDES_DIR . '/general-func.php';

// Check if user is logged in
session_start();
$loggedindata = checkIfLoggedIn('', '', '', '', '', 1);
if (!$loggedindata[0]) {
    header('Location: login.php');
    exit;
}

$user_id = $loggedindata[0]['user_details']['id'];

// Check if onboarding is already completed
\eBizIndia\PDOConn::connectToDB('mysql');
$db_conn = \eBizIndia\PDOConn::getConnection();

$check_sql = "SELECT onboarding_completed FROM `" . CONST_TBL_PREFIX . "users` WHERE id = :user_id";
$stmt = $db_conn->prepare($check_sql);
$stmt->execute([':user_id' => $user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user_data && $user_data['onboarding_completed']) {
    header('Location: 12-week-dashboard.php');
    exit;
}

$step = (int)($_GET['step'] ?? 1);
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        // Update profile preferences
        try {
            $timezone = $_POST['timezone'] ?? 'Asia/Kolkata';
            $date_format = $_POST['date_format'] ?? 'd-m-Y';

            // Update user preferences
            $prefs_sql = "UPDATE `" . CONST_TBL_PREFIX . "user_preferences`
                         SET time_zone = :timezone,
                             date_format = :date_format
                         WHERE user_id = :user_id";

            $stmt = $db_conn->prepare($prefs_sql);
            $stmt->execute([
                ':timezone' => $timezone,
                ':date_format' => $date_format,
                ':user_id' => $user_id
            ]);

            header('Location: onboarding-wizard.php?step=3');
            exit;

        } catch (Exception $e) {
            $error = 'Failed to save preferences. Please try again.';
        }

    } elseif ($step === 3) {
        // Mark onboarding as complete
        try {
            $update_sql = "UPDATE `" . CONST_TBL_PREFIX . "users`
                          SET onboarding_completed = 1
                          WHERE id = :user_id";

            $stmt = $db_conn->prepare($update_sql);
            $stmt->execute([':user_id' => $user_id]);

            // Redirect to dashboard
            header('Location: 12-week-dashboard.php?welcome=1');
            exit;

        } catch (Exception $e) {
            $error = 'Failed to complete onboarding. Please try again.';
        }
    }
}

$page_title = 'Welcome to ' . CONST_APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .wizard-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .wizard-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .progress-bar-custom {
            height: 5px;
            background: #e9ecef;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .progress-bar-fill {
            height: 100%;
            background: #667eea;
            border-radius: 5px;
            transition: width 0.3s;
        }
        .step-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .step-title {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="wizard-container">
            <div class="wizard-card">
                <!-- Progress bar -->
                <div class="progress-bar-custom">
                    <div class="progress-bar-fill" style="width: <?php echo ($step / 3 * 100); ?>%;"></div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($step === 1): ?>
                    <!-- Step 1: Welcome -->
                    <div class="text-center">
                        <div class="step-icon">👋</div>
                        <h1 class="step-title">Welcome!</h1>
                        <p class="lead">We're excited to help you achieve your goals with the 12-Week Year system.</p>

                        <div class="alert alert-info text-start mt-4">
                            <strong>What is the 12-Week Year?</strong>
                            <p class="mb-0">Instead of annual goals, focus on what you can accomplish in just 12 weeks. This creates urgency, improves focus, and dramatically increases your execution rate.</p>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <a href="onboarding-wizard.php?step=2" class="btn btn-primary btn-lg">Get Started</a>
                        </div>
                    </div>

                <?php elseif ($step === 2): ?>
                    <!-- Step 2: Set Preferences -->
                    <div class="text-center">
                        <div class="step-icon">⚙️</div>
                        <h2 class="step-title">Set Your Preferences</h2>
                        <p class="text-muted">Customize your experience</p>
                    </div>

                    <form method="POST" class="mt-4">
                        <div class="mb-3">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select class="form-select" id="timezone" name="timezone">
                                <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                                <option value="America/New_York">America/New_York (EST)</option>
                                <option value="America/Los_Angeles">America/Los_Angeles (PST)</option>
                                <option value="Europe/London">Europe/London (GMT)</option>
                                <option value="Australia/Sydney">Australia/Sydney (AEST)</option>
                                <option value="UTC">UTC</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="date_format" class="form-label">Date Format</label>
                            <select class="form-select" id="date_format" name="date_format">
                                <option value="d-m-Y">DD-MM-YYYY (31-12-2025)</option>
                                <option value="m-d-Y">MM-DD-YYYY (12-31-2025)</option>
                                <option value="Y-m-d">YYYY-MM-DD (2025-12-31)</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Continue</button>
                            <a href="onboarding-wizard.php?step=1" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>

                <?php elseif ($step === 3): ?>
                    <!-- Step 3: Ready to Start -->
                    <div class="text-center">
                        <div class="step-icon">🚀</div>
                        <h2 class="step-title">You're All Set!</h2>
                        <p class="lead">Ready to create your first 12-week cycle and start achieving your goals.</p>

                        <div class="alert alert-success text-start mt-4">
                            <strong>Next Steps:</strong>
                            <ol class="mb-0">
                                <li>Create your first 12-week cycle</li>
                                <li>Set your goals for this cycle</li>
                                <li>Break down goals into weekly tasks</li>
                                <li>Track your progress daily</li>
                            </ol>
                        </div>

                        <form method="POST">
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Go to Dashboard</button>
                                <a href="onboarding-wizard.php?step=2" class="btn btn-outline-secondary">Back</a>
                            </div>
                        </form>
                    </div>

                <?php endif; ?>

                <p class="text-center text-muted mt-4 mb-0">
                    <small>Step <?php echo $step; ?> of 3</small>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
