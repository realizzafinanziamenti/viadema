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
        Schema::create('installment_product_default', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('renewability_percentage', 5, 2)->default(40.00); // Percentuale di rinnovo su ammortamento
            $table->decimal('percentage_alert', 5, 2)->default(35.00);           // Percentuale di alert su rinnovo
            $table->integer('alert_months')->default(0);                                 // Numero di mesi per l'alert

            $table->unique(['installment_id', 'product_type_id'], 'unique_installment_product_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_product_default');
    }
};
