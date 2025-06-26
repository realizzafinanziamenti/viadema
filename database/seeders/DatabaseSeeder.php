<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // production seeding
        $this->call([
            RolesAndPermissionsSeeder::class,
            SuperAdminSeeder::class,
            CustomerTypeSeeder::class,
            FinancialTableSeeder::class,
            InstallmentSeeder::class,
            InsuranceSeeder::class,
            ProductTypeSeeder::class,
            ProductSubtypeSeeder::class,
            InstallmentProductDefaultSeeder::class,
            FormDocumentSeeder::class,
        ]);

        // development seeding
        if (app()->environment('local', 'testing')) {
            $this->call([
                UserSeeder::class,
                CustomerSeeder::class,
                PracticeSeeder::class,
                EventSeeder::class,
            ]);
        }
    }
}
