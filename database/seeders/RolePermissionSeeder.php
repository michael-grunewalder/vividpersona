<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Global role definitions. Permissions are identical for a role across
     * every team; only the assignment is team-scoped.
     *
     * @var array<string, array<int, string>>
     */
    private const ROLE_PERMISSIONS = [
        'owner' => [
            'team.update',
            'team.delete',
            'team.manage-members',
            'team.assign-roles',
            'content.view',
            'content.create',
            'content.update',
            'content.delete',
            'content.publish',
            'billing.manage',
        ],
        'admin' => [
            'team.update',
            'team.manage-members',
            'team.assign-roles',
            'content.view',
            'content.create',
            'content.update',
            'content.delete',
            'content.publish',
        ],
        'editor' => [
            'content.view',
            'content.create',
            'content.update',
            'content.delete',
            'content.publish',
        ],
        'viewer' => [
            'content.view',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = collect(self::ROLE_PERMISSIONS)->flatten()->unique()->values();

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::ROLE_PERMISSIONS as $role => $rolePermissions) {
            Role::findOrCreate($role, 'web')->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
