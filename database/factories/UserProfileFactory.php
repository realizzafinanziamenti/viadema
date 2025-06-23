<?php

namespace Database\Factories;

use App\Enums\UserDepartment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProfile>
 */
class UserProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_department' => fake()->randomElement(UserDepartment::cases())->value,
            'phone' => fake()->phoneNumber(),
            'tax_id' => fake()->taxId(),
            'city' => fake()->city(),
        ];
    }
}
