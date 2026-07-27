<?php

define('APP_ROOT', __DIR__);

// Serve static files directly when using PHP's built-in server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($uri !== '/' && is_file(APP_ROOT . $uri)) {
    return false;
}

require_once APP_ROOT . '/vendor/autoload.php';

$envFile = APP_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            $pos = strpos($line, '=');
            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/config/permissions.php';

use App\Core\Database;
use App\Core\Session;
use App\Core\Router;

Database::getInstance();

Session::start();

require_once APP_ROOT . '/routes.php';

\App\Controllers\AuthController::autoLoginFromCookie();

Router::getInstance()->dispatch();
