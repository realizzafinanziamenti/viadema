<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Enums\LeadCommunication;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\CustomerType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customerTypes = CustomerType::all();

        return [
            'user_id' => User::factory(),
            'customer_type_id' => fake()->randomElement($customerTypes)->id ?? null,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-18 years'),
            'tax_id' => fake()->unique()->taxId(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'customer_status' => fake()->randomElement(CustomerStatus::cases())->value,
            'lead_source' => fake()->randomElement(LeadSource::cases())->value,
            'lead_status' => fake()->randomElement(LeadStatus::cases())->value,
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
