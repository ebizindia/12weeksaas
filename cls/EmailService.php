<?php
/**
 * EmailService Class
 *
 * Centralized email sending service using PHPMailer
 * Handles:
 * - Email verification
 * - Password reset
 * - Welcome emails
 * - Notifications
 */

namespace eBizIndia;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PDO;

class EmailService
{
    /**
     * Send verification email
     *
     * @param int $user_id User ID
     * @param string $email Email address
     * @param string $token Verification token
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendVerificationEmail($user_id, $email, $token)
    {
        $verification_url = CONST_APP_URL . '/verify-email.php?token=' . urlencode($token);

        $subject = 'Verify your email address';
        $template = 'verification';

        $data = [
            'verification_url' => $verification_url,
            'app_name' => CONST_APP_NAME ?? '12-Week Year',
            'expiry_hours' => CONST_EMAIL_VERIFICATION_EXPIRY ?? 24
        ];

        return self::send($user_id, $email, $subject, $template, $data);
    }

    /**
     * Send welcome email (after verification)
     *
     * @param int $user_id User ID
     * @param string $email Email address
     * @param string $name User's name
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendWelcomeEmail($user_id, $email, $name)
    {
        $login_url = CONST_APP_URL . '/login.php';

        $subject = 'Welcome to ' . (CONST_APP_NAME ?? '12-Week Year') . '!';
        $template = 'welcome';

        $data = [
            'name' => $name,
            'login_url' => $login_url,
            'app_name' => CONST_APP_NAME ?? '12-Week Year'
        ];

        return self::send($user_id, $email, $subject, $template, $data);
    }

    /**
     * Send password reset email
     *
     * @param string $email Email address
     * @param string $token Reset token
     * @param string $name User's name
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendPasswordResetEmail($email, $token, $name = '')
    {
        $reset_url = CONST_APP_URL . '/reset-password.php?token=' . urlencode($token);

        $subject = 'Reset your password';
        $template = 'password-reset';

        $data = [
            'name' => $name,
            'reset_url' => $reset_url,
            'app_name' => CONST_APP_NAME ?? '12-Week Year',
            'expiry_hours' => CONST_PASSWORD_RESET_EXPIRY ?? 1
        ];

        return self::send(null, $email, $subject, $template, $data);
    }

    /**
     * Send password changed notification
     *
     * @param string $email Email address
     * @param string $name User's name
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendPasswordChangedEmail($email, $name)
    {
        $subject = 'Your password was changed';
        $template = 'password-changed';

        $data = [
            'name' => $name,
            'app_name' => CONST_APP_NAME ?? '12-Week Year',
            'support_email' => CONST_SMTP_FROM_EMAIL ?? 'support@example.com'
        ];

        return self::send(null, $email, $subject, $template, $data);
    }

    /**
     * Generic email send method
     *
     * @param int|null $user_id User ID (null for non-user emails)
     * @param string $to Email address
     * @param string $subject Email subject
     * @param string $template Template name
     * @param array $data Template data
     * @return array ['success' => bool, 'message' => string]
     */
    private static function send($user_id, $to, $subject, $template, $data)
    {
        try {
            // Check if SMTP is configured
            if (!defined('CONST_SMTP_HOST') || empty(CONST_SMTP_HOST)) {
                $error_msg = "SMTP not configured";
                error_log($error_msg);
                self::logEmail($user_id, $to, $subject, $template, 'failed', $error_msg);
                return ['success' => false, 'message' => $error_msg];
            }

            // Create PHPMailer instance
            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = CONST_SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = CONST_SMTP_USERNAME ?? '';
            $mail->Password = CONST_SMTP_PASSWORD ?? '';
            $mail->SMTPSecure = (CONST_SMTP_ENCRYPTION ?? 'tls') === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = CONST_SMTP_PORT ?? 587;

            // Character set
            $mail->CharSet = 'UTF-8';

            // Recipients
            $mail->setFrom(
                CONST_SMTP_FROM_EMAIL ?? 'noreply@example.com',
                CONST_SMTP_FROM_NAME ?? 'No Reply'
            );
            $mail->addAddress($to);
            $mail->addReplyTo(
                CONST_SMTP_FROM_EMAIL ?? 'noreply@example.com',
                CONST_SMTP_FROM_NAME ?? 'No Reply'
            );

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;

            // Load template
            $html_body = self::loadTemplate($template, $data);
            $text_body = self::htmlToText($html_body);

            $mail->Body = $html_body;
            $mail->AltBody = $text_body;

            // Send
            $mail->send();

            // Log success
            self::logEmail($user_id, $to, $subject, $template, 'sent');

            return ['success' => true, 'message' => 'Email sent successfully'];

        } catch (Exception $e) {
            $error_msg = "Email send failed: {$mail->ErrorInfo}";
            error_log($error_msg);
            self::logEmail($user_id, $to, $subject, $template, 'failed', $error_msg);
            return ['success' => false, 'message' => $error_msg];
        } catch (\Exception $e) {
            $error_msg = "Email send error: " . $e->getMessage();
            error_log($error_msg);
            self::logEmail($user_id, $to, $subject, $template, 'failed', $error_msg);
            return ['success' => false, 'message' => $error_msg];
        }
    }

