<?php

namespace App\Rules;

use App\Models\CustomerType;
use App\Models\FinancialTable;
use App\Models\Installment;
use App\Models\Insurance;
use App\Models\ProductSubtype;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotUsedInPractices implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @param  ProductSubtype|CustomerType|FinancialTable|Installment|Insurance  $model
     */
    public function __construct(
        protected ProductSubtype|CustomerType|FinancialTable|Installment|Insurance $model,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Se il modello ha pratiche associate, fallisce la validazione
        // e restituisce un messaggio di errore.
        // Questo impedisce la modifica del tipo prodotto se è già associato a pratiche.
        if ($this->model->practices()->exists()) {
            $fail("Impossibile modificare perché è già associato a una o più pratiche.");
        }
    }
}
