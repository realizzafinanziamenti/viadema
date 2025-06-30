<?php

namespace App\Enums;

enum LeadSource: string
{
    case TIK_TOK = 'tik_tok';
    case META = 'meta';
    case SEARCH_ENGINE = 'search_engine';
    case REFERRAL = 'referral';

    /**
     * Get the display label for the current lead source
     */
    public function getLabelText(): string
    {
        return match ($this) {
            self::TIK_TOK => 'Tik Tok',
            self::META => 'Meta',
            self::SEARCH_ENGINE => 'Motore di Ricerca',
            self::REFERRAL => 'Passaparola',
            self::OTHER => 'Other',
        };
    }
}
