<?php

namespace App\Enums;

enum LeadStatus: string
{
    case NEW = 'new';
    case TO_RECONTACT = 'to_recontact';
    case IN_NEGOTIATION = 'in_negotiation';
    case NOT_FEASIBLE = 'not_feasible';
    case FEASIBLE = 'feasible';
    case AWAITING_DOCUMENTATION = 'Attesa documentazione';
    case NOT_INTERESTED = 'Non interessato';
    case AWAITING_CDS = 'Attesa CDS';
    case INTERNAL_RENEWAL = 'Rinnovo interno';
    case EXTERNAL_RENEWAL = 'Rinnovo esterno';
    case INTERNAL_RENEWAL_NOT_INTERESTED = 'Rinnovo interno N.I.';
    case EXTERNAL_RENEWAL_NOT_INTERESTED = 'Rinnovo esterno N.I.';
    case UNREACHABLE = 'Irreperibile';
    case NOT_ANSWERING = 'Non risponde';
    case INTERESTED = 'Interessato';

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
            self::AWAITING_DOCUMENTATION => 'Attesa Doc',
            self::NOT_INTERESTED=> 'Non interessato',
            self::AWAITING_CDS => 'Attesa CDS',
            self::INTERNAL_RENEWAL => 'Rinnovo interno',
            self::EXTERNAL_RENEWAL => 'Rinnovo esterno',
            self::INTERNAL_RENEWAL_NOT_INTERESTED => 'Rinnovo interno N.I.',
            self::EXTERNAL_RENEWAL_NOT_INTERESTED => 'Rinnovo esterno N.I.',
            self::UNREACHABLE => 'Irreperibile',
            self::NOT_ANSWERING => 'Non risponde',
            self::INTERESTED => 'Interessato',
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
            self::AWAITING_DOCUMENTATION=>'lead-status-in-negotiation',
            self::NOT_INTERESTED=> 'lead-status-not-feasible',
            self::AWAITING_CDS => 'lead-status-in-negotiation',
            self::INTERNAL_RENEWAL => 'lead-status-to-recontact',
            self::EXTERNAL_RENEWAL => 'lead-status-to-recontact',
            self::INTERNAL_RENEWAL_NOT_INTERESTED => 'lead-status-in-negotiation',
            self::EXTERNAL_RENEWAL_NOT_INTERESTED => 'lead-status-in-negotiation',
            self::UNREACHABLE => 'lead-status-in-negotiation',
            self::NOT_ANSWERING => 'lead-status-in-negotiation',
            self::INTERESTED => 'lead-status-new',

        };
    }
}
