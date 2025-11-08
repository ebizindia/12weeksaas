<?php
/**
 * Waitlist Signup Handler
 * Handles waitlist registrations with validation and email notifications
 */

header('Content-Type: application/json');

// Include main config if available, or use standalone config
$config_file = dirname(__DIR__) . '/config.php';
if (file_exists($config_file)) {
    require_once $config_file;
    require_once CONST_INCLUDES_DIR . '/ebiz-autoload.php';
    $use_main_db = true;
} else {
    // Standalone configuration
    define('WAITLIST_DB_HOST', 'localhost');
    define('WAITLIST_DB_NAME', 'your_database');
    define('WAITLIST_DB_USER', 'your_username');
    define('WAITLIST_DB_PASS', 'your_password');
    $use_main_db = false;
}

$response = ['success' => false, 'message' => ''];

try {
    // Validate input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $title = trim($_POST['title'] ?? '');

    // Validation
    if (empty($name)) {
        throw new Exception('Please enter your name.');
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address.');
    }

    // Sanitize inputs
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $company = htmlspecialchars($company, ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // Connect to database
    if ($use_main_db) {
        \eBizIndia\PDOConn::connectToDB('mysql');
        $db = \eBizIndia\PDOConn::getConnection();
        $table_prefix = CONST_TBL_PREFIX ?? '';
    } else {
        $dsn = "mysql:host=" . WAITLIST_DB_HOST . ";dbname=" . WAITLIST_DB_NAME . ";charset=utf8mb4";
        $db = new PDO($dsn, WAITLIST_DB_USER, WAITLIST_DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $table_prefix = '';
    }

    // Check if email already exists
    $check_sql = "SELECT id FROM `{$table_prefix}waitlist` WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($check_sql);
    $stmt->execute([':email' => $email]);

    if ($stmt->fetch()) {
        throw new Exception('This email is already on the waitlist. We\'ll notify you when we launch!');
    }

    // Insert into waitlist
    $insert_sql = "INSERT INTO `{$table_prefix}waitlist`
                  (name, email, company, title, ip_address, user_agent, created_at)
                  VALUES (:name, :email, :company, :title, :ip_address, :user_agent, NOW())";

    $stmt = $db->prepare($insert_sql);
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':company' => $company,
        ':title' => $title,
        ':ip_address' => $ip_address,
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);

    $waitlist_id = $db->lastInsertId();

    // Get position in waitlist
    $position_sql = "SELECT COUNT(*) as position FROM `{$table_prefix}waitlist` WHERE id <= :id";
    $stmt = $db->prepare($position_sql);
    $stmt->execute([':id' => $waitlist_id]);
    $position = $stmt->fetch()['position'] ?? 0;

    // Send confirmation email
    sendWaitlistEmail($name, $email, $position);

    // Send admin notification (if configured)
    if (defined('CONST_SMTP_FROM_EMAIL')) {
        sendAdminNotification($name, $email, $company, $title, $position);
    }

    $response['success'] = true;
    $response['message'] = 'Success! You\'re on the waitlist.';
    $response['email'] = $email;
    $response['position'] = $position;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Waitlist signup error: " . $e->getMessage());
}

echo json_encode($response);

/**
 * Send confirmation email to waitlist member
 */
function sendWaitlistEmail($name, $email, $position) {
    // Check if EmailService is available (Phase 2)
    if (class_exists('eBizIndia\EmailService')) {
        sendEmailWithService($name, $email, $position);
    } else {
        sendEmailBasic($name, $email, $position);
    }
}

/**
 * Send email using EmailService (if available)
 */
function sendEmailWithService($name, $email, $position) {
    // This would use the EmailService from Phase 2
    // For now, fall back to basic email
    sendEmailBasic($name, $email, $position);
}

/**
 * Send basic email using PHP mail()
 */
function sendEmailBasic($name, $email, $position) {
    $subject = "You're on the waitlist for 12 Week Edge!";

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #0f3460, #16213e); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .position { font-size: 48px; font-weight: bold; color: #d4af37; text-align: center; margin: 20px 0; }
            .btn { display: inline-block; background: #d4af37; color: #1a1a2e; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to 12 Week Edge!</h1>
            </div>
            <div class='content'>
                <h2>Hi " . htmlspecialchars($name) . ",</h2>

                <p>Thank you for joining the waitlist! You're now part of an exclusive group of executives preparing to transform their execution.</p>

                <div class='position'>
                    #" . number_format($position) . "
                </div>
                <p style='text-align: center; color: #666;'>Your position on the waitlist</p>

                <h3>What happens next?</h3>
                <ul>
                    <li>We'll email you with exclusive updates as we prepare for launch</li>
                    <li>You'll get early access before the public launch</li>
                    <li>You'll receive a special discount (50% off first year)</li>
                    <li>Priority support for lifetime</li>
                </ul>

                <h3>In the meantime...</h3>
                <p>Start thinking about your next 12-week goals. What would make the next quarter exceptional for you?</p>

                <p>We're excited to help you achieve more in 12 weeks than others do in 12 months.</p>

                <p><strong>The 12 Week Edge Team</strong></p>

                <hr style='border: 1px solid #ddd; margin: 30px 0;'>

                <p style='font-size: 12px; color: #999;'>
                    You're receiving this email because you signed up for the 12 Week Edge waitlist.
                    If you didn't sign up, you can safely ignore this email.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: 12 Week Edge <noreply@12weekedge.com>\r\n";

    mail($email, $subject, $message, $headers);
}

/**
 * Send notification to admin
 */
function sendAdminNotification($name, $email, $company, $title, $position) {
    if (!defined('CONST_SMTP_FROM_EMAIL')) {
        return;
    }

    $admin_email = CONST_SMTP_FROM_EMAIL;
    $subject = "New Waitlist Signup: " . $name;

    $message = "
    New waitlist signup:

    Name: $name
    Email: $email
    Company: $company
    Title: $title
    Position: #$position
    Time: " . date('Y-m-d H:i:s') . "
    ";

    $headers = "From: 12 Week Edge <noreply@12weekedge.com>\r\n";

    mail($admin_email, $subject, $message, $headers);
}
