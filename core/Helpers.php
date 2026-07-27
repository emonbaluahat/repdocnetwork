<?php

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);
        if ($value === false || $value === null) {
            return $default;
        }
        return $value;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $file = $keys[0];
        $configPath = APP_ROOT . '/config/' . $file . '.php';

        if (!file_exists($configPath)) {
            return $default;
        }

        static $configs = [];
        if (!isset($configs[$file])) {
            $configs[$file] = require $configPath;
        }

        $value = $configs[$file];
        for ($i = 1; $i < count($keys); $i++) {
            if (!isset($value[$keys[$i]])) {
                return $default;
            }
            $value = $value[$keys[$i]];
        }

        return $value;
    }
}

if (!function_exists('__')) {
    function __(string $key, array $replacements = [], ?string $locale = null): string
    {
        static $translations = [];

        $locale = $locale ?: ($_SESSION['lang'] ?? 'bn');
        $langFile = APP_ROOT . '/config/lang/' . $locale . '.php';

        if (!isset($translations[$locale])) {
            if (file_exists($langFile)) {
                $translations[$locale] = require $langFile;
            } else {
                $translations[$locale] = [];
            }
        }

        $text = $translations[$locale][$key] ?? $key;

        foreach ($replacements as $search => $replace) {
            $text = str_replace('{' . $search . '}', $replace, $text);
        }

        return $text;
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \App\Core\CSRF::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('method_field')) {
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . e($method) . '">';
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (!function_exists('session')) {
    function session(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_SESSION;
        }
        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null): ?string
    {
        if ($message === null) {
            if (isset($_SESSION['_flash'][$key])) {
                $msg = $_SESSION['_flash'][$key];
                unset($_SESSION['_flash'][$key]);
                return $msg;
            }
            return null;
        }
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('abort')) {
    function abort(int $code = 404, string $message = 'Not Found'): void
    {
        http_response_code($code);
        echo '<h1>' . e($code) . ' - ' . e($message) . '</h1>';
        exit;
    }
}

if (!function_exists('is_post')) {
    function is_post(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}

if (!function_exists('is_ajax')) {
    function is_ajax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

if (!function_exists('slugify')) {
    function slugify(string $text): string
    {
        $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');
        return mb_strtolower($text, 'UTF-8');
    }
}

if (!function_exists('generate_reference')) {
    function generate_reference(string $prefix = 'DOC'): string
    {
        return $prefix . '-' . date('Y') . '-' . date('m') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }
}

if (!function_exists('format_date')) {
    function format_date(string $date, string $format = 'd M Y'): string
    {
        $timestamp = strtotime($date);
        return $timestamp ? date($format, $timestamp) : $date;
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(string $date): string
    {
        return format_date($date, 'd M Y, h:i A');
    }
}

if (!function_exists('truncate')) {
    function truncate(string $text, int $length = 100): string
    {
        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length, 'UTF-8') . '...';
    }
}

if (!function_exists('json_response')) {
    function json_response(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('logger')) {
    function logger(string $message, string $level = 'INFO', ?array $context = null): void
    {
        $logDir = APP_ROOT . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/app-' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $line = "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;

        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
