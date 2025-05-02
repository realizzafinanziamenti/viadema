<?php

namespace Database\Seeders;

use App\Models\CustomerType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customerTypes = [
            ['name' => 'Ministeriale'],
            ['name' => 'Parapubblico'],
            ['name' => 'Privato'],
            ['name' => 'Pubblico'],
            ['name' => 'Ministeriale Mef'],
            ['name' => 'Forze Armate Mef'],
        ];

        foreach ($customerTypes as $customerType) {
            CustomerType::create($customerType);
        }
    }
}
