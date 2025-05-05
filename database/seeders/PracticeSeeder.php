<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\FinancialTable;
use App\Models\Installment;
use App\Models\Insurance;
use App\Models\Practice;
use App\Models\ProductSubtype;
use App\Models\ProductType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PracticeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productTypes = ProductType::all();
        $productSubtypes = ProductSubtype::all();
        $users = User::all();
        $customers = Customer::all();
        $financialTables = FinancialTable::all();
        $insurances = Insurance::all();
        $installments = Installment::all();
        $customerTypes = CustomerType::all();

        foreach ($productTypes as $productType) {
            for ($i = 0; $i < 25; $i++) {
                Practice::factory()->create([
                    'product_type_id' => $productType->id,
                    'product_subtype_id' => $productSubtypes->random()->id,
                    'user_id' => $users->random()->id,
                    'customer_id' => $customers->random()->id,
                    'financial_table_id' => $financialTables->random()->id,
                    'insurance_id' => $insurances->random()->id,
                    'installment_id' => $installments->random()->id,
                    'customer_type_id' => $customerTypes->random()->id,
                ]);
            }
        }
    }
}
