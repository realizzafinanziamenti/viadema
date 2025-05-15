<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $userIds;

        if (! $userIds) {
            $userIds = User::pluck('id')->all();
        }
        if (empty($userIds)) {
            $userIds = [1]; // Fallback to a default user ID if no users exist
        }

        $start = Carbon::instance(fake()->dateTimeBetween('-1 month', '+1 month'));
        $end = fake()->dateTimeBetween($start, $start->copy()->addDay());

        return [
            'user_id' => fake()->randomElement($userIds),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'starts_at' => $start,
            'ends_at' => $end,
            'is_all_day' => fake()->boolean(),
        ];
    }
}
