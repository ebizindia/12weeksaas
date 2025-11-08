<?php
/**
 * Account Settings Page
 *
 * Phase 1: Individual SaaS Mode
 * Allows users to manage their personal account settings, privacy, and preferences
 */

$page = 'account-settings';
require_once("inc.php");

// Check if user has access
if (!in_array('VIEW', $allowed_menu_perms) && !in_array('ALL', $allowed_menu_perms)) {
    header("Location: " . CONST_APP_ABSURL . "/");
    exit;
}

$page_title = "Account Settings - 12-Week Year";
$page_description = "Manage your personal account and privacy settings";

$user_id = $loggedindata[0]['id'];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['error_code' => 1, 'message' => 'Invalid security token']);
        exit;
    }

    $action = $_POST['action'];

    try {
        switch ($action) {
            case 'update_profile':
                $result = updateProfile($user_id, $_POST);
                echo json_encode($result);
                break;

            case 'change_password':
                $result = changePassword($user_id, $_POST);
                echo json_encode($result);
                break;

            case 'update_privacy':
                $result = updatePrivacySettings($user_id, $_POST);
                echo json_encode($result);
                break;

            case 'update_notifications':
                $result = updateNotificationSettings($user_id, $_POST);
                echo json_encode($result);
                break;

            default:
                echo json_encode(['error_code' => 1, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        \eBizIndia\ErrorHandler::logError(['function' => __FILE__, 'error' => $e->getMessage()], $e);
        echo json_encode(['error_code' => 1, 'message' => 'An error occurred']);
    }
    exit;
}

// Get user data
$member = new \eBizIndia\Member();
$profile = $member->getProfile($user_id);
$user_preferences = \eBizIndia\UserPreferences::get($user_id);
$leaderboard_settings = \eBizIndia\UserPreferences::getLeaderboardSettings($user_id);

// Set template data
$template_data = [
    'page_title' => $page_title,
    'page_description' => $page_description,
    'user_id' => $user_id,
    'profile' => $profile[0] ?? [],
    'preferences' => $user_preferences,
    'leaderboard_settings' => $leaderboard_settings,
    'allowed_menu_perms' => $allowed_menu_perms,
    'csrf_token' => $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))
];

// Store CSRF token in session
$_SESSION['csrf_token'] = $template_data['csrf_token'];

// Register body template
$body_template_file = CONST_THEMES_TEMPLATE_INCLUDE_PATH . 'account-settings.tpl';
$page_renderer->registerBodyTemplate($body_template_file, $template_data);

// Update base template data
$additional_base_template_data = ['page_title' => $page_title, 'module_name' => $page];
$page_renderer->updateBaseTemplateData($additional_base_template_data);

$page_renderer->addCss(\scriptProviderFuncs\getCss($page));
$js_files = \scriptProviderFuncs\getJavascripts($page);
$page_renderer->addJavascript($js_files['BSH'], 'BEFORE_SLASH_HEAD');
$page_renderer->addJavascript($js_files['BSB'], 'BEFORE_SLASH_BODY');

$page_renderer->renderPage();

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Update user profile information
 */
function updateProfile($user_id, $data)
{
    $fname = trim($data['fname'] ?? '');
    $lname = trim($data['lname'] ?? '');
    $time_zone = trim($data['time_zone'] ?? CONST_TIME_ZONE);
    $date_format = trim($data['date_format'] ?? 'd-m-Y');

    if (empty($fname)) {
        return ['error_code' => 2, 'message' => 'First name is required'];
    }

    try {
        $db_conn = \eBizIndia\PDOConn::getConnection();

        // Update members table
        $sql = "UPDATE `" . CONST_TBL_PREFIX . "members`
                SET `fname` = :fname,
                    `lname` = :lname
                WHERE `user_acnt_id` = :user_id";

        $stmt = $db_conn->prepare($sql);
        $stmt->execute([
            ':fname' => $fname,
            ':lname' => $lname,
            ':user_id' => $user_id
        ]);

        // Update user preferences
        \eBizIndia\UserPreferences::update($user_id, [
            'time_zone' => $time_zone,
            'date_format' => $date_format
        ]);

        return ['error_code' => 0, 'message' => 'Profile updated successfully'];

    } catch (Exception $e) {
        \eBizIndia\ErrorHandler::logError(['function' => 'updateProfile', 'error' => $e->getMessage()], $e);
        return ['error_code' => 1, 'message' => 'Failed to update profile'];
    }
}

