<?php

namespace App\Enums;

enum LeadStatus: string
{
    case NEW = 'new';
    case TO_RECONTACT = 'to_recontact';
    case IN_NEGOTIATION = 'in_negotiation';
    case NOT_FEASIBLE = 'not_feasible';
    case FEASIBLE = 'feasible';

    /**
     * Get the display label for the current lead status
     */
    public function getLabelText(): string
    {
        return match ($this) {
            self::NEW => 'Nuovo',
            self::TO_RECONTACT => 'Da Ricontattare',
            self::IN_NEGOTIATION => 'In Trattativa',
            self::NOT_FEASIBLE => 'Non Fattibile',
            self::FEASIBLE => 'Fattibile',
        };
    }

    /**
     * Get the CSS class for the current lead status
     */
    public function getLabelColor(): string
    {
        return match ($this) {
            self::NEW => 'lead-status-new',
            self::NOT_FEASIBLE => 'lead-status-not-feasible',
            self::TO_RECONTACT => 'lead-status-to-recontact',
            self::FEASIBLE => 'lead-status-feasible',
            self::IN_NEGOTIATION => 'lead-status-in-negotiation',
        };
    }
}
