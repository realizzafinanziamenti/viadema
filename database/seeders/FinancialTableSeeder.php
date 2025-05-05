<?php

namespace Database\Seeders;

use App\Models\FinancialTable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FinancialTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $financials = [
            ['percentage' => 0.5],
            ['percentage' => 1.0],
            ['percentage' => 1.5],
            ['percentage' => 2.0],
            ['percentage' => 2.5],
            ['percentage' => 3.0],
            ['percentage' => 3.5],
            ['percentage' => 4.0],
            ['percentage' => 4.5],
            ['percentage' => 5.0],
            ['percentage' => 5.5],
            ['percentage' => 6.0],
            ['percentage' => 6.5],
            ['percentage' => 7.0],
            ['percentage' => 7.5],
            ['percentage' => 8.0],
            ['percentage' => 8.5],
            ['percentage' => 9.0],
            ['percentage' => 9.5],
            ['percentage' => 10.0],
            ['percentage' => 10.5],
            ['percentage' => 11.0],
            ['percentage' => 11.5],
            ['percentage' => 12.0],
        ];

        foreach ($financials as $financial) {
            FinancialTable::create($financial);
        }
    }
}
