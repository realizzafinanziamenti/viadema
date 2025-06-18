<?php

namespace App\Enums;

enum CustomerStatus: string
{
    case LEAD = 'lead';
    case CUSTOMER = 'customer';

    /**
     * Get the display label for the current status
     */
    public function getLabelText(): string
    {
        return match ($this) {
            self::LEAD => 'Lead',
            self::CUSTOMER => 'Cliente',
        };
    }
}
