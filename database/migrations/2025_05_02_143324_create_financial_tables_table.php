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
        Schema::create('financial_tables', function (Blueprint $table) {
            $table->id();
            $table->decimal('percentage', 5, 2)->unique(); // example: 0.50, 1.00, 1.50
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_tables');
    }
};
