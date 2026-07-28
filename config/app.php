<?php

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

define('APP_NAME', env('APP_NAME', 'RepDocNetwork'));
define('APP_URL', env('APP_URL', 'http://localhost:8000'));
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_DEBUG', env('APP_DEBUG', 'false') === 'true');

define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 120));
define('CSRF_EXPIRY', (int) env('CSRF_EXPIRY', 7200));

define('MAX_LOGIN_ATTEMPTS', (int) env('MAX_LOGIN_ATTEMPTS', 5));
define('LOCKOUT_MINUTES', (int) env('LOCKOUT_MINUTES', 15));
define('LOGIN_DECAY_MINUTES', (int) env('LOGIN_DECAY_MINUTES', 15));

define('OTP_LENGTH', (int) env('OTP_LENGTH', 6));
define('OTP_EXPIRY_MINUTES', (int) env('OTP_EXPIRY_MINUTES', 5));
define('OTP_MAX_ATTEMPTS', (int) env('OTP_MAX_ATTEMPTS', 3));
define('OTP_DECAY_MINUTES', (int) env('OTP_DECAY_MINUTES', 15));
define('OTP_LOCKOUT_MINUTES', (int) env('OTP_LOCKOUT_MINUTES', 15));

define('REMEMBER_TOKEN_LIFETIME_DAYS', (int) env('REMEMBER_TOKEN_LIFETIME_DAYS', 30));

define('TIMEZONE', 'Asia/Dhaka');
define('LOCALE', 'bn_BD');

date_default_timezone_set(TIMEZONE);
setlocale(LC_ALL, LOCALE . '.utf8');
