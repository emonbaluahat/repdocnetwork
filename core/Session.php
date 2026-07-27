<?php

namespace App\Core;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Lax');

            if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                ini_set('session.cookie_secure', '1');
            }

            ini_set('session.gc_maxlifetime', SESSION_LIFETIME * 60);
            ini_set('session.cookie_lifetime', SESSION_LIFETIME * 60);

            session_start();
        }

        self::$started = true;

        if (self::get('_flash_clear')) {
            self::clearFlashes();
        }
        self::set('_flash_clear', true);
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        self::$started = false;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value === null) {
            $flash = $_SESSION['_flash'][$key] ?? null;
            return $flash;
        }
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    private static function clearFlashes(): void
    {
        unset($_SESSION['_flash']);
        unset($_SESSION['_old_input']);
        unset($_SESSION['_errors']);
        unset($_SESSION['_flash_clear']);
    }

    public static function errors(): array
    {
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);
        return $errors;
    }

    public static function hasErrors(): bool
    {
        return !empty($_SESSION['_errors']);
    }

    public static function setId(string $id): void
    {
        session_id($id);
    }

    public static function getId(): string
    {
        return session_id();
    }
}
