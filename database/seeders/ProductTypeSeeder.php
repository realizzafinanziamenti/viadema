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
            ['name' => 'Cessione del Quinto', 'slug' => 'cessione-del-quinto'],
            ['name' => 'Delegazione di Pagamento', 'slug' => 'delegazione-di-pagamento'],
            ['name' => 'Mutui', 'slug' => 'mutui'],
            ['name' => 'Prestiti', 'slug' => 'prestiti'],
        ];

        foreach ($productTypes as $type) {
            ProductType::create($type);
        }
    }
}
