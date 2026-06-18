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
        Schema::create('practice_opportunities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            $table->foreignId('product_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_subtype_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('financial_table_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('insurance_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('installment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_type_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('amount_disbursed', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->decimal('rate_amount', 10, 2)->nullable();

            $table->decimal('tan', 8, 3)->nullable();
            $table->decimal('teg', 8, 2)->nullable();
            $table->decimal('taeg', 8, 2)->nullable();

            $table->date('first_installment_date')->nullable();
            $table->date('last_installment_date')->nullable();

            $table->decimal('renewability_percentage', 5, 2)->default(40);
            $table->decimal('percentage_alert', 5, 2)->default(35);

            $table->boolean('is_renewal')->default(false);
            $table->string('production_type')->nullable();

            $table->string('disbursing_institution')->nullable();
            $table->string('financial_institution')->nullable();
            $table->string('previous_finance')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_opportunities');
    }
};
