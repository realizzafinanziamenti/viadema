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

    /**
     * Get the CSS class for the current lead status
     */
    public function getLabelColor(): string
    {
        return match ($this) {
            self::NEW => 'lead-status-new',
            self::ACTIVE => 'lead-status-active',
            self::LOST => 'lead-status-lost',
        };
    }
}
