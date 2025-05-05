<?php

namespace Database\Seeders;

use App\Models\ProductSubtype;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSubtypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productSubtypes = [
            ['name' => 'Dipendente'],
            ['name' => 'Dipendente Mef'],
            ['name' => 'Pensionati Altri Enti'],
            ['name' => 'Pensionati Inps/ex Inpdap'],
        ];

        foreach ($productSubtypes as $subtype) {
            ProductSubtype::create($subtype);
        }
    }
}
