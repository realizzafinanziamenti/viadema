<?php

namespace Database\Factories;

use App\Enums\PracticeStatus;
use App\Enums\ProductionType;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\FinancialTable;
use App\Models\Installment;
use App\Models\InstallmentProductDefault;
use App\Models\Insurance;
use App\Models\Practice;
use App\Models\ProductSubtype;
use App\Models\ProductType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Practice>
 */
class PracticeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generazione date inserimento e inizio
        $insertedAt = fake()->dateTimeBetween('-1 year', 'now');
        $firstInstallmentDate = fake()->dateTimeBetween($insertedAt, 'now');

        // Calcolo TAN, TEG e TAEG
        $tan = fake()->randomFloat(3, 1.000, 12.000);
        $teg = fake()->randomFloat(2, $tan + 0.2, $tan + 5.0);
        $taeg = fake()->randomFloat(2, $tan + 0.4, $tan + 6.0);

        // Importi finanziari
        $amountDisbursed = fake()->randomFloat(2, 1000, 25000);
        $totalAmount = $amountDisbursed + fake()->randomFloat(2, 500, 5000);

        return [
            // Relazioni
            // 'product_type_id' => ProductType::factory(),
            // 'product_subtype_id' => ProductSubtype::factory(),
            // 'user_id' => User::factory(),
            // 'customer_id' => Customer::factory(),
            // 'financial_table_id' => FinancialTable::factory(),
            // 'insurance_id' => Insurance::factory(),
            // 'installment_id' => Installment::factory(),
            // 'customer_type_id' => CustomerType::factory(),

            // Importi
            'amount_disbursed' => $amountDisbursed,
            'total_amount' => $totalAmount,
            'rate_amount' => fake()->randomFloat(2, 100, 1000),
            'tan' => $tan,
            'teg' => $teg,
            'taeg' => $taeg,

            // Date
            'inserted_at' => $insertedAt,
            'first_installment_date' => $firstInstallmentDate,
            'last_installment_date' => null,
            'early_settlement_date' => null,
            'disbursement_date' => null,

            // Stato e altri dati
            'practice_status' => fake()->randomElement(PracticeStatus::cases())->value,
            'previous_finance' => fake()->optional()->company(),
            'practice_code' => strtoupper(fake()->unique()->bothify('PR#??##')),
            'is_renewal' => fake()->boolean(20),
            'production_type' => fake()->randomElement(ProductionType::cases())->value,
            'disbursing_institution' => fake()->optional()->company(),
            'financial_institution' => fake()->optional()->company(),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Practice $practice) {
            $this->setDefaults($practice);
        })->afterCreating(function (Practice $practice) {
            $this->setDefaults($practice);
            $practice->save();
        });
    }

    protected function setDefaults(Practice $practice)
    {
        if ($practice->product_type_id && $practice->installment_id && $practice->first_installment_date) {

            // Recupera valori di default delle percentuali di rinnovo e alert basati sul tipo di prodotto e rata
            $default = InstallmentProductDefault::where('product_type_id', $practice->product_type_id)
                ->where('installment_id', $practice->installment_id)
                ->first();

            if ($default) {
                // Imposta le date di rinnovo e alert
                $practice->renewability_percentage = $default->renewability_percentage;
                $practice->percentage_alert = $default->percentage_alert;
            }

            // se stato pratica DISBURSED (Liquidata), calcola le date di liquidazione e estinzione anticipata
            if ($practice->practice_status === PracticeStatus::DISBURSED) {
                $practice->early_settlement_date = $practice->first_installment_date?->copy()->addDays(fake()->numberBetween(5, 30));
                $practice->disbursement_date = $practice->early_settlement_date?->copy()->addMonths(fake()->numberBetween(6, 24));
            }
        }
    }
}
