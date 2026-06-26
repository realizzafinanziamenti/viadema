<?php

namespace Database\Factories;

use App\Enums\PracticeStatus;
use App\Enums\ProductionType;
use App\Models\Customer;
use App\Models\Practice;
use App\Models\PracticeOpportunity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Practice>
 */
class PracticeFactory extends Factory
{
    protected $model = Practice::class;

    public function definition(): array
    {
        $insertedAt = Carbon::instance(fake()->dateTimeBetween('-1 year', 'now'));
        $status = fake()->randomElement(PracticeStatus::cases());

        return [
            'user_id' => User::factory(),
            'customer_id' => Customer::factory(),

            'inserted_at' => $insertedAt,
            'early_settlement_date' => null,
            'disbursement_date' => $status === PracticeStatus::DISBURSED
                ? Carbon::instance(fake()->dateTimeBetween($insertedAt, 'now'))
                : null,

            'practice_status' => $status->value,
            'days_transformation' => fake()->optional()->numberBetween(1, 90),
            'sum_dec_plus_35' => fake()->optional()->randomFloat(2, 100, 5000),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Practice $practice) {
            if ($practice->practice_opportunity_id) {
                return;
            }

            $firstInstallmentDate = Carbon::parse($practice->inserted_at ?? now())
                ->copy()
                ->addMonth()
                ->startOfDay();

            $totalInstallments = fake()->randomElement([24, 36, 48, 60, 72, 84, 96, 120]);

            $amountDisbursed = fake()->randomFloat(2, 1000, 25000);
            $totalAmount = $amountDisbursed + fake()->randomFloat(2, 500, 5000);

            $tan = fake()->randomFloat(3, 1.000, 12.000);
            $teg = fake()->randomFloat(2, $tan + 0.2, $tan + 5.0);
            $taeg = fake()->randomFloat(2, $tan + 0.4, $tan + 6.0);

            $opportunity = PracticeOpportunity::create([
                'customer_id' => $practice->customer_id,

                'product_type_id' => null,
                'product_subtype_id' => null,
                'financial_table_id' => null,
                'insurance_id' => null,
                'installment_id' => null,
                'customer_type_id' => null,

                'amount_disbursed' => $amountDisbursed,
                'total_amount' => $totalAmount,
                'rate_amount' => fake()->randomFloat(2, 100, 1000),

                'tan' => $tan,
                'teg' => $teg,
                'taeg' => $taeg,

                'first_installment_date' => $firstInstallmentDate,
                'last_installment_date' => $firstInstallmentDate
                    ->copy()
                    ->addMonthsNoOverflow($totalInstallments - 1),

                'renewability_percentage' => 40.00,
                'percentage_alert' => 35.00,

                'is_renewal' => fake()->boolean(20),
                'production_type' => fake()->optional()->randomElement(ProductionType::cases())?->value,

                'disbursing_institution' => fake()->optional()->company(),
                'financial_institution' => fake()->optional()->company(),
                'previous_finance' => fake()->optional()->company(),
                'notes' => fake()->optional()->sentence(),
            ]);

            $practice->practice_opportunity_id = $opportunity->id;
            $practice->save();

            $practice->setRelation('opportunity', $opportunity);
        });
    }
}
