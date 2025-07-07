<?php

namespace App\Observers;

use App\Enums\EventAction;
use App\Enums\EventType;
use App\Jobs\ManageRenewabilityEventJob;
use App\Jobs\SendPracticeRenewabilityAlertJob;
use App\Models\Installment;
use App\Models\Practice;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;

class PracticeObserver
{
    /**
     * Handle the Practice "created" event.
     */
    public function created(Practice $practice): void
    {
        // Create an event for renewability date
        if ($practice->renewability_date) {
            dispatch(new ManageRenewabilityEventJob($practice, EventAction::CREATE))
                ->afterCommit();
        }

        // Schedule a job to send an alert notification
        if ($practice->alert_date) {
            dispatch(new SendPracticeRenewabilityAlertJob($practice))
                ->delay($practice->alert_date)
                ->afterCommit();
        }
    }

    /**
     * Handle the Practice "updated" event.
     */
    public function updated(Practice $practice): void
    {
        // Update the event if the renewability date has changed
        if ($practice->isDirty('renewability_date')) {
            dispatch(new ManageRenewabilityEventJob($practice, EventAction::UPDATE))
                ->afterCommit();
        }

        if ($practice->alert_date) {
            dispatch(new SendPracticeRenewabilityAlertJob($practice))
                ->delay($practice->alert_date)
                ->afterCommit();
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

        // Set snapshot values for the Practice model
        if ($practice->isDirty([
            'product_subtype_id',
            'financial_table_id',
            'insurance_id',
            'installment_id',
            'customer_type_id'
        ])) {
            $this->setSnapshotValues($practice);
        }
    }

    /**
     * Handle the Practice "deleted" event.
     */
    public function deleted(Practice $practice): void
    {
        // Delete the associated event if it exists
        dispatch(new ManageRenewabilityEventJob($practice, EventAction::DELETE))->afterCommit();

        foreach ($practice->attachments as $attachment) {
            $attachment->delete();
        }
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
        // If the practice is force deleted, we can also delete the attachments
        foreach ($practice->attachments as $attachment) {
            $attachment->forceDelete();
        }

        // Optionally, you can also delete any related events or notifications
        dispatch(new ManageRenewabilityEventJob($practice, EventAction::DELETE))->afterCommit();
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

    /**
     * Set snapshot values for the Practice model based on related models.
     *
     * @param Practice $practice
     */
    public function setSnapshotValues(Practice $practice): void
    {
        // Set snapshot values based on the related models
        if ($practice->productSubtype) {
            $practice->product_subtype_label = Str::of($practice->productSubtype->name)->trim();
        }

        if ($practice->financialTable) {
            $practice->financial_table_percentage = $practice->financialTable->percentage;
        }

        if ($practice->insurance) {
            $practice->insurance_label = Str::of($practice->insurance->name)->trim();
        }

        if ($practice->installment) {
            $practice->installment_value_label = $practice->installment->value;
        }

        if ($practice->customerType) {
            $practice->customer_type_label = Str::of($practice->customerType->name)->trim();
        }
    }
}
