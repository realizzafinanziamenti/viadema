<?php

namespace App\Enums;

enum LeadStatus: string
{
    case NEW = 'new';
    case CONTACTED = 'contacted';
    case WAITING_REPLY = 'waiting_reply';

    /**
     * Get the display label for the current lead status
     */
    public function getLabelText(): string
    {
        return match ($this) {
            self::NEW => 'Nuovo',
            self::CONTACTED => 'Contattato',
            self::WAITING_REPLY => 'In attesa di risposta',
        };
    }

    /**
     * Get the CSS class for the current lead status
     */
    public function getLabelColor(): string
    {
        return match ($this) {
            self::NEW => 'lead-status-new',
            self::CONTACTED => 'lead-status-contacted',
            self::WAITING_REPLY => 'lead-status-waiting-reply',
        };
    }
}
