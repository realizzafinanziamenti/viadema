<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Enums\LeadStatus;
use App\Models\CustomerType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_type_id' => CustomerType::query()->inRandomOrder()->value('id'),

            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-18 years'),

            'tax_id' => $this->fakeItalianTaxId(),

            'address' => fake()->address(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),

            'customer_status' => fake()->randomElement(CustomerStatus::cases())->value,
            'lead_status' => fake()->randomElement(LeadStatus::cases())->value,

            'notes' => fake()->optional()->paragraph(),
        ];
    }

    private function fakeItalianTaxId(): string
    {
        return strtoupper(fake()->unique()->regexify('[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]'));
    }
}