<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Reset cached roles and permissions
         */
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        /**
         * Roles
         */
        Role::updateOrCreate(['name' => 'superadmin']);
        $teamMember = Role::updateOrCreate(['name' => 'team_member']);
        $observer = Role::updateOrCreate(['name' => 'observer']);

        /**
         * Create permissions
         */
        $permissions = [
            // team members permissions
            'access team members',
            'create team members',
            'view team members',
            'update team members',
            'delete team members',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission]);
        }

        // update cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        /**
         * Assign permissions to roles
         */
        $teamMember->givePermissionTo([
            'access team members',
            'view team members',
        ]);

        $observer->givePermissionTo([
            'access team members',
            'view team members',
        ]);
    }
}