    /**
     * Load email template
     *
     * @param string $template Template name
     * @param array $data Template data
     * @return string HTML content
     */
    private static function loadTemplate($template, $data)
    {
        $template_file = dirname(__DIR__) . '/templates/emails/' . $template . '.html';

        if (!file_exists($template_file)) {
            // Return default simple template
            return self::getDefaultTemplate($template, $data);
        }

        $content = file_get_contents($template_file);

        // Replace variables
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', htmlspecialchars($value ?? ''), $content);
        }

        return $content;
    }

    /**
     * Get default email template (simple, no external file)
     *
     * @param string $template Template name
     * @param array $data Template data
     * @return string HTML content
     */
    private static function getDefaultTemplate($template, $data)
    {
        $app_name = htmlspecialchars($data['app_name'] ?? '12-Week Year');

        switch ($template) {
            case 'verification':
                return "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <h2>Verify your email address</h2>
                    <p>Thank you for signing up for {$app_name}!</p>
                    <p>Please click the link below to verify your email address and activate your account:</p>
                    <p><a href='" . htmlspecialchars($data['verification_url'] ?? '#') . "' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Verify Email</a></p>
                    <p>This link will expire in " . (int)($data['expiry_hours'] ?? 24) . " hours.</p>
                    <p>If you didn't sign up for an account, you can safely ignore this email.</p>
                    <p>Best regards,<br>{$app_name} Team</p>
                </body>
                </html>";

            case 'welcome':
                return "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <h2>Welcome to {$app_name}!</h2>
                    <p>Hi " . htmlspecialchars($data['name'] ?? '') . ",</p>
                    <p>Your email has been verified and your account is now active!</p>
                    <p>Ready to get started? Log in to create your first 12-week cycle:</p>
                    <p><a href='" . htmlspecialchars($data['login_url'] ?? '#') . "' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Log In</a></p>
                    <p>We're excited to help you achieve your goals!</p>
                    <p>Best regards,<br>{$app_name} Team</p>
                </body>
                </html>";

            case 'password-reset':
                return "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <h2>Reset your password</h2>
                    <p>Hi " . htmlspecialchars($data['name'] ?? 'there') . ",</p>
                    <p>We received a request to reset your password. Click the link below to create a new password:</p>
                    <p><a href='" . htmlspecialchars($data['reset_url'] ?? '#') . "' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a></p>
                    <p>This link will expire in " . (int)($data['expiry_hours'] ?? 1) . " hour.</p>
                    <p>If you didn't request a password reset, you can safely ignore this email. Your password will not be changed.</p>
                    <p>Best regards,<br>{$app_name} Team</p>
                </body>
                </html>";

            case 'password-changed':
                return "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <h2>Password Changed</h2>
                    <p>Hi " . htmlspecialchars($data['name'] ?? '') . ",</p>
                    <p>This is a confirmation that your password was successfully changed.</p>
                    <p>If you didn't make this change, please contact us immediately at " . htmlspecialchars($data['support_email'] ?? 'support@example.com') . "</p>
                    <p>Best regards,<br>{$app_name} Team</p>
                </body>
                </html>";

            default:
                return "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <p>Email from {$app_name}</p>
                </body>
                </html>";
        }
    }

    /**
     * Convert HTML to plain text
     *
     * @param string $html HTML content
     * @return string Plain text
     */
    private static function htmlToText($html)
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Log email to database
     *
     * @param int|null $user_id User ID
     * @param string $to Email address
     * @param string $subject Subject
     * @param string $template Template name
     * @param string $status Status (queued, sent, failed, bounced)
     * @param string|null $error_message Error message if failed
     * @return bool Success
     */
    private static function logEmail($user_id, $to, $subject, $template, $status, $error_message = null)
    {
        try {
            \eBizIndia\PDOConn::connectToDB('mysql');
            $db_conn = \eBizIndia\PDOConn::getConnection();

            $table = CONST_TBL_PREFIX . 'email_log';

            $sql = "INSERT INTO `$table`
                   (`user_id`, `to_email`, `subject`, `template`, `status`, `sent_at`, `error_message`)
                   VALUES (:user_id, :to_email, :subject, :template, :status, :sent_at, :error_message)";

            $stmt = $db_conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':to_email' => $to,
                ':subject' => $subject,
                ':template' => $template,
                ':status' => $status,
                ':sent_at' => ($status === 'sent') ? date('Y-m-d H:i:s') : null,
                ':error_message' => $error_message
            ]);

            return true;

        } catch (\Exception $e) {
            error_log("Failed to log email: " . $e->getMessage());
            return false;
        }
    }
}
