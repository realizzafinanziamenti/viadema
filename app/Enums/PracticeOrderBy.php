<?php

namespace App\Enums;

enum PracticeOrderBy: string
{
    case UPDATED_AT_DESC = 'updated_at|desc';
    case CREATED_AT_DESC = 'created_at|desc';
    case CREATED_AT_ASC = 'created_at|asc';
    case RENEWABILITY_DATE_DESC = 'renewability_date|desc';
    case RENEWABILITY_DATE_ASC = 'renewability_date|asc';
    case DISBURSEMENT_DATE_DESC = 'disbursement_date|desc';
    case DISBURSEMENT_DATE_ASC = 'disbursement_date|asc';

    /**
     * Get the field of the current practice order by
     */
    public function field(): string
    {
        return explode('|', $this->value)[0];
    }

    /**
     * Get the direction of the current practice order by
     */
    public function direction(): string
    {
        return explode('|', $this->value)[1];
    }

    /**
     * Get the display label for the current practice order by
     */
    public function getLabelText(): string
    {
        return match ($this) {
            self::UPDATED_AT_DESC => 'Ultimo aggiornamento',
            self::CREATED_AT_DESC => 'Data di creazione (più recente)',
            self::CREATED_AT_ASC => 'Data di creazione (più vecchia)',
            self::RENEWABILITY_DATE_DESC => 'Data di rinnovabilità (più recente)',
            self::RENEWABILITY_DATE_ASC => 'Data di rinnovabilità (più vecchia)',
            self::DISBURSEMENT_DATE_DESC => 'Data di liquidazione (più recente)',
            self::DISBURSEMENT_DATE_ASC => 'Data di liquidazione (più vecchia)',
        };
    }

    /**
     * Get the options for the practice order by enum.
     * This is useful for generating dropdowns or select inputs.
     * [value => label]
     */
    public static function options(array $excluded = []): array
    {
        return collect(self::cases())
            ->reject(fn($case) => in_array($case, $excluded))
            ->mapWithKeys(fn($case) => [$case->value => $case->getLabelText()])
            ->toArray();
    }
}
