<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\Request;
use App\Core\Security;
use App\Core\View;
use App\Core\Database;
use App\Models\User;
use App\Models\Shop;
use App\Models\Invitation;
use App\Models\Permission;
use App\Models\AuditLog;

class StaffController extends Controller
{
    public function index(): void
    {
        if (!AuthContext::isStaffManager()) {
            flash('error', __('auth.unauthorized'));
            $this->redirect('/');
        }
        $shopId = AuthContext::shopId();
        $shop = Shop::find($shopId);

        $db = Database::getInstance();
        $staff = $db->fetchAll(
            "SELECT u.*, su.role, su.joined_at, su.invited_by,
                    inviter.name as invited_by_name
             FROM shop_user su
             JOIN users u ON su.user_id = u.id
             LEFT JOIN users inviter ON su.invited_by = inviter.id
             WHERE su.shop_id = :shop_id AND su.is_active = 1
             ORDER BY su.joined_at ASC",
            ['shop_id' => $shopId]
        );

        $invitations = Invitation::getByShop($shopId, 'pending');

        $this->render('settings/staff', [
            'shop' => $shop,
            'staff' => $staff,
            'invitations' => $invitations,
        ]);
    }

    public function inviteForm(): void
    {
        $this->authorize('invite_staff');
        $shopId = AuthContext::shopId();
        $shop = Shop::find($shopId);

        $this->render('settings/staff-invite', [
            'shop' => $shop,
        ]);
    }

    public function invite(): void
    {
        $this->authorize('invite_staff');
        $shopId = AuthContext::shopId();
        $userId = AuthContext::id();

        $email = trim(Request::input('email'));
        $phone = trim(Request::input('phone'));
        $role = Request::input('role');

        if (empty($email) && empty($phone)) {
            flash('error', __('staff.require_contact'));
            $this->back();
        }

        $validRoles = [ROLE_ADMIN, ROLE_OPERATOR, ROLE_CUSTOMER];
        if (!in_array($role, $validRoles)) {
            $role = ROLE_OPERATOR;
        }

        $invitation = Invitation::createInvitation($shopId, $userId, $role, $email, $phone);

        $existingUser = null;
        if ($email) {
            $existingUser = User::findByEmail($email);
        } elseif ($phone) {
            $cleanPhone = Security::sanitizePhone($phone ?? '');
            $existingUser = User::findByPhone($cleanPhone);
        }

        if ($existingUser) {
            $hasAccess = User::hasAccessToShop($existingUser['id'], $shopId);
            if ($hasAccess) {
                Invitation::decline((int) $invitation['id']);
                flash('error', __('staff.already_member'));
                $this->back();
            }

            Shop::addUser($shopId, $existingUser['id'], $role, $userId);
            Invitation::accept((int) $invitation['id']);

            AuditLog::log('staff.added', 'user', $existingUser['id'], null, [
                'shop_id' => $shopId,
                'role' => $role,
            ], $userId, $shopId);

            flash('success', __('staff.invited'));
            $this->redirect('/staff');
        }

        if ($email) {
            $inviteUrl = url('accept-invite?token=' . $invitation['raw_token']);
            $shopName = Shop::find($shopId)['name'] ?? '';
            \App\Core\Mailer::sendInvite($email, $shopName, $inviteUrl);
        }

        AuditLog::log('staff.invited', 'invitation', $invitation['id'], null, [
            'shop_id' => $shopId,
            'role' => $role,
            'email' => $email,
            'phone' => $phone,
        ], $userId, $shopId);

        flash('success', __('staff.invited'));
        $this->redirect('/staff');
    }

    public function remove(int $id): void
    {
        $this->authorize('invite_staff');
        $shopId = AuthContext::shopId();
        $userId = AuthContext::id();

        if ($id === $userId) {
            flash('error', __('staff.cannot_remove_self'));
            $this->back();
        }

        $shop = Shop::find($shopId);
        if ((int) $shop['owner_id'] === $id) {
            flash('error', __('staff.cannot_remove_owner'));
            $this->back();
        }

        Shop::removeUser($shopId, $id);

        AuditLog::log('staff.removed', 'user', $id, null, ['shop_id' => $shopId], $userId, $shopId);

        flash('success', __('staff.removed'));
        $this->back();
    }

    public function changeRole(int $id): void
    {
        $this->authorize('manage_operators');
        $shopId = AuthContext::shopId();
        $userId = AuthContext::id();

        if ($id === $userId) {
            flash('error', __('staff.cannot_change_own_role'));
            $this->back();
        }

        $newRole = Request::input('role');
        $validRoles = [ROLE_ADMIN, ROLE_OPERATOR, ROLE_CUSTOMER];
        if (!in_array($newRole, $validRoles)) {
            flash('error', __('staff.invalid_role'));
            $this->back();
        }

        $db = Database::getInstance();
        $db->update(
            'shop_user',
            ['role' => $newRole],
            'user_id = :user_id AND shop_id = :shop_id',
            ['user_id' => $id, 'shop_id' => $shopId]
        );

        AuditLog::log('staff.role_changed', 'user', $id, null, [
            'shop_id' => $shopId,
            'new_role' => $newRole,
        ], $userId, $shopId);

        flash('success', __('staff.role_updated'));
        $this->back();
    }

    public function permissions(): void
    {
        $this->authorize('manage_staff_permissions');
        $shopId = AuthContext::shopId();
        $shop = Shop::find($shopId);

        $permissions = Permission::getAllGrouped();
        $db = Database::getInstance();
        $rolePermissions = $db->fetchAll(
            "SELECT rp.role, rp.permission_id FROM role_permission rp"
        );

        $rolePermMap = [];
        foreach ($rolePermissions as $rp) {
            $rolePermMap[$rp['role']][] = $rp['permission_id'];
        }

        $roles = [ROLE_OWNER, ROLE_ADMIN, ROLE_OPERATOR, ROLE_CUSTOMER];

        $this->render('settings/permissions', [
            'grouped_permissions' => $permissions,
            'role_perm_map' => $rolePermMap,
            'roles' => $roles,
            'shop' => $shop,
        ]);
    }

    public function updatePermissions(): void
    {
        $this->authorize('manage_staff_permissions');
        $shopId = AuthContext::shopId();
        $userId = AuthContext::id();

        $rolePermissions = Request::input('permissions', []);
        $db = Database::getInstance();

        foreach ($rolePermissions as $role => $permissionIds) {
            $validRoles = [ROLE_OWNER, ROLE_ADMIN, ROLE_OPERATOR, ROLE_CUSTOMER];
            if (!in_array($role, $validRoles)) {
                continue;
            }

            $db->delete('role_permission', 'role = :role', ['role' => $role]);

            foreach ($permissionIds as $permId) {
                $db->insert('role_permission', [
                    'role' => $role,
                    'permission_id' => (int) $permId,
                ]);
            }
        }

        AuditLog::log('staff.permissions_updated', 'permission', null, null, [
            'shop_id' => $shopId,
            'roles' => array_keys($rolePermissions),
        ], $userId, $shopId);

        Permission::loadPermissionsToGlobal();

        flash('success', __('permission.updated'));
        $this->redirect('/staff/permissions');
    }
}
