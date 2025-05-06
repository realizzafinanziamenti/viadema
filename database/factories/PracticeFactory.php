<?php

namespace Database\Factories;

use App\Enums\PracticeStatus;
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
        $paidAt = $practiceStatus->value === PracticeStatus::DISBURSED->value ? fake()->dateTimeBetween($startedAt, 'now') : null;
        $extinguishedAt = $paidAt ? fake()->dateTimeBetween($paidAt, 'now') : null;
        $renewableAt = $paidAt ? fake()->dateTimeBetween($paidAt, 'now') : null;
        $tan = fake()->randomFloat(3, 1.000, 12.000);
        $teg = fake()->randomFloat(2, $tan + 0.2, $tan + 5.0);
        $taeg = fake()->randomFloat(2, $tan + 0.4, $tan + 6.0);

        return [
            'inserted_at' => $insertedAt,
            'started_at' => $startedAt,
            'paid_at' => $paidAt,
            'extinguished_at' => $extinguishedAt,
            'renewable_at' => $renewableAt,
            'practice_status' => $practiceStatus,
            'rate_amount' => fake()->randomFloat(2, 100, 10000),
            'tan' => $tan,
            'teg' => $teg,
            'taeg' => $taeg,
            'practice_code' => '#' . fake()->unique()->numerify('AB###'),
        ];
    }
}
