<?php

namespace App\Observers;

use App\Enums\EventAction;
use App\Enums\EventType;
use App\Jobs\ManageRenewabilityEventJob;
use App\Models\Installment;
use App\Models\Practice;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class PracticeObserver
{
    /**
     * Handle the Practice "created" event.
     */
    public function created(Practice $practice): void
    {
        // Create an event for renewability date
        if ($practice->renewability_date) {
            dispatch(new ManageRenewabilityEventJob($practice, EventAction::CREATE))->afterCommit();
        }
    }

    /**
     * Handle the Practice "updated" event.
     */
    public function updated(Practice $practice): void
    {
        // Update the event if the renewability date has changed
        if ($practice->isDirty('renewability_date')) {
            dispatch(new ManageRenewabilityEventJob($practice, EventAction::UPDATE))->afterCommit();
        }
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
            $this->calculateDates($practice);
        }
    }

    /**
     * Handle the Practice "deleted" event.
     */
    public function deleted(Practice $practice): void
    {
        // Delete the associated event if it exists
        dispatch(new ManageRenewabilityEventJob($practice, EventAction::DELETE))->afterCommit();
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
                $practice->last_installment_date = $firstDate->copy()->addMonthsNoOverflow($totalInstallments - 1);

                // Calcolo delle rate di rinnovo e alert
                if ($totalInstallments > 0) {
                    $renewabilityInstallments = ceil($totalInstallments * ($practice->renewability_percentage / 100));
                    $alertInstallments = ceil($totalInstallments * ($practice->percentage_alert / 100));

                    // Calcolo data di rinnovo
                    $practice->renewability_date = $practice->renewability_percentage !== null
                        ? $firstDate->copy()->addMonthsNoOverflow($renewabilityInstallments)
                        : null;

                    // Calcolo data alert
                    $practice->alert_date = $practice->percentage_alert !== null
                        ? $firstDate->copy()->addMonthsNoOverflow($alertInstallments)
                        : null;
                }
            }
        }
    }
}
