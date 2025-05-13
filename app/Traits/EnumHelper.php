<?php

namespace App\Traits;

trait EnumHelper
{
    /**
     * Restituisce un array [value => label] da un enum class.
     */
    protected function getEnumOptions(string $enumClass): array
    {
        $options = [];

        foreach ($enumClass::cases() as $case) {
            $options[$case->value] = method_exists($case, 'getLabelText')
                ? $case->getLabelText()
                : $case->name;
        }

        return $options;
    }
}
