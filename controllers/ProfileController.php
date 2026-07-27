<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\Security;
use App\Core\View;
use App\Core\Database;
use App\Models\User;
use App\Models\SessionModel;
use App\Models\AuditLog;

class ProfileController extends Controller
{
    public function index(): void
    {
        $user = AuthContext::user();
        if (!$user) {
            $this->redirect('/login');
        }

        $db = Database::getInstance();
        $activeSessions = $db->fetchAll(
            "SELECT * FROM sessions
             WHERE user_id = :user_id
             AND last_activity > DATE_SUB(NOW(), INTERVAL 24 HOUR)
             ORDER BY last_activity DESC",
            ['user_id' => $user['id']]
        );

        $this->render('profile/index', [
            'user' => $user,
            'active_sessions' => $activeSessions,
            'current_session_id' => session_id(),
        ]);
    }

    public function update(): void
    {
        $user = AuthContext::user();
        if (!$user) {
            $this->redirect('/login');
        }

        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email,' . $user['id'],
            'phone' => 'required|phone',
        ]);

        User::update($user['id'], $data);

        $updatedUser = User::find($user['id']);
        AuthContext::login($updatedUser);

        AuditLog::log('profile.updated', 'user', $user['id'], null, $data, $user['id']);

        flash('success', __('profile.updated'));
        $this->redirect('/profile');
    }

    public function changePassword(): void
    {
        $user = AuthContext::user();
        if (!$user) {
            $this->redirect('/login');
        }

        $currentPassword = Request::input('current_password');
        $newPassword = Request::input('new_password');
        $confirmPassword = Request::input('new_password_confirmation');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            flash('error', __('form.required'));
            $this->back();
        }

        if (!Security::verifyPassword($currentPassword, $user['password'])) {
            flash('error', __('profile.wrong_password'));
            $this->back();
        }

        if ($newPassword !== $confirmPassword) {
            flash('error', __('auth.confirm_password'));
            $this->back();
        }

        $strength = Security::validatePasswordStrength($newPassword);
        if (!$strength['valid']) {
            flash('error', implode(' ', $strength['errors']));
            $this->back();
        }

        User::update($user['id'], ['password' => Security::hashPassword($newPassword)]);

        AuditLog::log('profile.password_changed', 'user', $user['id'], null, null, $user['id']);

        flash('success', __('profile.password_changed'));
        $this->redirect('/profile');
    }

    public function terminateSession(): void
    {
        $user = AuthContext::user();
        if (!$user) {
            $this->redirect('/login');
        }

        $sessionId = Request::input('session_id');
        if (empty($sessionId)) {
            $this->back();
        }

        $db = Database::getInstance();
        $session = $db->fetch(
            "SELECT id FROM sessions WHERE id = :id AND user_id = :user_id",
            ['id' => $sessionId, 'user_id' => $user['id']]
        );

        if ($session && $sessionId !== session_id()) {
            SessionModel::terminateSession($sessionId);
        }

        AuditLog::log('profile.session_terminated', 'session', null, null, null, $user['id']);

        flash('success', __('profile.session_terminated'));
        $this->back();
    }

    public function terminateAllSessions(): void
    {
        $user = AuthContext::user();
        if (!$user) {
            $this->redirect('/login');
        }

        SessionModel::terminateAllUserSessions($user['id'], session_id());

        AuditLog::log('profile.all_sessions_terminated', 'user', $user['id'], null, null, $user['id']);

        flash('success', __('profile.logout_others_success'));
        $this->back();
    }
}
