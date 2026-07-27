<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\Session;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\TenantManager;
use App\Core\View;
use App\Core\Database;
use App\Core\RateLimiter;
use App\Core\OtpManager;
use App\Models\User;
use App\Models\PasswordReset;
use App\Models\AuditLog;
use App\Models\SessionModel;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (AuthContext::check()) {
            $this->redirect('/');
        }
        View::setLayout('layouts/auth');
        $this->render('auth/login');
    }

    public function login(): void
    {
        $login = trim(Request::input('login'));
        $password = Request::input('password');
        $remember = Request::input('remember') === 'on';

        if (empty($login) || empty($password)) {
            flash('error', __('auth.invalid_credentials'));
            $this->back();
        }

        $ip = Request::ip();
        $rateLimiter = RateLimiter::forLogin($login, $ip);

        if ($rateLimiter->isLockedOut()) {
            $availableIn = $rateLimiter->availableIn();
            $minutes = ceil($availableIn / 60);
            flash('error', __('auth.too_many_attempts', ['minutes' => $minutes]));
            $this->back();
        }

        $user = null;
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $user = User::findByEmail($login);
        } elseif (preg_match('/^\+?[\d\-\(\)\s]+$/', $login)) {
            $phone = Security::sanitizePhone($login);
            $user = User::findByPhone($phone);
        } else {
            $user = User::findByUsername($login);
        }

        if (!$user || !Security::verifyPassword($password, $user['password'])) {
            $rateLimiter->hit();
            flash('error', __('auth.invalid_credentials'));
            $this->back();
        }

        if ($user['status'] === 'blocked' || $user['status'] === 'inactive'
            || (!$user['is_super_admin'] && $user['status'] !== 'active')) {
            $rateLimiter->hit();
            flash('error', __('auth.account_inactive'));
            $this->back();
        }

        $rateLimiter->clear();

        AuthContext::login($user);

        User::update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        SessionModel::createSession($user['id']);

        if ($remember) {
            $this->setRememberToken($user['id']);
        }

        if (!$user['is_super_admin']) {
            $shops = TenantManager::availableShops($user['id']);
            AuthContext::setShops($shops);

            if (count($shops) === 1) {
                AuthContext::setShop($shops[0]);
            }
        }

        AuditLog::log('auth.login', 'user', $user['id'], null, null, $user['id']);

        flash('success', __('auth.login_success'));
        $this->redirect('/');
    }

    public function registerForm(): void
    {
        if (AuthContext::check()) {
            $this->redirect('/');
        }
        View::setLayout('layouts/auth');
        $this->render('auth/register');
    }

    public function register(): void
    {
        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|phone',
            'password' => 'required|min:8|confirmed',
        ]);

        $data['password'] = Security::hashPassword($data['password']);
        $data['status'] = 'active';
        $data['username'] = null;

        $userId = User::create($data);

        $shopName = trim(Request::input('shop_name'));
        if (!empty($shopName)) {
            \App\Models\Shop::createWithOwner(['name' => $shopName], $userId);
        }

        AuditLog::log('user.registered', 'user', $userId, null, $data);

        flash('success', __('auth.register_success'));
        $this->redirect('/login');
    }

    public function logout(): void
    {
        $userId = AuthContext::id();

        $this->clearRememberToken();
        AuthContext::logout();

        if ($userId) {
            AuditLog::log('auth.logout', 'user', $userId, null, null, $userId);
        }

        flash('success', __('auth.logout_success'));
        $this->redirect('/login');
    }

    public function forgotPasswordForm(): void
    {
        if (AuthContext::check()) {
            $this->redirect('/');
        }
        View::setLayout('layouts/auth');
        $this->render('auth/forgot-password');
    }

    public function forgotPassword(): void
    {
        $login = trim(Request::input('login'));

        if (empty($login)) {
            flash('error', __('auth.invalid_credentials'));
            $this->back();
        }

        $rateLimiter = RateLimiter::forOtp($login);
        if ($rateLimiter->isLockedOut()) {
            $availableIn = $rateLimiter->availableIn();
            $minutes = ceil($availableIn / 60);
            flash('error', __('auth.too_many_attempts', ['minutes' => $minutes]));
            $this->back();
        }

        $user = null;
        $type = 'email';

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $user = User::findByEmail($login);
        } else {
            $phone = Security::sanitizePhone($login);
            $user = User::findByPhone($phone);
            $type = 'phone';
        }

        if (!$user) {
            flash('success', __('auth.reset_link_sent'));
            $this->back();
        }

        $tokenData = PasswordReset::createToken($user['id'], $type);

        if ($type === 'email') {
            $resetUrl = url('reset-password/' . $tokenData['raw_token'] . '?email=' . urlencode($user['email']));
            $subject = __('mail.reset_subject');
            $body = __('mail.reset_body', ['url' => $resetUrl]);
            \App\Core\Mailer::send($user['email'], $subject, $body);
        } else {
            $resetUrl = url('reset-password/' . $tokenData['raw_token'] . '?phone=' . urlencode($user['phone']));
            $message = __('mail.reset_sms', ['url' => $resetUrl]);
        }

        AuditLog::log('auth.password_reset_request', 'user', $user['id'], null, null, $user['id']);

        flash('success', __('auth.reset_link_sent'));
        $this->redirect('/login');
    }

    public function resetPasswordForm(): void
    {
        if (AuthContext::check()) {
            $this->redirect('/');
        }

        $token = Request::input('token');
        $email = Request::input('email');
        $phone = Request::input('phone');

        if (empty($token)) {
            flash('error', __('auth.invalid_reset_token'));
            $this->redirect('/login');
        }

        View::setLayout('layouts/auth');
        $this->render('auth/reset-password', [
            'token' => $token,
            'email' => $email,
            'phone' => $phone,
        ]);
    }

    public function resetPassword(): void
    {
        $token = Request::input('token');
        $email = Request::input('email');
        $phone = Request::input('phone');
        $password = Request::input('password');
        $passwordConfirmation = Request::input('password_confirmation');

        if (empty($token) || empty($password) || empty($passwordConfirmation)) {
            flash('error', __('auth.invalid_reset_token'));
            $this->back();
        }

        if ($password !== $passwordConfirmation) {
            flash('error', __('auth.confirm_password'));
            $this->back();
        }

        $strength = Security::validatePasswordStrength($password);
        if (!$strength['valid']) {
            flash('error', implode(' ', $strength['errors']));
            $this->back();
        }

        $user = null;
        if ($email) {
            $user = User::findByEmail($email);
        } elseif ($phone) {
            $user = User::findByPhone($phone);
        }

        if (!$user) {
            flash('error', __('auth.invalid_reset_token'));
            $this->redirect('/login');
        }

        $resetRecord = PasswordReset::findValid($user['id'], $token);
        if (!$resetRecord) {
            flash('error', __('auth.invalid_reset_token'));
            $this->redirect('/login');
        }

        PasswordReset::markUsed($resetRecord['id']);

        $hashedPassword = Security::hashPassword($password);
        User::update($user['id'], ['password' => $hashedPassword]);

        PasswordReset::expireOldTokens($user['id']);

        AuditLog::log('auth.password_reset_completed', 'user', $user['id'], null, null, $user['id']);

        flash('success', __('auth.password_updated'));
        $this->redirect('/login');
    }

    public function sendOtp(): void
    {
        $login = trim(Request::input('login'));

        if (empty($login)) {
            Response::json(['error' => __('auth.invalid_credentials')], 422);
        }

        $type = 'email';
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $user = User::findByEmail($login);
            $type = 'email';
        } else {
            $phone = Security::sanitizePhone($login);
            $user = User::findByPhone($phone);
            $type = 'phone';
        }

        if (!$user) {
            Response::json(['error' => __('auth.invalid_credentials')], 422);
        }

        if (!OtpManager::canSend($login, Request::ip())) {
            $lockoutTime = OtpManager::getLockoutTime($login);
            $minutes = ceil($lockoutTime / 60);
            Response::json([
                'error' => __('auth.otp_max_attempts', ['minutes' => $minutes]),
                'locked' => true,
                'lockout_seconds' => $lockoutTime,
            ], 429);
        }

        $otp = OtpManager::generate(OTP_LENGTH);

        $db = Database::getInstance();
        $otpHash = hash('sha256', $otp);
        $expiresAt = date('Y-m-d H:i:s', time() + (OTP_EXPIRY_MINUTES * 60));

        $db->insert('login_tokens', [
            'user_id' => $user['id'],
            'token' => $otpHash,
            'type' => $type === 'phone' ? 'phone' : 'email',
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        OtpManager::recordAttempt($login, Request::ip());

        OtpManager::send($login, $otp, $type);

        AuditLog::log('auth.otp_sent', 'user', $user['id'], null, ['type' => $type], $user['id']);

        $remaining = OtpManager::getRemainingAttempts($login);

        Response::json([
            'message' => __('auth.otp_sent'),
            'remaining_attempts' => $remaining,
            'expiry_minutes' => OTP_EXPIRY_MINUTES,
        ]);
    }

    public function verifyOtpForm(): void
    {
        View::setLayout('layouts/auth');
        $this->render('auth/verify-otp');
    }

    public function verifyOtp(): void
    {
        $phone = Request::input('phone');
        $otp = Request::input('otp');

        if (empty($phone) || empty($otp)) {
            flash('error', __('auth.invalid_otp'));
            $this->back();
        }

        $rateLimiter = RateLimiter::forOtp($phone);
        if ($rateLimiter->isLockedOut()) {
            $availableIn = $rateLimiter->availableIn();
            $minutes = ceil($availableIn / 60);
            flash('error', __('auth.otp_max_attempts', ['minutes' => $minutes]));
            $this->back();
        }

        $db = Database::getInstance();
        $otpHash = hash('sha256', $otp);

        $token = $db->fetch(
            "SELECT lt.*, u.status as user_status
             FROM login_tokens lt
             JOIN users u ON lt.user_id = u.id
             WHERE u.phone = :phone
             AND lt.token = :token
             AND lt.type = 'phone'
             AND lt.used_at IS NULL
             AND lt.expires_at > NOW()
             LIMIT 1",
            ['phone' => $phone, 'token' => $otpHash]
        );

        if (!$token) {
            $rateLimiter->hit();
            flash('error', __('auth.invalid_otp'));
            $this->back();
        }

        if ($token['user_status'] === 'blocked') {
            flash('error', __('auth.account_blocked'));
            $this->back();
        }

        if ($token['user_status'] === 'inactive') {
            flash('error', __('auth.account_inactive'));
            $this->back();
        }

        $rateLimiter->clear();

        $db->update('login_tokens', ['used_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $token['id']]);

        $user = User::find((int) $token['user_id']);
        AuthContext::login($user);

        $db->update('users', ['phone_verified_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $user['id']]);

        SessionModel::createSession($user['id']);

        if (!$user['is_super_admin']) {
            $shops = TenantManager::availableShops($user['id']);
            AuthContext::setShops($shops);

            if (count($shops) === 1) {
                AuthContext::setShop($shops[0]);
            }
        }

        OtpManager::clearAttempts($phone);

        AuditLog::log('auth.otp_verified', 'user', $user['id'], null, null, $user['id']);

        flash('success', __('auth.otp_verified'));
        $this->redirect('/');
    }

    public function acceptInvite(): void
    {
        $token = Request::input('token');

        if (empty($token)) {
            flash('error', __('auth.invite_invalid'));
            $this->redirect('/login');
        }

        $invitation = \App\Models\Invitation::findByToken($token);

        if (!$invitation) {
            flash('error', __('auth.invite_invalid'));
            $this->redirect('/login');
        }

        if (AuthContext::guest()) {
            Session::set('pending_invite_token', $token);
            $this->redirect('/register');
        }

        $user = AuthContext::user();

        $shopId = (int) $invitation['shop_id'];
        $role = $invitation['role'];
        $invitedBy = (int) $invitation['invited_by'];

        \App\Models\Shop::addUser($shopId, $user['id'], $role, $invitedBy);
        \App\Models\Invitation::accept((int) $invitation['id']);

        AuditLog::log('invitation.accepted', 'user', $user['id'], null, [
            'shop_id' => $shopId,
            'role' => $role,
        ], $user['id'], $shopId);

        flash('success', __('auth.invite_accepted'));
        $this->redirect('/');
    }

    private function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + (REMEMBER_TOKEN_LIFETIME_DAYS * 86400));

        $db = Database::getInstance();
        $db->insert('login_tokens', [
            'user_id' => $userId,
            'token' => hash('sha256', $token),
            'type' => 'remember',
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $cookieValue = $userId . ':' . $token;
        $cookieOptions = [
            'expires' => time() + (REMEMBER_TOKEN_LIFETIME_DAYS * 86400),
            'path' => '/',
            'domain' => '',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        if (PHP_VERSION_ID >= 70300) {
            setcookie('remember_token', $cookieValue, $cookieOptions);
        } else {
            setcookie('remember_token', $cookieValue, (int) $cookieOptions['expires'], $cookieOptions['path'], $cookieOptions['domain'], $cookieOptions['secure'], $cookieOptions['httponly']);
        }
    }

    private function clearRememberToken(): void
    {
        if (isset($_COOKIE['remember_token'])) {
            $cookieValue = $_COOKIE['remember_token'];
            $parts = explode(':', $cookieValue, 2);
            if (count($parts) === 2) {
                $userId = (int) $parts[0];
                $token = $parts[1];
                $hashedToken = hash('sha256', $token);

                $db = Database::getInstance();
                $db->query(
                    "UPDATE login_tokens SET used_at = NOW() WHERE user_id = :user_id AND token = :token AND type = 'remember' AND used_at IS NULL",
                    ['user_id' => $userId, 'token' => $hashedToken]
                );
            }

            setcookie('remember_token', '', time() - 3600, '/');
        }
    }

    public static function autoLoginFromCookie(): bool
    {
        if (AuthContext::check()) {
            return true;
        }

        if (!isset($_COOKIE['remember_token'])) {
            return false;
        }

        $cookieValue = $_COOKIE['remember_token'];
        $parts = explode(':', $cookieValue, 2);
        if (count($parts) !== 2) {
            return false;
        }

        $userId = (int) $parts[0];
        $token = $parts[1];
        $hashedToken = hash('sha256', $token);

        $db = Database::getInstance();
        $record = $db->fetch(
            "SELECT lt.*, u.*
             FROM login_tokens lt
             JOIN users u ON lt.user_id = u.id
             WHERE lt.user_id = :user_id
             AND lt.token = :token
             AND lt.type = 'remember'
             AND lt.used_at IS NULL
             AND lt.expires_at > NOW()
             LIMIT 1",
            ['user_id' => $userId, 'token' => $hashedToken]
        );

        if (!$record || $record['status'] !== 'active') {
            return false;
        }

        unset($record['password']);
        AuthContext::login($record);

        $db->update('login_tokens', ['used_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $record['id']]);

        return true;
    }
}