/**
 * Change user password
 */
function changePassword($user_id, $data)
{
    $current_password = $data['current_password'] ?? '';
    $new_password = $data['new_password'] ?? '';
    $confirm_password = $data['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        return ['error_code' => 2, 'message' => 'All password fields are required'];
    }

    if ($new_password !== $confirm_password) {
        return ['error_code' => 2, 'message' => 'New passwords do not match'];
    }

    if (strlen($new_password) < CONST_MIN_PASSWORD_LENGTH) {
        return ['error_code' => 2, 'message' => 'Password must be at least ' . CONST_MIN_PASSWORD_LENGTH . ' characters'];
    }

    try {
        $db_conn = \eBizIndia\PDOConn::getConnection();

        // Verify current password
        $sql = "SELECT `password` FROM `" . CONST_TBL_PREFIX . "users` WHERE `id` = :user_id";
        $stmt = $db_conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['error_code' => 1, 'message' => 'User not found'];
        }

        // Verify current password (supports both bcrypt and legacy hash)
        $password_valid = false;
        if (password_verify($current_password, $user['password'])) {
            $password_valid = true;
        } elseif (hash(CONST_HASH_FUNCTION, $current_password) === $user['password']) {
            $password_valid = true;
        }

        if (!$password_valid) {
            return ['error_code' => 2, 'message' => 'Current password is incorrect'];
        }

        // Update password with bcrypt
        $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

        $sql = "UPDATE `" . CONST_TBL_PREFIX . "users`
                SET `password` = :password
                WHERE `id` = :user_id";

        $stmt = $db_conn->prepare($sql);
        $stmt->execute([
            ':password' => $new_password_hash,
            ':user_id' => $user_id
        ]);

        return ['error_code' => 0, 'message' => 'Password changed successfully'];

    } catch (Exception $e) {
        \eBizIndia\ErrorHandler::logError(['function' => 'changePassword', 'error' => $e->getMessage()], $e);
        return ['error_code' => 1, 'message' => 'Failed to change password'];
    }
}

/**
 * Update privacy settings
 */
function updatePrivacySettings($user_id, $data)
{
    $leaderboard_visible = isset($data['leaderboard_visible']) ? (bool)$data['leaderboard_visible'] : false;
    $display_name = trim($data['display_name'] ?? '');

    try {
        $result = \eBizIndia\UserPreferences::updateLeaderboardSettings($user_id, $leaderboard_visible, $display_name);

        if ($result) {
            return ['error_code' => 0, 'message' => 'Privacy settings updated successfully'];
        } else {
            return ['error_code' => 1, 'message' => 'Failed to update privacy settings'];
        }

    } catch (Exception $e) {
        \eBizIndia\ErrorHandler::logError(['function' => 'updatePrivacySettings', 'error' => $e->getMessage()], $e);
        return ['error_code' => 1, 'message' => 'Failed to update privacy settings'];
    }
}

/**
 * Update notification preferences
 */
function updateNotificationSettings($user_id, $data)
{
    $email_weekly_summary = isset($data['email_weekly_summary']) ? 1 : 0;
    $email_achievements = isset($data['email_achievements']) ? 1 : 0;
    $email_reminders = isset($data['email_reminders']) ? 1 : 0;

    try {
        $result = \eBizIndia\UserPreferences::update($user_id, [
            'email_weekly_summary' => $email_weekly_summary,
            'email_achievements' => $email_achievements,
            'email_reminders' => $email_reminders
        ]);

        if ($result) {
            return ['error_code' => 0, 'message' => 'Notification preferences updated successfully'];
        } else {
            return ['error_code' => 1, 'message' => 'Failed to update notification preferences'];
        }

    } catch (Exception $e) {
        \eBizIndia\ErrorHandler::logError(['function' => 'updateNotificationSettings', 'error' => $e->getMessage()], $e);
        return ['error_code' => 1, 'message' => 'Failed to update notification preferences'];
    }
}
