<?php

namespace Database\Seeders;

use App\Models\ProductType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productTypes = [
            ['name' => 'Cessione del Quinto'],
            ['name' => 'Delegazione di Pagamento'],
            ['name' => 'Mutui'],
            ['name' => 'Prestiti'],
        ];

        foreach ($productTypes as $type) {
            ProductType::create($type);
        }
    }
}
