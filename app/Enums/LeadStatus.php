<?php

namespace App\Enums;

enum LeadStatus: string
{
    case NEW = 'new';
    case NOT_FEASIBLE = 'not_feasible';
    case TO_RECONTACT = 'to_recontact';
    case FEASIBLE = 'feasible';
    case IN_NEGOTIATION = 'in_negotiation';

    /**
     * Get the display label for the current lead status
     */
    public function getLabelText(): string
    {
        return match ($this) {
            self::NEW => 'Nuovo',
            self::NOT_FEASIBLE => 'Non Fattibile',
            self::TO_RECONTACT => 'Da Ricontattare',
            self::FEASIBLE => 'Fattibile',
            self::IN_NEGOTIATION => 'In Trattativa',
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
