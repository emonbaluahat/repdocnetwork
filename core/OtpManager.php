<?php

namespace App\Core;

use App\Models\OtpAttempt;
use App\Services\SmsProviderInterface;

class OtpManager
{
    private static ?SmsProviderInterface $smsProvider = null;

    public static function setSmsProvider(SmsProviderInterface $provider): void
    {
        self::$smsProvider = $provider;
    }

    public static function generate(int $length = 6): string
    {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= random_int(0, 9);
        }
        return $otp;
    }

    public static function send(string $to, string $otp, string $type = 'email'): bool
    {
        if ($type === 'email') {
            return Mailer::sendOtp($to, $otp);
        }

        if ($type === 'phone' && self::$smsProvider !== null) {
            $message = __('mail.otp_body', ['otp' => $otp]);
            return self::$smsProvider->send($to, $message);
        }

        return false;
    }

    public static function verify(string $otp, string $storedOtp): bool
    {
        if (empty($storedOtp) || empty($otp)) {
            return false;
        }
        return hash_equals($storedOtp, $otp);
    }

    public static function canSend(string $identifier, string $ip): bool
    {
        $attempt = OtpAttempt::findByIdentifier($identifier);
        if (!$attempt) {
            return true;
        }

        if ($attempt['locked_until'] && strtotime($attempt['locked_until']) > time()) {
            return false;
        }

        $maxAttempts = (int) env('OTP_MAX_ATTEMPTS', 3);
        $decayMinutes = (int) env('OTP_DECAY_MINUTES', 15);
        $cutoff = date('Y-m-d H:i:s', time() - ($decayMinutes * 60));

        if ($attempt['last_attempt_at'] < $cutoff) {
            return true;
        }

        return $attempt['attempts'] < $maxAttempts;
    }

    public static function recordAttempt(string $identifier, string $ip): void
    {
        OtpAttempt::record($identifier, $ip);
    }

    public static function lock(string $identifier): void
    {
        $lockoutMinutes = (int) env('OTP_LOCKOUT_MINUTES', 15);
        OtpAttempt::lock($identifier, $lockoutMinutes);
    }

    public static function clearAttempts(string $identifier): void
    {
        OtpAttempt::clearByIdentifier($identifier);
    }

    public static function getRemainingAttempts(string $identifier): int
    {
        $attempt = OtpAttempt::findByIdentifier($identifier);
        if (!$attempt) {
            return (int) env('OTP_MAX_ATTEMPTS', 3);
        }

        $maxAttempts = (int) env('OTP_MAX_ATTEMPTS', 3);
        $decayMinutes = (int) env('OTP_DECAY_MINUTES', 15);
        $cutoff = date('Y-m-d H:i:s', time() - ($decayMinutes * 60));

        if ($attempt['last_attempt_at'] < $cutoff) {
            return $maxAttempts;
        }

        return max(0, $maxAttempts - $attempt['attempts']);
    }

    public static function getLockoutTime(string $identifier): int
    {
        $attempt = OtpAttempt::findByIdentifier($identifier);
        if (!$attempt || !$attempt['locked_until']) {
            return 0;
        }

        return max(0, strtotime($attempt['locked_until']) - time());
    }
}
