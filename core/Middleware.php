<?php

namespace App\Core;

class Middleware
{
    public static function auth(): void
    {
        if (!AuthContext::check()) {
            flash('error', __('auth.login_required'));
            Response::redirect('/login');
        }
    }

    public static function guest(): void
    {
        if (AuthContext::check()) {
            Response::redirect('/');
        }
    }

    public static function csrf(): void
    {
        $method = Request::method();
        if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
            $token = Request::csrfToken();
            if (!CSRF::validate($token)) {
                if (Request::isAjax()) {
                    Response::json(['error' => 'Invalid CSRF token.'], 419);
                }
                flash('error', 'Session expired. Please try again.');
                Response::redirect('/login');
            }
        }
    }

    public static function shopScope(): void
    {
        if (!AuthContext::hasShop()) {
            if (Request::isAjax()) {
                Response::json(['error' => 'No active shop.'], 403);
            }
            flash('error', __('shop.not_found'));
            Response::redirect('/');
        }
    }

    public static function owner(): void
    {
        if (AuthContext::role() !== ROLE_OWNER) {
            if (Request::isAjax()) {
                Response::json(['error' => 'Unauthorized.'], 403);
            }
            flash('error', __('auth.unauthorized'));
            Response::redirect('/');
        }
    }

    public static function admin(): void
    {
        $role = AuthContext::role();
        if (!in_array($role, [ROLE_OWNER, ROLE_ADMIN])) {
            if (Request::isAjax()) {
                Response::json(['error' => 'Unauthorized.'], 403);
            }
            flash('error', __('auth.unauthorized'));
            Response::redirect('/');
        }
    }

    public static function superAdmin(): void
    {
        if (!AuthContext::isSuperAdmin()) {
            if (Request::isAjax()) {
                Response::json(['error' => 'Unauthorized.'], 403);
            }
            flash('error', __('auth.unauthorized'));
            Response::redirect('/');
        }
    }

    public static function role(string $role): void
    {
        $userRole = AuthContext::role();
        if ($userRole !== $role && !AuthContext::isSuperAdmin()) {
            if (Request::isAjax()) {
                Response::json(['error' => 'Unauthorized.'], 403);
            }
            flash('error', __('auth.unauthorized'));
            Response::redirect('/');
        }
    }

    public static function anyRole(array $roles): void
    {
        $userRole = AuthContext::role();
        if (!in_array($userRole, $roles) && !AuthContext::isSuperAdmin()) {
            if (Request::isAjax()) {
                Response::json(['error' => 'Unauthorized.'], 403);
            }
            flash('error', __('auth.unauthorized'));
            Response::redirect('/');
        }
    }

    public static function permission(string $permission): void
    {
        if (!AuthContext::hasPermission($permission)) {
            if (Request::isAjax()) {
                Response::json(['error' => 'Permission denied.'], 403);
            }
            flash('error', __('auth.permission_denied'));
            Response::redirect('/');
        }
    }

    public static function anyPermission(array $permissions): void
    {
        foreach ($permissions as $perm) {
            if (AuthContext::hasPermission($perm)) {
                return;
            }
        }

        if (Request::isAjax()) {
            Response::json(['error' => 'Permission denied.'], 403);
        }
        flash('error', __('auth.permission_denied'));
        Response::redirect('/');
    }
}
