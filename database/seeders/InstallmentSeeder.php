<?php

namespace Database\Seeders;

use App\Models\Installment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstallmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $installments = [
            ['value' => 12],
            ['value' => 24],
            ['value' => 36],
            ['value' => 48],
            ['value' => 60],
            ['value' => 72],
            ['value' => 84],
            ['value' => 96],
            ['value' => 108],
            ['value' => 120],
        ];

        foreach ($installments as $installment) {
            Installment::create($installment);
        }
    }
}
