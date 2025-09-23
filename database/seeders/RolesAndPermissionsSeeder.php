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
        $directProduction = Role::updateOrCreate(['name' => 'direct_production']);
        $indirectProduction = Role::updateOrCreate(['name' => 'indirect_production']);
        $callCenter = Role::updateOrCreate(['name' => 'call_center']);
        $consultant = Role::updateOrCreate(['name' => 'consultant']);
        $external = Role::updateOrCreate(['name' => 'external']);
        $observer = Role::updateOrCreate(['name' => 'observer']);

        /**
         * Create permissions
         */
        $permissions = [
            // dashboard permissions
            'access dashboard',
            // profile permissions
            'view profile',
            'update profile',
            // calendar and events permissions
            'access calendar',
            'access events',
            'create events',
            'view events',
            'view all events',
            'update events',
            'delete events',
            // activity log permissions
            'access activity log',
            'view activity log',
            // team members permissions
            'access users',
            'create users',
            'view users',
            'view all team members',
            'update users',
            'delete team members',
            // customers permissions
            'access customers',
            'create customers',
            'view customers',
            'view all customers',
            'update customers',
            'delete customers',
            'assign customer to user',
            // leads permissions
            'access leads',
            'create leads',
            'import leads',
            'view leads',
            'view all leads',
            'update leads',
            'delete leads',
            'assign lead to user',
            // practices permissions
            'access practices',
            'create practices',
            'import practices',
            'view practices',
            'view all practices',
            'update practices',
            'delete practices',
            'assign practice to user',
            // simulator permissions
            'access simulator',
            'view simulator',
            // settings permissions
            'access settings',
            // product subtypes permissions
            'manage product subtypes',
            'create product subtypes',
            'update product subtypes',
            'delete product subtypes',
            // financial table permissions
            'manage financial tables',
            'create financial tables',
            'update financial tables',
            'delete financial tables',
            // insurance permissions
            'manage insurances',
            'create insurances',
            'update insurances',
            'delete insurances',
            // installments permissions
            'manage installments',
            'create installments',
            'update installments',
            'delete installments',
            // customer types permissions
            'manage customer types',
            'create customer types',
            'update customer types',
            'delete customer types',
            // document permissions
            'access form documents',
            'create form documents',
            'view form documents',
            'view all form documents',
            'update form documents',
            'delete form documents',
            'download form documents',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission]);
        }

        // update cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        /**
         * Define role permissions for each role
         * For now, all roles will have the same permissions, except for the observer role.
         */
        $teamMemberPermissions = [
            // dashboard permissions
            'access dashboard',
            // profile permissions
            'view profile',
            'update profile',
            // calendar and events permissions
            'access calendar',
            'access events',
            'create events',
            'view events',
            'update events',
            'delete events',
            // assign team members permissions
            'access users',
            'view users',
            // assign customers permissions
            'access customers',
            'create customers',
            'view customers',
            'update customers',
            'delete customers',
            // assign leads permissions
            'access leads',
            'create leads',
            'view leads',
            'update leads',
            'delete leads',
            // assign practices permissions
            'access practices',
            'view practices',
            'create practices',
            'update practices',
            'delete practices',
            // simulator permissions
            'access simulator',
            'view simulator',
            // document permissions
            'access form documents',
            'view form documents',
            'download form documents',
            // settings permissions
            'access settings',
        ];

        $observerPermissions = [
            // dashboard permissions
            'access dashboard',
            // profile permissions
            'view profile',
            // calendar and events permissions
            'access calendar',
            'access events',
            'view events',
            // assign team members permissions
            'access users',
            'view users',
            // assign customers permissions
            'access customers',
            'view customers',
            // assign leads permissions
            'access leads',
            'view leads',
            // assign practices permissions
            'access practices',
            'view practices',
            // document permissions
            'access form documents',
        ];

        /**
         * Sync permissions to roles
         */
        $directProduction->syncPermissions($teamMemberPermissions);
        $indirectProduction->syncPermissions($teamMemberPermissions);
        $callCenter->syncPermissions($teamMemberPermissions);
        $consultant->syncPermissions($teamMemberPermissions);
        $external->syncPermissions($teamMemberPermissions);
        $observer->syncPermissions($observerPermissions);
    }
}
