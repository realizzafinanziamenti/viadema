<?php

namespace App\Enums;

enum PracticeStatus: string
{
    case UNDER_REVIEW = 'under_review';
    case REJECTED = 'rejected';
    case APPROVED = 'approved';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';
    case DISBURSED = 'disbursed';

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

    /**
     * Get all statuses except DISBURSED
     *
     * @return PracticeStatus[]
     */
    public static function labelsWithoutDisbursed(): array
    {
        return collect(self::cases())
            ->reject(fn(self $status) => $status === self::DISBURSED)
            ->mapWithKeys(fn(self $status) => [$status->value => $status->getLabelText()])
            ->toArray();
    }
}
