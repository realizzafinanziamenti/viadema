<?php

namespace Database\Seeders;

use App\Models\Insurance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InsuranceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insurances = [
            ['name' => 'Net'],
            ['name' => 'Met-Life'],
            ['name' => 'Cardif'],
            ['name' => 'Axa'],
            ['name' => 'Credem Vita'],
            ['name' => 'Iptiq'],
        ];

        foreach ($insurances as $insurance) {
            Insurance::create($insurance);
        }
    }
}
