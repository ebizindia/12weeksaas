<?php
/**
 * CaptchaService Class
 *
 * Google reCAPTCHA v3 integration
 * Provides invisible bot protection
 */

namespace eBizIndia;

class CaptchaService
{
    /**
     * Verify reCAPTCHA v3 token
     *
     * @param string $token Token from client-side reCAPTCHA
     * @param string $action Action name (signup, login, etc.)
     * @return array ['success' => bool, 'score' => float, 'message' => string]
     */
    public static function verify($token, $action = 'submit')
    {
        // If reCAPTCHA is disabled, always pass
        if (!defined('CONST_RECAPTCHA_ENABLED') || !CONST_RECAPTCHA_ENABLED) {
            return [
                'success' => true,
                'score' => 1.0,
                'message' => 'reCAPTCHA disabled'
            ];
        }

        // Check if secret key is configured
        if (!defined('CONST_RECAPTCHA_SECRET_KEY') || empty(CONST_RECAPTCHA_SECRET_KEY)) {
            error_log("reCAPTCHA secret key not configured");
            return [
                'success' => true, // Fail open - allow request
                'score' => 0.5,
                'message' => 'reCAPTCHA not configured'
            ];
        }

        // Validate token
        if (empty($token)) {
            return [
                'success' => false,
                'score' => 0.0,
                'message' => 'reCAPTCHA token missing'
            ];
        }

        try {
            // Make request to Google reCAPTCHA API
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret' => CONST_RECAPTCHA_SECRET_KEY,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
            ];

            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data),
                    'timeout' => 10 // 10 second timeout
                ]
            ];

            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

            if ($result === false) {
                error_log("reCAPTCHA API request failed");
                return [
                    'success' => true, // Fail open
                    'score' => 0.5,
                    'message' => 'reCAPTCHA verification failed'
                ];
            }

            $response = json_decode($result, true);

            // Check response
            if (!$response['success']) {
                $error_codes = $response['error-codes'] ?? [];
                error_log("reCAPTCHA verification failed: " . implode(', ', $error_codes));
                return [
                    'success' => false,
                    'score' => 0.0,
                    'message' => 'reCAPTCHA verification failed'
                ];
            }

            // Check action matches
            if (isset($response['action']) && $response['action'] !== $action) {
                error_log("reCAPTCHA action mismatch: expected $action, got {$response['action']}");
                return [
                    'success' => false,
                    'score' => 0.0,
                    'message' => 'Invalid reCAPTCHA action'
                ];
            }

            $score = $response['score'] ?? 0.0;
            $threshold = defined('CONST_RECAPTCHA_THRESHOLD') ? CONST_RECAPTCHA_THRESHOLD : 0.5;

            // Check score against threshold
            if ($score < $threshold) {
                return [
                    'success' => false,
                    'score' => $score,
                    'message' => 'reCAPTCHA score too low (possible bot)'
                ];
            }

            return [
                'success' => true,
                'score' => $score,
                'message' => 'reCAPTCHA passed'
            ];

        } catch (\Exception $e) {
            error_log("reCAPTCHA exception: " . $e->getMessage());
            return [
                'success' => true, // Fail open - don't block users due to our error
                'score' => 0.5,
                'message' => 'reCAPTCHA error'
            ];
        }
    }

    /**
     * Get reCAPTCHA site key for frontend
     *
     * @return string|null Site key or null if not configured
     */
    public static function getSiteKey()
    {
        if (defined('CONST_RECAPTCHA_SITE_KEY') && !empty(CONST_RECAPTCHA_SITE_KEY)) {
            return CONST_RECAPTCHA_SITE_KEY;
        }
        return null;
    }

    /**
     * Check if reCAPTCHA is enabled
     *
     * @return bool True if enabled
     */
    public static function isEnabled()
    {
        return defined('CONST_RECAPTCHA_ENABLED') && CONST_RECAPTCHA_ENABLED;
    }

    /**
     * Get HTML script tag for reCAPTCHA v3
     *
     * @return string HTML script tag or empty string if disabled
     */
    public static function getScriptTag()
    {
        if (!self::isEnabled()) {
            return '';
        }

        $site_key = self::getSiteKey();
        if (!$site_key) {
            return '';
        }

        return '<script src="https://www.google.com/recaptcha/api.js?render=' . htmlspecialchars($site_key) . '"></script>';
    }

    /**
     * Get JavaScript code to execute reCAPTCHA and submit form
     *
     * @param string $action Action name
     * @param string $formId Form ID
     * @return string JavaScript code
     */
    public static function getFormSubmitScript($action, $formId)
    {
        if (!self::isEnabled()) {
            return '';
        }

        $site_key = self::getSiteKey();
        if (!$site_key) {
            return '';
        }

        return "
        <script>
        function onSubmit{$formId}(e) {
            e.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute('" . htmlspecialchars($site_key) . "', {action: '" . htmlspecialchars($action) . "'}).then(function(token) {
                    var form = document.getElementById('" . htmlspecialchars($formId) . "');
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'recaptcha_token';
                    input.value = token;
                    form.appendChild(input);
                    form.submit();
                });
            });
        }
        document.getElementById('" . htmlspecialchars($formId) . "').addEventListener('submit', onSubmit{$formId});
        </script>
        ";
    }
}
