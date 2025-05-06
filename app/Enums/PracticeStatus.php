<?php

namespace App\Enums;

enum PracticeStatus: int
{
    case UNDER_REVIEW = 1;
    case REJECTED = 2;
    case APPROVED = 3;
    case SUSPENDED = 4;
    case PENDING = 5;
    case DISBURSED = 6;

    /**
     * Check if the current status is the same as the given status
     */
    public function isStatus(PracticeStatus $status): bool
    {
        return $this === $status;
    }

    /**
     * Get the display label for the current role
     */
    public function getLabelText(): string
    {
        return match ($this) {
            self::UNDER_REVIEW => 'Istruttoria',
            self::REJECTED => 'Respinta',
            self::APPROVED => 'Deliberata',
            self::SUSPENDED => 'Sospesa',
            self::PENDING => 'In Attesa',
            self::DISBURSED => 'Liquidata',
        };
    }

    /**
     * Get the CSS classes for styling the label based on the role
     */
    public function getLabelColor(): string
    {
        return match ($this) {
            self::UNDER_REVIEW => 'under-review',
            self::REJECTED => 'rejected',
            self::APPROVED => 'approved',
            self::SUSPENDED => 'suspended',
            self::PENDING => 'pending',
            self::DISBURSED => 'disbursed',
        };
    }
}
