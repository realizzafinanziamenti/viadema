<?php

namespace Database\Seeders;

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
         * Create team member test user
         */
        $teamMemberTest = User::factory()->create([
            'first_name' => 'Team Member',
            'last_name' => 'Test',
            'email' => 'team@test.com',
            'password' => Hash::make('password'),
        ]);

        $teamMemberTest->assignRole('team_member');

        UserProfile::factory()->create([
            'user_id' => $teamMemberTest->id,
            'phone' => '1234567890',
            'tax_id' => 'TMMTST12X34Y123Z',
            'city' => 'Roma',
        ]);

        /**
         * Create random team members
         */
        for ($i = 0; $i < 25; $i++) {
            $teamMember = User::factory()->create();

            $teamMember->assignRole('team_member');

            UserProfile::factory()->create([
                'user_id' => $teamMember->id,
            ]);
        }
    }
}
