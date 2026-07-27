<?php

namespace App\Core;

use App\Models\FailedLogin;

class RateLimiter
{
    private string $key;
    private int $maxAttempts;
    private int $decayMinutes;
    private int $lockoutMinutes;

    public function __construct(string $key, int $maxAttempts = 5, int $decayMinutes = 15, int $lockoutMinutes = 15)
    {
        $this->key = $key;
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
        $this->lockoutMinutes = $lockoutMinutes;
    }

    public static function forLogin(string $identifier, string $ip): self
    {
        return new self(
            "login:{$identifier}:{$ip}",
            (int) env('MAX_LOGIN_ATTEMPTS', 5),
            (int) env('LOGIN_DECAY_MINUTES', 15),
            (int) env('LOCKOUT_MINUTES', 15)
        );
    }

    public static function forOtp(string $identifier): self
    {
        return new self(
            "otp:{$identifier}",
            (int) env('OTP_MAX_ATTEMPTS', 3),
            (int) env('OTP_DECAY_MINUTES', 15),
            (int) env('OTP_LOCKOUT_MINUTES', 15)
        );
    }

    public function attempt(callable $callback): mixed
    {
        if ($this->isLockedOut()) {
            return false;
        }

        $result = $callback();

        if ($result === false) {
            $this->hit();
        } else {
            $this->clear();
        }

        return $result;
    }

    public function hit(): void
    {
        FailedLogin::record($this->key);
    }

    public function clear(): void
    {
        FailedLogin::clearByKey($this->key);
    }

    public function isLockedOut(): bool
    {
        $attempts = FailedLogin::countRecent($this->key, $this->decayMinutes);
        return $attempts >= $this->maxAttempts;
    }

    public function remainingAttempts(): int
    {
        $attempts = FailedLogin::countRecent($this->key, $this->decayMinutes);
        return max(0, $this->maxAttempts - $attempts);
    }

    public function availableIn(): int
    {
        if (!$this->isLockedOut()) {
            return 0;
        }

        $recent = FailedLogin::getMostRecent($this->key);
        if (!$recent) {
            return 0;
        }

        $lockoutUntil = strtotime($recent['attempted_at']) + ($this->lockoutMinutes * 60);
        return max(0, $lockoutUntil - time());
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }
}
