<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\Request;
use App\Core\View;
use App\Core\Database;
use App\Models\Permission;
use App\Models\AuditLog;

class PermissionController extends Controller
{
    public function index(): void
    {
        $permissions = Permission::getAllGrouped();

        $db = Database::getInstance();
        $rolePermissions = $db->fetchAll(
            "SELECT rp.role, rp.permission_id FROM role_permission rp"
        );

        $rolePermMap = [];
        foreach ($rolePermissions as $rp) {
            $rolePermMap[$rp['role']][] = (int) $rp['permission_id'];
        }

        $roles = [ROLE_OWNER, ROLE_ADMIN, ROLE_OPERATOR, ROLE_CUSTOMER];

        $this->render('admin/permissions', [
            'grouped_permissions' => $permissions,
            'role_perm_map' => $rolePermMap,
            'roles' => $roles,
        ]);
    }

    public function update(): void
    {
        $rolePermissions = Request::input('permissions', []);

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
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

            $db->commit();

            Permission::loadPermissionsToGlobal();

            AuditLog::log('permissions.updated', 'permission', null, null, [
                'roles' => array_keys($rolePermissions),
            ]);

            flash('success', __('permission.updated'));
        } catch (\Exception $e) {
            $db->rollback();
            flash('error', __('error.server_error'));
        }

        $this->redirect('/admin/permissions');
    }
}
