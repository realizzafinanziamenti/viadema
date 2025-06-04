<?php

namespace Database\Factories;

use App\Enums\PracticeStatus;
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
        $startedAt = fake()->dateTimeBetween($insertedAt, 'now');

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
            'started_at' => $startedAt,
            'paid_at' => null,
            'first_due_date' => null,
            'last_due_date' => null,
            'extinguished_at' => null,

            // Stato e altri dati
            'practice_status' => fake()->randomElement(PracticeStatus::cases())->value,
            'previous_finance' => fake()->optional()->company(),
            'practice_code' => strtoupper(fake()->bothify('PR#??##')),
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
        if ($practice->product_type_id && $practice->installment_id) {
            $default = InstallmentProductDefault::where('product_type_id', $practice->product_type_id)
                ->where('installment_id', $practice->installment_id)
                ->first();

            if ($default) {
                $practice->renewability_percentage = $default->renewability_percentage;
                $practice->percentage_alert = $default->percentage_alert;

                $installment = Installment::find($practice->installment_id);

                if ($installment) {
                    // Calcolo rate e date
                    $totalRate = $installment->value;
                    $rinnovoRate = ceil($totalRate * ($default->renewability_percentage / 100));
                    $alertRate = ceil($totalRate * ($default->percentage_alert / 100));

                    $practice->renewable_at = Carbon::parse($practice->started_at)->addMonths($rinnovoRate);
                    $practice->alert_date = Carbon::parse($practice->started_at)->addMonths($alertRate);

                    $practice->first_due_date = Carbon::parse($practice->started_at)->addMonth();
                    $practice->last_due_date = (clone $practice->first_due_date)->addMonths($totalRate - 1);
                }
            }

            // if practice_status is DISBURSED, set paid_at and optionally extinguished_at
            if ($practice->practice_status == PracticeStatus::DISBURSED->value) {
                $practice->paid_at = Carbon::parse($practice->started_at)->addDays(fake()->numberBetween(5, 30));
                $practice->extinguished_at = (clone $practice->paid_at)->addMonths(fake()->numberBetween(6, 24));
            }
        }
    }
}
