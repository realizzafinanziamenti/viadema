<?php

namespace Database\Factories;

use App\Enums\EventType;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
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

        $startTime = Carbon::createFromTime(
            rand(9, 16),
            rand(0, 59)
        )->format('H:i:s');

        $endTime = Carbon::createFromFormat('H:i:s', $startTime)
            ->addMinutes(rand(30, 180))
            ->format('H:i:s');

        return [
            'user_id' => fake()->randomElement($userIds),
            'practice_id' => null,
            'event_type' => EventType::GENERAL->value,
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'start_date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_all_day' => false,
        ];
    }
}
