<?php
/**
 * Terms of Service Page
 *
 * Generic terms of service for 12-Week Year SaaS
 * IMPORTANT: Customize this for your business needs
 * Consider consulting with a lawyer
 */

require_once 'config.php';
require_once CONST_INCLUDES_DIR . '/ebiz-autoload.php';

$page_title = 'Terms of Service';
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

        <p>Welcome to <?php echo htmlspecialchars(CONST_APP_NAME); ?>. By accessing or using our service, you agree to be bound by these Terms of Service.</p>

        <h2>1. Acceptance of Terms</h2>
        <p>By creating an account and using <?php echo htmlspecialchars(CONST_APP_NAME); ?>, you agree to comply with and be bound by these Terms of Service. If you do not agree to these terms, please do not use our service.</p>

        <h2>2. Description of Service</h2>
        <p><?php echo htmlspecialchars(CONST_APP_NAME); ?> provides a goal-setting and productivity management platform based on the 12-week year methodology. Our service helps users plan, track, and achieve their goals through cycles, tasks, and progress monitoring.</p>

        <h2>3. User Accounts</h2>
        <p><strong>Account Creation:</strong> To use our service, you must create an account by providing accurate and complete information. You are responsible for maintaining the confidentiality of your account credentials.</p>
        <p><strong>Account Security:</strong> You are solely responsible for all activities that occur under your account. Notify us immediately of any unauthorized use of your account.</p>
        <p><strong>Age Requirement:</strong> You must be at least 18 years old to use this service.</p>

        <h2>4. User Responsibilities</h2>
        <p>You agree to:</p>
        <ul>
            <li>Provide accurate, current, and complete information during registration</li>
            <li>Maintain the security of your password and account</li>
            <li>Notify us immediately of any unauthorized use of your account</li>
            <li>Use the service only for lawful purposes</li>
            <li>Not attempt to interfere with the proper functioning of the service</li>
            <li>Not attempt to access other users' accounts or data</li>
        </ul>

        <h2>5. Privacy and Data Protection</h2>
        <p>Your use of <?php echo htmlspecialchars(CONST_APP_NAME); ?> is also governed by our <a href="<?php echo htmlspecialchars(CONST_PRIVACY_POLICY_URL); ?>">Privacy Policy</a>. We take your privacy seriously and employ industry-standard security measures to protect your data.</p>
        <p><strong>Data Encryption:</strong> Your goals and tasks are encrypted using AES-256 encryption to protect your privacy.</p>
        <p><strong>Data Ownership:</strong> You retain all rights to the content you create in our service.</p>

        <h2>6. Service Availability</h2>
        <p>We strive to provide reliable service, but we do not guarantee uninterrupted or error-free operation. We may temporarily suspend access for maintenance, updates, or technical issues.</p>

        <h2>7. Intellectual Property</h2>
        <p>The service, including all content, features, and functionality, is owned by <?php echo htmlspecialchars(CONST_APP_NAME); ?> and is protected by copyright, trademark, and other intellectual property laws.</p>
        <p>You may not copy, modify, distribute, or reverse-engineer any part of our service without written permission.</p>

        <h2>8. User Content</h2>
        <p><strong>Your Content:</strong> You retain ownership of all content you create (goals, tasks, notes). By using our service, you grant us a license to store, display, and process your content solely for the purpose of providing the service.</p>
        <p><strong>Content Responsibility:</strong> You are solely responsible for the content you create. We do not endorse, support, represent, or guarantee the completeness or accuracy of any user content.</p>

        <h2>9. Prohibited Conduct</h2>
        <p>You agree not to:</p>
        <ul>
            <li>Use the service for any illegal purpose</li>
            <li>Harass, abuse, or harm other users</li>
            <li>Upload malicious code, viruses, or harmful software</li>
            <li>Attempt to gain unauthorized access to the service or other users' accounts</li>
            <li>Scrape, data mine, or use automated tools to access the service</li>
            <li>Impersonate any person or entity</li>
            <li>Interfere with or disrupt the service</li>
        </ul>

        <h2>10. Account Termination</h2>
        <p>We reserve the right to suspend or terminate your account if you violate these Terms of Service. You may also delete your account at any time by contacting us.</p>
        <p>Upon termination, your right to use the service will immediately cease, and we may delete your data after a reasonable grace period.</p>

        <h2>11. Disclaimers</h2>
        <p>THE SERVICE IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT.</p>
        <p>We do not guarantee that the service will meet your requirements or that it will be uninterrupted, secure, or error-free.</p>

        <h2>12. Limitation of Liability</h2>
        <p>TO THE MAXIMUM EXTENT PERMITTED BY LAW, <?php echo strtoupper(htmlspecialchars(CONST_APP_NAME)); ?> SHALL NOT BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, OR ANY LOSS OF PROFITS OR REVENUES, WHETHER INCURRED DIRECTLY OR INDIRECTLY, OR ANY LOSS OF DATA, USE, GOODWILL, OR OTHER INTANGIBLE LOSSES.</p>

        <h2>13. Indemnification</h2>
        <p>You agree to indemnify and hold harmless <?php echo htmlspecialchars(CONST_APP_NAME); ?> from any claims, damages, losses, liabilities, and expenses arising out of your use of the service or violation of these Terms.</p>

        <h2>14. Changes to Terms</h2>
        <p>We reserve the right to modify these Terms of Service at any time. We will notify users of significant changes via email or through the service. Your continued use of the service after changes constitutes acceptance of the modified terms.</p>

        <h2>15. Governing Law</h2>
        <p>These Terms shall be governed by and construed in accordance with the laws of [Your Jurisdiction], without regard to its conflict of law provisions.</p>

        <h2>16. Contact Information</h2>
        <p>If you have any questions about these Terms of Service, please contact us at:</p>
        <p>Email: <?php echo htmlspecialchars(CONST_SMTP_FROM_EMAIL ?: 'support@example.com'); ?></p>

        <hr class="my-4">

        <p class="text-center">
            <a href="<?php echo htmlspecialchars(CONST_APP_URL); ?>/login.php" class="btn btn-primary">Back to Login</a>
            <a href="<?php echo htmlspecialchars(CONST_PRIVACY_POLICY_URL); ?>" class="btn btn-outline-secondary">Privacy Policy</a>
        </p>
    </div>
</body>
</html>
