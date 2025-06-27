<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_name' => fake()->word() . '.' . fake()->fileExtension(),
            'file_path' => fake()->filePath(),
            'mime_type' => fake()->mimeType(),
            'file_size' => fake()->numberBetween(1000, 1000000), // Size in bytes
        ];
    }
}
