<?php
/**
 * Privacy Policy Page
 *
 * Generic privacy policy for 12-Week Year SaaS
 * IMPORTANT: Customize this for your business needs and jurisdiction
 * Consider consulting with a privacy lawyer
 */

require_once 'config.php';
require_once CONST_INCLUDES_DIR . '/ebiz-autoload.php';

$page_title = 'Privacy Policy';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - <?php echo CONST_APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body {
            padding-top: 20px;
            padding-bottom: 40px;
        }
        .container {
            max-width: 800px;
        }
        h1 {
            margin-bottom: 30px;
        }
        h2 {
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        .last-updated {
            color: #666;
            font-style: italic;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <p class="last-updated">Last Updated: <?php echo date('F j, Y'); ?></p>

        <p>This Privacy Policy describes how <?php echo htmlspecialchars(CONST_APP_NAME); ?> ("we", "us", or "our") collects, uses, and protects your personal information when you use our service.</p>

        <h2>1. Information We Collect</h2>

        <h3>1.1 Information You Provide</h3>
        <p>When you create an account, we collect:</p>
        <ul>
            <li><strong>Account Information:</strong> Email address, password (encrypted), first name, last name</li>
            <li><strong>Profile Information:</strong> Timezone, date format preferences, notification preferences</li>
            <li><strong>Content:</strong> Your goals, tasks, notes, and other content you create in the service</li>
        </ul>

        <h3>1.2 Automatically Collected Information</h3>
        <p>When you use our service, we automatically collect:</p>
        <ul>
            <li><strong>Usage Data:</strong> Pages visited, features used, actions performed</li>
            <li><strong>Technical Data:</strong> IP address, browser type, device information, operating system</li>
            <li><strong>Log Data:</strong> Access times, error logs, performance data</li>
        </ul>

        <h3>1.3 Cookies and Tracking</h3>
        <p>We use cookies and similar technologies to:</p>
        <ul>
            <li>Maintain your login session</li>
            <li>Remember your preferences</li>
            <li>Analyze service usage and performance</li>
            <li>Provide security features</li>
        </ul>

        <h2>2. How We Use Your Information</h2>
        <p>We use your information to:</p>
        <ul>
            <li><strong>Provide the Service:</strong> Create and maintain your account, store your content, deliver features</li>
            <li><strong>Communication:</strong> Send you service-related emails (verification, password reset, updates)</li>
            <li><strong>Improve the Service:</strong> Analyze usage patterns, fix bugs, develop new features</li>
            <li><strong>Security:</strong> Detect and prevent fraud, abuse, and security incidents</li>
            <li><strong>Legal Compliance:</strong> Comply with legal obligations and enforce our terms</li>
        </ul>

        <h2>3. Data Security</h2>
        <p>We take security seriously and employ industry-standard measures to protect your data:</p>
        <ul>
            <li><strong>Encryption in Transit:</strong> All data transmitted between your device and our servers uses HTTPS/TLS encryption</li>
            <li><strong>Encryption at Rest:</strong> Your goals and tasks are encrypted using AES-256-GCM encryption</li>
            <li><strong>Password Security:</strong> Passwords are hashed using bcrypt before storage</li>
            <li><strong>Access Controls:</strong> Strict access controls limit who can access your data</li>
            <li><strong>Regular Security Audits:</strong> We regularly review and update our security practices</li>
        </ul>
        <p>However, no method of transmission over the Internet is 100% secure. While we strive to protect your data, we cannot guarantee absolute security.</p>

        <h2>4. Data Sharing and Disclosure</h2>
        <p>We do NOT sell your personal information to third parties.</p>

        <p>We may share your information only in the following circumstances:</p>
        <ul>
            <li><strong>With Your Consent:</strong> When you explicitly authorize us to share information</li>
            <li><strong>Service Providers:</strong> With trusted third-party service providers who help us operate the service (e.g., email delivery, hosting). These providers are contractually obligated to protect your data.</li>
            <li><strong>Legal Requirements:</strong> When required by law, court order, or government request</li>
            <li><strong>Safety and Security:</strong> To protect the rights, property, or safety of our users or others</li>
            <li><strong>Business Transfers:</strong> In the event of a merger, acquisition, or sale of assets (you will be notified)</li>
        </ul>

        <h2>5. Data Retention</h2>
        <p>We retain your personal information for as long as your account is active or as needed to provide you with services.</p>
        <p>If you delete your account, we will delete or anonymize your personal information within 30 days, unless we are required to retain it for legal or compliance purposes.</p>

        <h2>6. Your Rights and Choices</h2>
        <p>You have the following rights regarding your personal information:</p>
        <ul>
            <li><strong>Access:</strong> Request a copy of your personal information</li>
            <li><strong>Correction:</strong> Update or correct your account information anytime in Account Settings</li>
            <li><strong>Deletion:</strong> Request deletion of your account and personal data</li>
            <li><strong>Data Portability:</strong> Request an export of your data in a portable format</li>
            <li><strong>Opt-Out:</strong> Unsubscribe from marketing emails (service emails cannot be disabled)</li>
            <li><strong>Cookies:</strong> Disable cookies through your browser settings (may affect functionality)</li>
        </ul>
        <p>To exercise these rights, please contact us at <?php echo htmlspecialchars(CONST_SMTP_FROM_EMAIL ?: 'privacy@example.com'); ?></p>

        <h2>7. Leaderboard and Public Information</h2>
        <p>Our service includes an optional public leaderboard:</p>
        <ul>
            <li><strong>Opt-In Only:</strong> You must explicitly enable leaderboard visibility in your privacy settings</li>
            <li><strong>Pseudonyms:</strong> You can choose a display name (pseudonym) instead of your real name</li>
            <li><strong>Limited Information:</strong> Only your chosen name and score are displayed; your actual goals and tasks remain private</li>
            <li><strong>Control:</strong> You can opt out of the leaderboard at any time</li>
        </ul>

        <h2>8. Children's Privacy</h2>
        <p>Our service is not intended for children under 18 years of age. We do not knowingly collect personal information from children. If we become aware that we have collected information from a child, we will delete it promptly.</p>

        <h2>9. International Data Transfers</h2>
        <p>Your information may be transferred to and processed in countries other than your country of residence. We ensure appropriate safeguards are in place to protect your information in accordance with this Privacy Policy.</p>

        <h2>10. Third-Party Services</h2>
        <p>Our service may contain links to third-party websites or integrate with third-party services. This Privacy Policy does not apply to those third parties. We encourage you to review their privacy policies.</p>

        <p><strong>Third-Party Services We Use:</strong></p>
        <ul>
            <?php if (defined('CONST_RECAPTCHA_ENABLED') && CONST_RECAPTCHA_ENABLED): ?>
            <li><strong>Google reCAPTCHA:</strong> For spam and bot protection (<a href="https://policies.google.com/privacy" target="_blank">Google Privacy Policy</a>)</li>
            <?php endif; ?>
            <li><strong>Email Delivery:</strong> For sending transactional emails</li>
        </ul>

        <h2>11. Do Not Track</h2>
        <p>We do not currently respond to "Do Not Track" signals from browsers, as there is no industry standard for compliance.</p>

        <h2>12. California Privacy Rights (CCPA)</h2>
        <p>If you are a California resident, you have additional rights under the California Consumer Privacy Act (CCPA):</p>
        <ul>
            <li>Right to know what personal information we collect, use, and disclose</li>
            <li>Right to request deletion of your personal information</li>
            <li>Right to opt-out of the sale of personal information (we don't sell your data)</li>
            <li>Right to non-discrimination for exercising your privacy rights</li>
        </ul>

        <h2>13. European Privacy Rights (GDPR)</h2>
        <p>If you are in the European Economic Area (EEA), you have rights under the General Data Protection Regulation (GDPR):</p>
        <ul>
            <li>Right to access, rectify, or erase your personal data</li>
            <li>Right to restrict or object to processing</li>
            <li>Right to data portability</li>
            <li>Right to withdraw consent</li>
            <li>Right to lodge a complaint with a supervisory authority</li>
        </ul>
        <p><strong>Legal Basis:</strong> We process your data based on your consent, contract performance, legal obligations, and legitimate interests.</p>

        <h2>14. Changes to This Privacy Policy</h2>
        <p>We may update this Privacy Policy from time to time. We will notify you of significant changes via email or through the service. Your continued use of the service after changes indicates acceptance of the updated policy.</p>

        <h2>15. Contact Us</h2>
        <p>If you have any questions, concerns, or requests regarding this Privacy Policy or our privacy practices, please contact us:</p>
        <p>
            <strong>Email:</strong> <?php echo htmlspecialchars(CONST_SMTP_FROM_EMAIL ?: 'privacy@example.com'); ?><br>
            <strong>Subject Line:</strong> Privacy Inquiry
        </p>

        <hr class="my-4">

        <p class="text-center">
            <a href="<?php echo htmlspecialchars(CONST_APP_URL); ?>/login.php" class="btn btn-primary">Back to Login</a>
            <a href="<?php echo htmlspecialchars(CONST_TERMS_OF_SERVICE_URL); ?>" class="btn btn-outline-secondary">Terms of Service</a>
        </p>
    </div>
</body>
</html>
