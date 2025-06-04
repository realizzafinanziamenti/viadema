<?php

namespace Database\Seeders;

use App\Models\Installment;
use App\Models\InstallmentProductDefault;
use App\Models\ProductType;
use Demo\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstallmentProductDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Products
        $products = [
            'cessione-del-quinto',
            'delegazione-di-pagamento',
            'mutui',
            'prestiti'
        ];

        $installments = Installment::whereIn('value', [12, 24, 36, 48, 60, 72, 84, 96, 108, 120])->get()->keyBy('value');

        foreach ($products as $slug) {
            $productType = ProductType::where('slug', $slug)->first();

            $defaults = [];

            foreach ($installments as $installment) {
                $defaults[] = [
                    'installment_id' => $installments->id,
                    'renewable_percentage' => 40.00,
                    'percentage_alert' => $installment->value === 60 ? 10.00 : 35.00,
                ];
            }

            $productType->installmentProductDefaults()->createMany($defaults);
        }
    }
}
