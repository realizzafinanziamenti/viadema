<?php

namespace App\Enums;

enum PracticeStatus: string
{
    case UNDER_REVIEW = 'under_review';
    case REJECTED = 'rejected';
    case APPROVED = 'approved';
    case SUSPENDED = 'suspended';
    case DISBURSED = 'disbursed';
    case NOTICED = 'Notificata';
    case AWAITING_POLICY = 'Attesa polizza';
    case AWAITING_RENEWAL = 'Attesa rinnovabilità';
    case UNDER_EVALUATION = 'In valutazione';
    case AWAITING_SETTLEMENT = 'Attesa conteggio';

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
            self::DISBURSED => 'Liquidata',
            self::NOTICED => 'Notificata',
            self::AWAITING_POLICY=>'Attesa polizza',
            self::AWAITING_RENEWAL=>'Attesa rinnovo',
            self::UNDER_EVALUATION=>'In valutazione',
            self::AWAITING_SETTLEMENT=>'Attesa conteggio'
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
            self::DISBURSED => 'disbursed',
            self::NOTICED => 'approved',
            self::AWAITING_POLICY =>'suspended',
            self::AWAITING_RENEWAL =>'awaiting-renew',
            self::UNDER_EVALUATION => 'suspended',
            self::AWAITING_SETTLEMENT=> 'suspended'

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