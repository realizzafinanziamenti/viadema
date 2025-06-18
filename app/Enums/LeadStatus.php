<?php

namespace App\Enums;

enum LeadStatus: string
{
    case NEW = 'new';
    case ACTIVE = 'active';
    case LOST = 'lost';

    /**
     * Get the display label for the current lead status
     */
    public function getLabelText(): string
    {
        return match ($this) {
            self::NEW => 'Nuovo',
            self::ACTIVE => 'Attivo',
            self::LOST => 'Perso',
        };
    }
}
