<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->dropForeign(['product_type_id']);
            $table->dropForeign(['product_subtype_id']);
            $table->dropForeign(['financial_table_id']);
            $table->dropForeign(['insurance_id']);
            $table->dropForeign(['installment_id']);
            $table->dropForeign(['customer_type_id']);
        });

        Schema::table('practices', function (Blueprint $table) {
            $table->dropColumn([
                'product_type_id',
                'product_subtype_id',
                'financial_table_id',
                'insurance_id',
                'installment_id',
                'customer_type_id',

                'product_subtype_label',
                'financial_table_percentage',
                'insurance_label',
                'installment_value_label',
                'customer_type_label',

                'amount_disbursed',
                'total_amount',
                'rate_amount',
                'tan',
                'teg',
                'taeg',

                'first_installment_date',
                'last_installment_date',

                'renewability_percentage',
                'percentage_alert',

                'is_renewal',
                'production_type',
                'disbursing_institution',
                'financial_institution',
                'previous_finance',
                'notes',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->foreignId('product_type_id')->nullable()->after('customer_id')->constrained('product_types')->nullOnDelete();
            $table->foreignId('product_subtype_id')->nullable()->after('product_type_id')->constrained('product_subtypes')->nullOnDelete();
            $table->foreignId('financial_table_id')->nullable()->after('product_subtype_id')->constrained('financial_tables')->nullOnDelete();
            $table->foreignId('insurance_id')->nullable()->after('financial_table_id')->constrained('insurances')->nullOnDelete();
            $table->foreignId('installment_id')->nullable()->after('insurance_id')->constrained('installments')->nullOnDelete();
            $table->foreignId('customer_type_id')->nullable()->after('installment_id')->constrained('customer_types')->nullOnDelete();

            $table->string('product_subtype_label')->nullable()->after('customer_type_id');
            $table->decimal('financial_table_percentage', 5, 2)->nullable()->after('product_subtype_label');
            $table->string('insurance_label')->nullable()->after('financial_table_percentage');
            $table->string('installment_value_label')->nullable()->after('insurance_label');
            $table->string('customer_type_label')->nullable()->after('installment_value_label');

            $table->decimal('amount_disbursed', 12, 2)->nullable()->after('customer_type_label');
            $table->decimal('total_amount', 12, 2)->nullable()->after('amount_disbursed');
            $table->decimal('rate_amount', 12, 2)->nullable()->after('total_amount');
            $table->decimal('tan', 6, 3)->nullable()->after('rate_amount');
            $table->decimal('teg', 6, 2)->nullable()->after('tan');
            $table->decimal('taeg', 6, 2)->nullable()->after('teg');

            $table->date('first_installment_date')->nullable()->after('inserted_at');
            $table->date('last_installment_date')->nullable()->after('first_installment_date');

            $table->decimal('renewability_percentage', 5, 2)->nullable()->after('disbursement_date');
            $table->decimal('percentage_alert', 5, 2)->nullable()->after('renewability_date');

            $table->boolean('is_renewal')->nullable()->after('sum_dec_plus_35');
            $table->string('production_type')->nullable()->after('is_renewal');
            $table->string('disbursing_institution')->nullable()->after('production_type');
            $table->string('financial_institution')->nullable()->after('disbursing_institution');
            $table->string('previous_finance')->nullable()->after('financial_institution');
            $table->text('notes')->nullable()->after('previous_finance');
        });
    }
};