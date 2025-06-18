<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueNormalized implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @param  string  $table                  Nome della tabella su cui applicare la regola
     * @param  string  $column              Nome della colonna su cui verificare l'unicità
     * @param  string|null  $except         Un valore da escludere dalla verifica di unicità
     */
    public function __construct(
        protected string $table,
        protected string $column,
        protected ?string $except = null
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Normalizzazione del valore ricevuto: rimuove spazi e converte in minuscolo
        $normalized = strtolower(trim($value));

        // Costruisce la query per cercare un valore esistente normalizzato nella tabella/colonna
        $query = DB::table($this->table)
            ->whereRaw("LOWER(TRIM($this->column)) = ?", [$normalized]);

        // Se è stato specificato un valore da escludere, lo aggiunge alla query
        if ($this->except !== null) {
            $query->where('id', '!=', $this->except);
        }

        // Se esiste almeno un risultato corrispondente, fallisce la validazione
        if ($query->exists()) {
            $fail("Esiste già un elemento con questo $attribute.");
        }
    }
}
