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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_type_id')->constrained()->cascadeOnDelete();  // tipo prodotto
            $table->foreignId('product_subtype_id')->nullable()->constrained()->nullOnDelete();  // sottotipo prodotto
            $table->foreignId('collaborator_id')->constrained()->cascadeOnDelete();  // collaboratore
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();  // cliente
            $table->foreignId('financial_table_id')->nullable()->constrained()->nullOnDelete();  // tabella/piano finanziario
            $table->foreignId('insurance_id')->nullable()->constrained()->nullOnDelete();  // assicurazione
            $table->foreignId('installment_id')->nullable()->constrained()->nullOnDelete();  // numero rate
            $table->foreignId('customer_type_id')->nullable()->constrained()->nullOnDelete();  // tipologia cliente
            $table->string('previous_finance')->nullable();  // finanziaria estinta
            $table->date('inserted_at')->nullable();  // data di inserimento in sistema
            $table->date('started_at')->nullable();  // data di apertura pratica
            $table->date('paid_at')->nullable();  // data liquidazione
            $table->date('extinguished_at')->nullable();  // data di estinzione
            $table->date('renewable_at')->nullable();  // data di rinnovo
            $table->tinyInteger('product_status');  // stato prodotto
            $table->decimal('rate_amount', 10, 2)->nullable();  // importo rata
            $table->text('notes')->nullable();
            $table->string('practice_code')->unique();  // id univoco pratica
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
