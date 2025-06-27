<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\FormDocument;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FormDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FormDocument::factory()
            ->count(40)
            ->create([
                'user_id' => 1, // SuperAdmin user
            ])
            ->each(function (FormDocument $formDocument) {
                // Attach a random attachment to each form document
                $formDocument->attachment()->save(
                    Attachment::factory()->make()
                );
            });
    }
}
