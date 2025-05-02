<?php

namespace Database\Factories;

use App\ProductStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
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
        $productStatus = fake()->randomElement([ProductStatus::class]);
        $paidAt = $productStatus === ProductStatus::DISBURSED->value ? fake()->dateTimeBetween($startedAt, 'now') : null;

        return [
            'inserted_at' => $insertedAt,
            'started_at' => $startedAt,
            'paid_at' => $paidAt,
            'extinguished_at' => fake()->dateTimeBetween($paidAt, '+1 year'),
            'renewable_at' => fake()->dateTimeBetween($paidAt, '+1 year'),
            'product_status' => $productStatus,
            'rate_amount' => fake()->randomFloat(2, 100, 10000),
            'practice_code' => '#' . fake()->unique()->numerify('AB###'),
        ];
    }
}
