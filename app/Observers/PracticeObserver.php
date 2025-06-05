<?php

namespace App\Observers;

use App\Models\Installment;
use App\Models\Practice;
use Carbon\Carbon;

class PracticeObserver
{
    /**
     * Handle the Practice "created" event.
     */
    public function created(Practice $practice): void
    {
        //
    }

    /**
     * Handle the Practice "updated" event.
     */
    public function updated(Practice $practice): void
    {
        //
    }

    /**
     * Handle the Practice "saving" event.
     */
    public function saving(Practice $practice): void
    {
        if ($practice->isDirty([
            'installment_id',
            'first_installment_date',
            'renewability_percentage',
            'percentage_alert'
        ])) {
            if ($practice->installment_id && $practice->first_installment_date) {
                $installment = Installment::find($practice->installment_id);

                if ($installment) {
                    $totalInstallments = $installment->value;
                    $firstDate = Carbon::parse($practice->first_installment_date);

                    // Calcolo della data ultima rata
                    $practice->last_installment_date = $firstDate->copy()->addMonths($totalInstallments - 1);

                    // Calcolo delle rate di rinnovo e alert
                    $renewabilityInstallments = ceil($totalInstallments * ($practice->renewability_percentage / 100));
                    $alertInstallments = ceil($totalInstallments * ($practice->percentage_alert / 100));

                    // Calcolo delle date di rinnovo e alert
                    $practice->renewability_date = $firstDate->copy()->addMonths($renewabilityInstallments);
                    $practice->alert_date = $firstDate->copy()->addMonths($alertInstallments);
                }
            }
        }
    }

    /**
     * Handle the Practice "deleted" event.
     */
    public function deleted(Practice $practice): void
    {
        //
    }

    /**
     * Handle the Practice "restored" event.
     */
    public function restored(Practice $practice): void
    {
        //
    }

    /**
     * Handle the Practice "force deleted" event.
     */
    public function forceDeleted(Practice $practice): void
    {
        //
    }

    /**
     * Calculate the last installment date, renewability date and alert_date based on the first installment date and the number of installments.
     */
    public function calculateDates(Practice $practice): void
    {
        if ($practice->installment_id && $practice->first_installment_date) {
            $installment = Installment::find($practice->installment_id);

            if ($installment) {
                $totalInstallments = $installment->value;
                $firstDate = Carbon::parse($practice->first_installment_date);

                // Calcolo della data ultima rata
                $practice->last_installment_date = $firstDate->copy()->addMonths($totalInstallments - 1);

                // Calcolo delle rate di rinnovo e alert
                if ($totalInstallments > 0) {
                    $renewabilityInstallments = ceil($totalInstallments * ($practice->renewability_percentage / 100));
                    $alertInstallments = ceil($totalInstallments * ($practice->percentage_alert / 100));
                }

                // Calcolo delle date di rinnovo e alert
                $practice->renewability_date = $firstDate->copy()->addMonths($renewabilityInstallments);
                $practice->alert_date = $firstDate->copy()->addMonths($alertInstallments);
            }
        }
    }
}
