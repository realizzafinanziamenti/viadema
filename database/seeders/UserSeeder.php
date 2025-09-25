<?php

namespace Database\Seeders;

use App\Enums\UserDepartment;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Create floor manager test user
         */
        $floorManagerTest = User::factory()->create([
            'first_name' => 'Utente',
            'last_name' => 'Test1',
            'email' => 'user@test1.com',
            'password' => Hash::make('password'),
        ]);

        $floorManagerTest->assignRole(UserDepartment::FLOOR_MANAGER->getRole());

        UserProfile::factory()->create([
            'user_id' => $floorManagerTest->id,
            'phone' => '1234567890',
            'tax_id' => 'TMMTST12X34Y123Z',
            'city' => 'Roma',
        ]);

        /**
         * Create web test user
         */
        $webTest = User::factory()->create([
            'first_name' => 'Utente',
            'last_name' => 'Test2',
            'email' => 'user@test2.com',
            'password' => Hash::make('password'),
        ]);

        $webTest->assignRole(UserDepartment::WEB->getRole());

        UserProfile::factory()->create([
            'user_id' => $webTest->id,
            'phone' => '1234567890',
            'tax_id' => 'TMMTST12X34Y123A',
            'city' => 'Roma',
        ]);

        /**
         * Create consultant test user
         */
        $consultantTest = User::factory()->create([
            'first_name' => 'User',
            'last_name' => 'Test3',
            'email' => 'user@test3.com',
            'password' => Hash::make('password'),
        ]);

        $consultantTest->assignRole(UserDepartment::CONSULTANT->getRole());

        UserProfile::factory()->create([
            'user_id' => $consultantTest->id,
            'phone' => '1234567890',
            'tax_id' => 'CSTTST12X34Y123B',
            'city' => 'Roma',
        ]);

        /**
         * Create external permission test user
         */
        $externalTest = User::factory()->create([
            'first_name' => 'User',
            'last_name' => 'Test4',
            'email' => 'user@test4.com',
            'password' => Hash::make('password'),
        ]);

        $externalTest->assignRole(UserDepartment::EXTERNAL->getRole());

        UserProfile::factory()->create([
            'user_id' => $externalTest->id,
            'phone' => '1234567890',
            'tax_id' => 'CSTTST12X34Y123D',
            'city' => 'Roma',
        ]);

        /**
         * Create back office permission test user
         */
        $backOfficeTest = User::factory()->create([
            'first_name' => 'User',
            'last_name' => 'Test5',
            'email' => 'user@test5.com',
            'password' => Hash::make('password'),
        ]);

        $backOfficeTest->assignRole(UserDepartment::BACK_OFFICE->getRole());

        UserProfile::factory()->create([
            'user_id' => $backOfficeTest->id,
            'phone' => '1234567890',
            'tax_id' => 'CSTTST12X34Y123C',
            'city' => 'Roma',
        ]);

        /**
         * Create observer test user
         */
        $observerTest = User::factory()->create([
            'first_name' => 'Observer',
            'last_name' => 'Test',
            'email' => 'observer@test.com',
            'password' => Hash::make('password'),
        ]);

        $observerTest->assignRole(UserDepartment::OBSERVER->getRole());

        UserProfile::factory()->create([
            'user_id' => $observerTest->id,
            'phone' => '1234567890',
            'tax_id' => 'OBSTST12X34Y123Z',
            'city' => 'Roma',
        ]);

        /**
         * Create random team members
         */
        for ($i = 0; $i < 25; $i++) {
            $user = User::factory()->create();

            $user->assignRole(UserDepartment::randomRole()->getRole());

            $profile = UserProfile::factory()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
