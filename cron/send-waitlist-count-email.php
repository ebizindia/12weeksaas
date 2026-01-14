<?php
#!/usr/bin/env php
/**
 * Daily Waitlist Count Email Script
 *
 * This standalone script sends a daily email with the count of waitlist members.
 * It can be run via cron without requiring authentication.
 *
 * Usage:
 *   php /path/to/12weeksaas/cron/send-waitlist-count-email.php
 *
 * Cron Example (daily at 9:00 AM):
 *   0 9 * * * /usr/bin/php /home/user/12weeksaas/cron/send-waitlist-count-email.php >> /var/log/waitlist-email.log 2>&1
 */

// Output timestamp for logging
echo "=== Daily Waitlist Count Email Script ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Check if config.php exists
    $config_file = __DIR__ . "/../app/config.php";
    echo $config_file;
    if (!file_exists($config_file)) {
        throw new Exception("Configuration file not found. Please copy config-sample.php to config.php and update settings.");
    }
    
    // Bootstrap the application
    require_once(__DIR__ . "/../app/config.php");
    require_once(CONST_INCLUDES_DIR . "/ebiz-autoload.php");
    require_once(CONST_INCLUDES_DIR . "/general-func.php");
    require_once(CONST_CLASS_DIR . "/phpmailer/vendor/autoload.php");
    // Initialize database connection
    $conn = \eBizIndia\PDOConn::getInstance();
    echo "✓ Database connection established\n";

    // Count waitlist members
    $sql = "SELECT COUNT(*) as total_count FROM `waitlist`";
    $stmt = \eBizIndia\PDOConn::query($sql);
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    $waitlist_count = $result['total_count'] ?? 0;

    echo "✓ Waitlist count retrieved: {$waitlist_count}\n";

    // Check if recipient email is configured
    if (!defined('CONST_WAITLIST_REPORT_RECP') || empty(CONST_WAITLIST_REPORT_RECP['to'])) {
        throw new Exception("Waitlist report recipient email not configured in config.php");
    }

    // Prepare email data
    $current_date = date('F j, Y');
    $subject = CONST_MAIL_SUBJECT_PREFIX . " Daily Waitlist Count - {$current_date}";

    // Create HTML email body
    $html_body = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border: 1px solid #dee2e6;
        }
        .count-box {
            background: white;
            padding: 40px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .count-number {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        .count-label {
            font-size: 18px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .footer {
            background: #343a40;
            color: #adb5bd;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            border-radius: 0 0 10px 10px;
        }
        .divider {
            height: 2px;
            background: linear-gradient(to right, #667eea, #764ba2);
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 12-Week Year Waitlist Report</h1>
    </div>
    <div class="content">
        <div class="count-box">
            <div class="count-label">Total Waitlist Members</div>
            <div class="count-number">' . number_format($waitlist_count) . '</div>
        </div>
        <div class="divider"></div>
    </div>
    <div class="footer">
        Report generated on ' . $current_date . ' at ' . date('g:i A') . '<br>
        12-Week Year Automated Reporting System
    </div>
</body>
</html>';

    // Plain text version
    $text_body = "
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   12-Week Year Waitlist Report
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Total Waitlist Members: {$waitlist_count}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Report Date: {$current_date}
Time: " . date('g:i A') . "
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
";

    // Initialize mailer
    $mail = new \eBizIndia\Mailer(true, ['use_default' => CONST_USE_SERVERS_DEFAULT_SMTP]);
    // Override email if configured (for testing)
    if (!empty(CONST_EMAIL_OVERRIDE)) {
        $override_emails = is_array(CONST_EMAIL_OVERRIDE)
            ? CONST_EMAIL_OVERRIDE
            : array_map('trim', explode(',', CONST_EMAIL_OVERRIDE));
        $mail->setOverrideEmail($override_emails);
        echo "⚠ Email override is active. Sending to: " . implode(', ', $override_emails) . "\n";
    }

    // Prepare email data
    $message_data = [
        'subject' => $subject,
        'html_message' => $html_body,
        'text_message' => $text_body,
        'attachments' => [],
        'inlineimages' => []
    ];

    $other_data = [
        'from' => CONST_MAIL_SENDERS_EMAIL,
        'from_name' => CONST_MAIL_SENDERS_NAME,
        'recp' => CONST_WAITLIST_REPORT_RECP['to'],
        'cc' => CONST_WAITLIST_REPORT_RECP['cc'] ?? [],
        'bcc' => CONST_WAITLIST_REPORT_RECP['bcc'] ?? [],
        'reply_to' => !empty(CONST_MAIL_REPLYTO_EMAIL) ? [CONST_MAIL_REPLYTO_EMAIL] : []
    ];

    // Send email
    $result = $mail->sendEmail($message_data, $other_data);
    
    if ($result) {
        echo "✓ Email sent successfully to: " . implode(', ', CONST_WAITLIST_REPORT_RECP['to']) . "\n";
        echo "\n=== Script completed successfully ===\n";
        echo "Finished at: " . date('Y-m-d H:i:s') . "\n";
        exit(0);
    } else {
        throw new Exception("Failed to send email. Check mail server configuration.");
    }

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    echo "\n=== Script failed ===\n";
    echo "Finished at: " . date('Y-m-d H:i:s') . "\n";
    exit(1);
}
?>
