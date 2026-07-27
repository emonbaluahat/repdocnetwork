<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\Request;
use App\Core\Security;
use App\Core\View;
use App\Core\Database;
use App\Models\User;
use App\Models\Permission;
use App\Models\AuditLog;

class AdminController extends Controller
{
    public function users(): void
    {
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $role = trim($_GET['role'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;

        $db = Database::getInstance();
        $sql = "SELECT u.*, GROUP_CONCAT(DISTINCT su.shop_id) as shop_ids,
                GROUP_CONCAT(DISTINCT su.role) as shop_roles
                FROM users u
                LEFT JOIN shop_user su ON u.id = su.user_id AND su.is_active = 1";
        $countSql = "SELECT COUNT(DISTINCT u.id) as count FROM users u
                     LEFT JOIN shop_user su ON u.id = su.user_id AND su.is_active = 1";
        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(u.name LIKE :search OR u.email LIKE :search2 OR u.phone LIKE :search3)";
            $params['search'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
        }

        if ($status) {
            $where[] = "u.status = :status";
            $params['status'] = $status;
        }

        if ($role) {
            $where[] = "su.role = :role";
            $params['role'] = $role;
        }

        $whereClause = '';
        if (!empty($where)) {
            $whereClause = ' WHERE ' . implode(' AND ', $where);
        }

        $totalResult = $db->fetch($countSql . $whereClause, $params);
        $total = (int) ($totalResult['count'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql .= $whereClause . " GROUP BY u.id ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $users = $db->fetchAll($sql, $params);

        $this->render('admin/users', [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'search' => $search,
            'status' => $status,
            'role' => $role,
        ]);
    }

    public function userDetail(int $id): void
    {
        $user = User::find($id);
        if (!$user) {
            $this->redirect('/admin/users');
        }

        $db = Database::getInstance();
        $shops = $db->fetchAll(
            "SELECT s.*, su.role, su.joined_at FROM shops s
             JOIN shop_user su ON s.id = su.shop_id
             WHERE su.user_id = :user_id AND su.is_active = 1
             ORDER BY s.name",
            ['user_id' => $id]
        );

        $auditLogs = AuditLog::getByUser($id, 20);

        $this->render('admin/user-detail', [
            'user' => $user,
            'shops' => $shops,
            'audit_logs' => $auditLogs,
        ]);
    }

    public function toggleStatus(int $id): void
    {
        $user = User::find($id);
        if (!$user) {
            $this->redirect('/admin/users');
        }

        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';

        User::update($id, ['status' => $newStatus]);

        AuditLog::log(
            'admin.user_status_changed',
            'user',
            $id,
            ['status' => $user['status']],
            ['status' => $newStatus]
        );

        $message = $newStatus === 'active' ? __('admin.user_activated') : __('admin.user_deactivated');
        flash('success', $message);
        $this->redirect('/admin/users/' . $id);
    }

    public function changeRole(int $id): void
    {
        $user = User::find($id);
        if (!$user) {
            $this->redirect('/admin/users');
        }

        $shopId = (int) Request::input('shop_id');
        $newRole = Request::input('role');

        $validRoles = [ROLE_OWNER, ROLE_ADMIN, ROLE_OPERATOR, ROLE_CUSTOMER];
        if (!in_array($newRole, $validRoles)) {
            flash('error', __('admin.invalid_role'));
            $this->back();
        }

        $db = Database::getInstance();
        $db->update(
            'shop_user',
            ['role' => $newRole],
            'user_id = :user_id AND shop_id = :shop_id',
            ['user_id' => $id, 'shop_id' => $shopId]
        );

        AuditLog::log(
            'admin.user_role_changed',
            'user',
            $id,
            ['shop_id' => $shopId, 'role' => $user['role'] ?? null],
            ['shop_id' => $shopId, 'role' => $newRole]
        );

        flash('success', __('admin.role_updated'));
        $this->redirect('/admin/users/' . $id);
    }

    public function resetPassword(int $id): void
    {
        $user = User::find($id);
        if (!$user) {
            $this->redirect('/admin/users');
        }

        $newPassword = Security::generateStrongPassword();
        $hashedPassword = Security::hashPassword($newPassword);

        User::update($id, ['password' => $hashedPassword]);

        AuditLog::log('admin.user_password_reset', 'user', $id);

        $this->redirect('/admin/users/' . $id);
    }

    public function permissions(): void
    {
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

        $this->render('admin/permissions', [
            'grouped_permissions' => $permissions,
            'role_perm_map' => $rolePermMap,
            'roles' => $roles,
        ]);
    }

    public function updatePermissions(): void
    {
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

        AuditLog::log('admin.permissions_updated', 'permission', null, null, ['roles' => array_keys($rolePermissions)]);

        Permission::loadPermissionsToGlobal();

        flash('success', __('permission.updated'));
        $this->redirect('/admin/permissions');
    }

    public function auditLogs(): void
    {
        $filters = [
            'action' => Request::input('action'),
            'entity_type' => Request::input('entity_type'),
            'user_id' => Request::input('user_id') ? (int) Request::input('user_id') : null,
            'shop_id' => Request::input('shop_id') ? (int) Request::input('shop_id') : null,
            'date_from' => Request::input('date_from'),
            'date_to' => Request::input('date_to'),
        ];

        $page = max(1, (int) (Request::input('page') ?? 1));
        $perPage = 50;

        $results = AuditLog::search(array_filter($filters), $page, $perPage);

        $this->render('admin/audit-logs', [
            'logs' => $results['items'],
            'total' => $results['total'],
            'page' => $results['page'],
            'per_page' => $results['per_page'],
            'total_pages' => $results['total_pages'],
            'filters' => $filters,
        ]);
    }
}
