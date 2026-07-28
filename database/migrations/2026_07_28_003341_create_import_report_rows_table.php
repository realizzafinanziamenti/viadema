<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('import_report_rows', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('import_report_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->uuid('run_uuid');

            $table->unsignedInteger('row_number');
            $table->string('status', 30);

            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();

            $table->string('label')->nullable();
            $table->text('message')->nullable();

            $table->json('errors')->nullable();
            $table->json('raw_data')->nullable();

            $table->timestamps();

            $table->unique(
                ['import_report_id', 'run_uuid', 'row_number'],
                'import_report_rows_run_row_unique'
            );

            $table->index(
                ['import_report_id', 'status'],
                'import_report_rows_report_status_index'
            );

            $table->index(
                ['import_report_id', 'run_uuid'],
                'import_report_rows_report_run_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_report_rows');
    }
};