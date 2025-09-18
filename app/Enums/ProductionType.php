<?php

namespace App\Enums;

enum ProductionType: string
{
    case DIRECT = 'direct';
    case INDIRECT = 'indirect';

    /**
     * Get the label for the production type.
     */
    public function getLabelText(): string
    {
        return match ($this) {
            self::DIRECT => 'Diretta',
            self::INDIRECT => 'Indiretta',
        };
    }
}
