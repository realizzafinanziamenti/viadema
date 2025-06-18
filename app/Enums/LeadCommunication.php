<?php

namespace App\Enums;

enum LeadCommunication: string
{
    case EMAIL = 'email';
    case PHONE = 'phone';
    case SMS = 'sms';
    case APPOINTMENT = 'appointment';
    case OFFER_SENT = 'offer-sent';

    /**
     * Get the display label for the current communication method
     */
    public function getLabelText(): string
    {
        return match ($this) {
            self::EMAIL => 'Email',
            self::PHONE => 'Chiamata Telefonica',
            self::SMS => 'SMS',
            self::APPOINTMENT => 'Appuntamento',
            self::OFFER_SENT => 'Offerta Inviata',
        };
    }
}
