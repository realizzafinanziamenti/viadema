<?php

namespace App\Enums;

enum EventType: string
{
    case GENERAL = 'general';
    case RENEWABILITY_PRACTICE = 'renewability_practice';

    /**
     * Get the display label for the current status
     */
    public function getLabelText(): string
    {
        return match ($this) {
            self::GENERAL => 'Generico',
            self::RENEWABILITY_PRACTICE => 'Rinnovabilità pratica',
        };
    }
}
