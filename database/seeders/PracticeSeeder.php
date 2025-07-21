<?php

namespace Database\Seeders;

use App\Models\Attachment;
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
        $users = User::assignableUsers();
        $customers = Customer::all();
        $financialTables = FinancialTable::all();
        $insurances = Insurance::all();
        $customerTypes = CustomerType::all();

        foreach ($productTypes as $productType) {
            // Recupera le rate valide per il tipo di prodotto
            $validInstallments = Installment::whereHas('productTypes', function ($query) use ($productType) {
                $query->where('product_type_id', $productType->id);
            })->get();

            for ($i = 0; $i < 100; $i++) {
                $installment = $validInstallments->random();

                $practice = Practice::factory()->create([
                    'product_type_id' => $productType->id,
                    'product_subtype_id' => $productSubtypes->random()->id,
                    'user_id' => $users->random()->id,
                    'customer_id' => $customers->random()->id,
                    'financial_table_id' => $financialTables->random()->id,
                    'insurance_id' => $insurances->random()->id,
                    'installment_id' => $installment->id,
                    'customer_type_id' => $customerTypes->random()->id,
                ]);

                // nel 20% dei casi, imposta la data dell'alert a 15-30 minuti nel futuro
                if ($i % 5 === 0) {
                    $practice->update([
                        'alert_date' => now()->addMinutes(rand(10, 20)),
                        'user_id' => 1, // superadmin user
                    ]);
                }

                // 50% dei casi, aggiungi 1 o 2 allegati
                if (fake()->boolean()) {
                    $attachmentCount = rand(1, 2);

                    for ($j = 0; $j < $attachmentCount; $j++) {
                        $practice->attachments()->save(
                            Attachment::factory()->make()
                        );
                    }
                }
            }
        }
    }
}
