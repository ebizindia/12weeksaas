<?php
/**
 * TokenGenerator Class
 *
 * Generates cryptographically secure tokens for:
 * - Email verification
 * - Password reset
 * - Session tokens
 * - API tokens
 */

namespace eBizIndia;

class TokenGenerator
{
    /**
     * Generate a secure random token
     *
     * @param int $length Token length in bytes (default 32 = 64 hex chars)
     * @return string Hexadecimal token
     */
    public static function generate($length = 32)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Generate a verification token for email
     *
     * @return string 64-character hex token
     */
    public static function generateVerificationToken()
    {
        return self::generate(32); // 32 bytes = 64 hex characters
    }

    /**
     * Generate a password reset token
     *
     * @return string 64-character hex token
     */
    public static function generatePasswordResetToken()
    {
        return self::generate(32); // 32 bytes = 64 hex characters
    }

    /**
     * Generate a shorter token for SMS or similar
     *
     * @param int $length Length of numeric token (default 6)
     * @return string Numeric token
     */
    public static function generateNumericToken($length = 6)
    {
        $max = pow(10, $length) - 1;
        $min = pow(10, $length - 1);
        return str_pad(random_int($min, $max), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Hash a token for secure storage
     *
     * @param string $token Plain text token
     * @return string Hashed token
     */
    public static function hashToken($token)
    {
        return hash('sha256', $token);
    }

    /**
     * Verify a token against a hash
     *
     * @param string $token Plain text token
     * @param string $hash Hashed token from database
     * @return bool True if tokens match
     */
    public static function verifyToken($token, $hash)
    {
        return hash_equals($hash, self::hashToken($token));
    }
}
