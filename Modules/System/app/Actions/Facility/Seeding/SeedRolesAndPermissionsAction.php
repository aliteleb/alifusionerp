<?php

namespace Modules\System\Actions\Facility\Seeding;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SeedRolesAndPermissionsAction
{
    public function execute(): void
    {
        try {
            Log::info('Starting roles and permissions seeding');

            // Reset cached roles and permissions
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            // First create all permissions
            $createdPermissions = $this->createPermissions();

            // Clear cache again after creating permissions
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            // Then create roles and assign permissions
            $this->createRoles($createdPermissions);

            // Final cache clear
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            Log::info('Completed roles and permissions seeding');

        } catch (Exception $e) {
            // Log the error for debugging
            Log::error('Error seeding roles and permissions: '.$e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw the exception
            throw $e;
        }
    }

    /**
     * Create or update a permission with proper error handling for unique constraints.
     */
    private function createOrUpdatePermission(string $permissionName, ?string $group = null): Permission
    {
        try {
            // First try to find the permission
            $permission = Permission::where('name', $permissionName)
                ->where('guard_name', 'web')
                ->first();

            if ($permission) {
                // Update the group if provided
                if ($group) {
                    $permission->group = $group;
                    $permission->save();
                }

                return $permission;
            }

            // If not found, create new permission
            $permission = Permission::create([
                'name' => $permissionName,
                'guard_name' => 'web',
                'group' => $group,
            ]);

            return $permission;
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if (str_contains($e->getMessage(), 'unique constraint') ||
                str_contains($e->getMessage(), 'Duplicate entry')) {
                // Permission was created by another process, fetch it
                $permission = Permission::where('name', $permissionName)
                    ->where('guard_name', 'web')
                    ->first();

                if ($permission) {
                    // Update the group if provided
                    if ($group) {
                        $permission->group = $group;
                        $permission->save();
                    }

                    return $permission;
                }
            }

            // Re-throw if it's a different error
            throw $e;
        }
    }

    private function createPermissions(): array
    {
        Log::info('Creating permissions');

        // Define actions for permissions
        $actions = ['access', 'view', 'create', 'edit', 'delete'];

        // Create permissions for all Filament resources organized by navigation groups
        $resourcesByGroup = [
            'Organization' => [
                'branches',
                'departments',
            ],
            'Administration' => [
                'users',
                'roles',
                'activity_logs',
            ],
            'Reference Data' => [
                'countries',
                'currencies',
                'genders',
                'marital_statuses',
                'nationalities',
            ],
            'System & Settings' => [
                'settings',
                'rules',
            ],
        ];

        $permissions = [];

        // Create simple "access_resource" permissions
        foreach ($resourcesByGroup as $group => $resources) {
            foreach ($resources as $resource) {
                foreach ($actions as $action) {
                    $permissionName = "{$action}_{$resource}";
                    $permission = $this->createOrUpdatePermission($permissionName, $group);
                    $permissions[] = $permissionName;
                }
            }
        }

        // Custom permissions
        $customPermissions = [
            'view_all_branches',
            'manage_system_settings',
        ];

        // Labels only for allowing sync translation to detect them to add them to the translation files
        $labels = [
            // Organization Resources
            __('Branches'), __('Access Branches'), __('View Branches'), __('Create Branches'), __('Edit Branches'), __('Delete Branches'),
            __('Departments'), __('Access Departments'), __('View Departments'), __('Create Departments'), __('Edit Departments'), __('Delete Departments'),

            // Administration Resources
            __('Users'), __('Access Users'), __('View Users'), __('Create Users'), __('Edit Users'), __('Delete Users'),
            __('Roles'), __('Access Roles'), __('View Roles'), __('Create Roles'), __('Edit Roles'), __('Delete Roles'),
            __('Activity Logs'), __('Access Activity Logs'), __('View Activity Logs'), __('Create Activity Logs'), __('Edit Activity Logs'), __('Delete Activity Logs'),

            // Reference Data Resources
            __('Countries'), __('Access Countries'), __('View Countries'), __('Create Countries'), __('Edit Countries'), __('Delete Countries'),
            __('Currencies'), __('Access Currencies'), __('View Currencies'), __('Create Currencies'), __('Edit Currencies'), __('Delete Currencies'),
            __('Genders'), __('Access Genders'), __('View Genders'), __('Create Genders'), __('Edit Genders'), __('Delete Genders'),
            __('Marital Statuses'), __('Access Marital Statuses'), __('View Marital Statuses'), __('Create Marital Statuses'), __('Edit Marital Statuses'), __('Delete Marital Statuses'),
            __('Nationalities'), __('Access Nationalities'), __('View Nationalities'), __('Create Nationalities'), __('Edit Nationalities'), __('Delete Nationalities'),

            // System & Settings
            __('Settings'), __('Access Settings'), __('View Settings'), __('Create Settings'), __('Edit Settings'), __('Delete Settings'),
            __('Rules'), __('Access Rules'), __('View Rules'), __('Create Rules'), __('Edit Rules'), __('Delete Rules'),

            // Custom Permissions
            __('View All Branches'), __('Manage System Settings'),
        ];
        foreach ($customPermissions as $permissionName) {
            $permission = $this->createOrUpdatePermission($permissionName, 'Custom');
            $permissions[] = $permissionName;
        }

        Log::info('Created '.count($permissions).' permissions');

        return $permissions;
    }

    /**
     * Create or update roles and sync the provided permissions.
     *
     * @param  array<int, string>  $allPermissions
     */
    private function createRoles(array $allPermissions): void
    {
        Log::info('Creating roles');

        // Create admin role and give it all permissions
        $adminRole = $this->createOrUpdateRole('SuperAdmin', 'Super Administrator', 'System');

        // Only sync permissions that actually exist
        $existingPermissions = Permission::whereIn('name', $allPermissions)->pluck('name')->toArray();
        $adminRole->syncPermissions($existingPermissions);

        // Create supervisor role with comprehensive permissions
        $supervisorPermissions = [
            // Organization Resources
            'access_branches', 'view_branches', 'create_branches', 'edit_branches',
            'access_departments', 'view_departments', 'create_departments', 'edit_departments',

            // Administration (limited)
            'access_users', 'view_users', 'create_users', 'edit_users',
            'access_roles', 'view_roles',
            'access_activity_logs', 'view_activity_logs',

            // Reference Data
            'access_countries', 'view_countries', 'create_countries', 'edit_countries',
            'access_currencies', 'view_currencies', 'create_currencies', 'edit_currencies',
            'access_genders', 'view_genders', 'create_genders', 'edit_genders',
            'access_marital_statuses', 'view_marital_statuses', 'create_marital_statuses', 'edit_marital_statuses',
            'access_nationalities', 'view_nationalities', 'create_nationalities', 'edit_nationalities',

            // Custom Permissions
            'view_all_branches',
        ];

        $supervisorRole = $this->createOrUpdateRole('Supervisor', 'Supervisor', 'Management');

        // Only sync permissions that actually exist and are in our created list
        $existingSupervisorPermissions = Permission::whereIn('name', $supervisorPermissions)
            ->whereIn('name', $allPermissions)
            ->pluck('name')
            ->toArray();

        if (! empty($existingSupervisorPermissions)) {
            $supervisorRole->syncPermissions($existingSupervisorPermissions);
        }

        // Create participant role with basic permissions
        $participantPermissions = [
            // Organization (view only)
            'access_branches', 'view_branches',
            'access_departments', 'view_departments',

            // Reference Data (view only)
            'access_countries', 'view_countries',
            'access_currencies', 'view_currencies',
            'access_genders', 'view_genders',
            'access_marital_statuses', 'view_marital_statuses',
            'access_nationalities', 'view_nationalities',
        ];

        $participantRole = $this->createOrUpdateRole('Participant', 'Participant', 'General');

        // Only sync permissions that actually exist and are in our created list
        $existingParticipantPermissions = Permission::whereIn('name', $participantPermissions)
            ->whereIn('name', $allPermissions)
            ->pluck('name')
            ->toArray();

        if (! empty($existingParticipantPermissions)) {
            $participantRole->syncPermissions($existingParticipantPermissions);
        }

        Log::info('Created roles: SuperAdmin, Supervisor, Participant');
    }

    /**
     * Create or update a role with proper error handling for unique constraints.
     */
    private function createOrUpdateRole(string $roleName, string $displayName, string $group): Role
    {
        try {
            // First try to find the role
            $role = Role::where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if ($role) {
                // Update the role attributes
                $role->display_name = $displayName;
                $role->group = $group;
                $role->save();

                return $role;
            }

            // If not found, create new role
            $role = Role::create([
                'name' => $roleName,
                'guard_name' => 'web',
                'display_name' => $displayName,
                'group' => $group,
            ]);

            return $role;
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if (str_contains($e->getMessage(), 'unique constraint') ||
                str_contains($e->getMessage(), 'Duplicate entry')) {
                // Role was created by another process, fetch it
                $role = Role::where('name', $roleName)
                    ->where('guard_name', 'web')
                    ->first();

                if ($role) {
                    // Update the role attributes
                    $role->display_name = $displayName;
                    $role->group = $group;
                    $role->save();

                    return $role;
                }
            }

            // Re-throw if it's a different error
            throw $e;
        }
    }
}
