<?php

namespace Database\Seeders;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::assignableUsers();

        foreach ($users as $user) {

            // Create 1 to 3 customers for each user
            Customer::factory()
                ->count(random_int(1, 3))
                ->create([
                    'user_id' => $user->id,
                    'customer_status' => CustomerStatus::CUSTOMER->value,
                    'lead_source' => null,
                    'lead_status' => null,
                    'amount' => null,
                ]);

            // Create 1 to 3 leads for each user
            Customer::factory()
                ->count(random_int(1, 3))
                ->create([
                    'user_id' => $user->id,
                    'customer_status' => CustomerStatus::LEAD->value,
                ]);
        }
    }
}
