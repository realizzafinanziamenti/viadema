<?php

namespace Database\Seeders;

use App\Enums\UserDepartment;
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
        $floorManager = Role::updateOrCreate(['name' => UserDepartment::FLOOR_MANAGER->getRole()]);
        $web = Role::updateOrCreate(['name' => UserDepartment::WEB->getRole()]);
        $consultant = Role::updateOrCreate(['name' => UserDepartment::CONSULTANT->getRole()]);
        $external = Role::updateOrCreate(['name' => UserDepartment::EXTERNAL->getRole()]);
        $backOffice = Role::updateOrCreate(['name' => UserDepartment::BACK_OFFICE->getRole()]);
        $observer = Role::updateOrCreate(['name' => UserDepartment::OBSERVER->getRole()]);

        /**
         * Create permissions
         */
        $permissions = [
            // dashboard permissions
            'access dashboard',
            'view disbursed comparison', // liquidato
            'view latest disbursed practices', // ultime pratiche liquidate
            'view practice counters', // numero pratiche
            'view user list', // lista collaboratori
            'view monthly expenses', // spese mensili team
            'view practices by sector', // pratiche per comparto
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
            'update lead status',
            'delete leads',
            'assign lead to user',
            // practices permissions
            'access practices',
            'create practices',
            'import practices',
            'view practices',
            'view all practices',
            'update practices',
            'update practice status',
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
        $floorManagerPermissions = [
            // dashboard permissions
            'access dashboard',
            'view practice counters',
            'view latest disbursed practices',
            'view disbursed comparison',
            'view user list',
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
            // assign customers permissions
            'access customers',
            'create customers',
            'view customers',
            'update customers',
            'delete customers',
            // assign leads permissions
            'access leads',
            'create leads',
            'import leads',
            'view leads',
            'update leads',
            'update lead status',
            'delete leads',
            // assign practices permissions
            'access practices',
            'view practices',
            'create practices',
            'import practices',
            'update practices',
            'update practice status',
            'delete practices',
            // simulator permissions
            'access simulator',
            'view simulator',
            // document permissions
            'access form documents',
            'view form documents',
            'download form documents',
        ];

        $webPermissions = [
            // dashboard permissions
            'access dashboard',
            'view disbursed comparison',
            'view latest disbursed practices',
            'view practice counters',
            'view user list',
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
            'update lead status',
            'delete leads',
            'assign lead to user',
            // practices permissions
            'access practices',
            'create practices',
            'import practices',
            'view practices',
            'view all practices',
            'update practices',
            'update practice status',
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

        $consultantPermissions = [
            // dashboard permissions
            'access dashboard',
            'view disbursed comparison',
            'view latest disbursed practices',
            'view practice counters',
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
        ];

        $externalPermissions = $consultantPermissions;

        $backOfficePermissions = [
            // dashboard permissions
            'access dashboard',
            'view practice counters',
            'view latest disbursed practices',
            'view practices by sector',
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
            // assign practices permissions
            'access practices',
            'view practices',
            'create practices',
            'import practices',
            'update practices',
            'delete practices',
            // simulator permissions
            'access simulator',
            'view simulator',
            // customers permissions
            'access customers',
            'create customers',
            'view customers',
            'update customers',
            'delete customers',
            // leads permissions
            'access leads',
            'view leads',
            'import leads',
            // document permissions
            'access form documents',
            'create form documents',
            'view form documents',
            'view all form documents',
            'update form documents',
            'delete form documents',
            'download form documents',
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
        $floorManager->syncPermissions($floorManagerPermissions);
        $web->syncPermissions($webPermissions);
        $consultant->syncPermissions($consultantPermissions);
        $external->syncPermissions($externalPermissions);
        $backOffice->syncPermissions($backOfficePermissions);
        $observer->syncPermissions($observerPermissions);
    }
}
