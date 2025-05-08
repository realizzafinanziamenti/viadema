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
        Schema::create('practices', function (Blueprint $table) {
            $table->id();

            // Relazioni
            $table->foreignId('product_type_id')->constrained()->cascadeOnDelete();                 // es. Cessione, Mutuo
            $table->foreignId('product_subtype_id')->nullable()->constrained()->nullOnDelete(); // es. Mutuo Under 36
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();                               // collaboratore
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();                       // cliente
            $table->foreignId('financial_table_id')->nullable()->constrained()->nullOnDelete();     // tabella provvigione
            $table->foreignId('insurance_id')->nullable()->constrained()->nullOnDelete();           // assicurazione
            $table->foreignId('installment_id')->nullable()->constrained()->nullOnDelete();         // numero rate
            $table->foreignId('customer_type_id')->nullable()->constrained()->nullOnDelete();   // tipologia cliente

            // Importi finanziari
            $table->decimal('amount_disbursed', 10, 2)->nullable();     // finanziato/importo
            $table->decimal('total_amount', 10, 2)->nullable();             // montante
            $table->decimal('rate_amount', 10, 2)->nullable();              // importo rata
            $table->decimal('tan', 5, 3)->nullable();                               // TAN
            $table->decimal('teg', 5, 2)->nullable();                               // TEG
            $table->decimal('taeg', 5, 2)->nullable();                             // TAEG

            // Date
            $table->date('inserted_at')->nullable();                        // inserimento sistema
            $table->date('started_at')->nullable();                         // data d'inizio
            $table->date('paid_at')->nullable();                            // data liquidazione
            $table->date('first_due_date')->nullable();                // data prima rata / data d'inizio
            $table->date('last_due_date')->nullable();                 // data ultima rata / data di liquidazione
            $table->date('extinguished_at')->nullable();               // data estinzione anticipata
            $table->date('renewable_at')->nullable();                  // data rinnovabilità (calcolata)

            // Stato e flag
            $table->tinyInteger('practice_status');                         // stato pratica
            $table->integer('days_transformation')->nullable();        // Trasformazione GG (differenza giorni?)
            $table->decimal('sum_dec_plus_35', 10, 2)->nullable();     // somma dec + 35% (se utile davvero)

            // Dettagli
            $table->string('previous_finance')->nullable();            // finanziaria estinta
            $table->string('practice_code')->unique();                   // ID pratica univoco
            $table->text('notes')->nullable();                                  // note libere

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practices');
    }
};
