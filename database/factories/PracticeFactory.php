<?php

namespace Database\Factories;

use App\Enums\PracticeStatus;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\FinancialTable;
use App\Models\Installment;
use App\Models\Insurance;
use App\Models\ProductSubtype;
use App\Models\ProductType;
use App\Models\User;
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
        $insertedAt = fake()->dateTimeBetween('-1 year', 'now');
        $startedAt = fake()->dateTimeBetween($insertedAt, 'now');
        $practiceStatus = fake()->randomElement(PracticeStatus::cases());

        $paidAt = $practiceStatus === PracticeStatus::DISBURSED
            ? fake()->dateTimeBetween($startedAt, 'now')
            : null;

        $firstDueDate = $paidAt ? fake()->dateTimeBetween($paidAt, '+1 month') : null;
        $lastDueDate = $firstDueDate ? fake()->dateTimeBetween($firstDueDate, '+4 years') : null;
        $extinguishedAt = $paidAt ? fake()->optional()->dateTimeBetween($paidAt, $lastDueDate ?? '+4 years') : null;
        $renewableAt = $paidAt ? fake()->dateTimeBetween($paidAt, $lastDueDate ?? '+4 years') : null;

        $tan = fake()->randomFloat(3, 1.000, 12.000);
        $teg = fake()->randomFloat(2, $tan + 0.2, $tan + 5.0);
        $taeg = fake()->randomFloat(2, $tan + 0.4, $tan + 6.0);

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
            'paid_at' => $paidAt,
            'first_due_date' => $firstDueDate,
            'last_due_date' => $lastDueDate,
            'extinguished_at' => $extinguishedAt,
            'renewable_at' => $renewableAt,

            // Stato e altri dati
            'practice_status' => $practiceStatus->value,
            'days_transformation' => fake()->optional()->numberBetween(0, 90),
            'sum_dec_plus_35' => fake()->optional()->randomFloat(2, 100, 5000),
            'previous_finance' => fake()->optional()->company(),
            'practice_code' => strtoupper(fake()->bothify('PR#??##')),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
