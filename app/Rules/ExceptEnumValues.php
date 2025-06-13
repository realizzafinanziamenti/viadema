<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

class ExceptEnumValues implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @param  class-string  $enumClass
     * @param  array<int, string>  $except
     */
    public function __construct(
        protected string $enumClass,  // La classe enum da validare
        protected array $except = []     // Valori da escludere
    ) {
        if (! enum_exists($enumClass)) {
            throw new \InvalidArgumentException("{$enumClass} is not a valid enum.");
        }
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute  // Il nome dell'attributo da validare
     * @param  mixed  $value      // Il valore da validare
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Prende tutti i cases() dell’enum
        // Rimuove quelli con un value incluso in $except
        $allowedCases = array_filter(
            $this->enumClass::cases(),
            fn($case) => !in_array($case->value, $this->except, true)
        );

        // Estrae solo i value dagli enum filtrati
        $allowedValues = Arr::pluck($allowedCases, 'value');

        // Controlla se il valore è tra quelli consentiti
        if (! in_array($value, $allowedValues, true)) {
            $fail("Il valore selezionato non è valido.");
        }
    }
}
