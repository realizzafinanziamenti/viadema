<?php

namespace App\Observers;

use App\Enums\EventAction;
use App\Jobs\ManageRenewabilityEventJob;
use App\Jobs\SendPracticeRenewabilityAlertJob;
use App\Models\Practice;
use App\Models\PracticeOpportunity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PracticeObserver
{
    /**
     * Handle the Practice "created" event.
     */
    public function created(Practice $practice): void
    {
        if ($practice->renewability_date) {
            dispatch(new ManageRenewabilityEventJob($practice, EventAction::CREATE))
                ->afterCommit();
        }

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
        if ($practice->isDirty('renewability_date')) {
            dispatch(new ManageRenewabilityEventJob($practice, EventAction::UPDATE))
                ->afterCommit();
        }

        if ($practice->isDirty('alert_date') && $practice->alert_date) {
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
        $this->calculateDates($practice);
    }

    /**
     * Handle the Practice "deleted" event.
     */
    public function deleted(Practice $practice): void
    {
        dispatch(new ManageRenewabilityEventJob($practice, EventAction::DELETE))->afterCommit();

        DB::table('notifications')
            ->where('type', 'practice-renewability-alert')
            ->whereJsonContains('data->practice_id', $practice->id)
            ->delete();

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
        foreach ($practice->attachments as $attachment) {
            $attachment->forceDelete();
        }

        dispatch(new ManageRenewabilityEventJob($practice, EventAction::DELETE))->afterCommit();
    }

    /**
     * Calculate renewability_date and alert_date from the related PracticeOpportunity.
     */
    private function calculateDates(Practice $practice): void
    {
        $opportunity = $this->resolveOpportunity($practice);

        if (! $opportunity || ! $opportunity->first_installment_date) {
            $practice->renewability_date = null;
            $practice->alert_date = null;

            return;
        }

        $totalInstallments = $this->resolveTotalInstallments($opportunity);

        if (! $totalInstallments || $totalInstallments <= 0) {
            $practice->renewability_date = null;
            $practice->alert_date = null;

            return;
        }

        $firstDate = Carbon::parse($opportunity->first_installment_date);

        $practice->renewability_date = $opportunity->renewability_percentage !== null
            ? $firstDate->copy()->addMonthsNoOverflow(
                (int) ceil($totalInstallments * ((float) $opportunity->renewability_percentage / 100))
            )
            : null;

        $practice->alert_date = $opportunity->percentage_alert !== null
            ? $firstDate->copy()->addMonthsNoOverflow(
                (int) ceil($totalInstallments * ((float) $opportunity->percentage_alert / 100))
            )
            : null;
    }

    private function resolveOpportunity(Practice $practice): ?PracticeOpportunity
    {
        if ($practice->relationLoaded('opportunity')) {
            return $practice->opportunity;
        }

        if (! $practice->practice_opportunity_id) {
            return null;
        }

        return PracticeOpportunity::with('installment')
            ->find($practice->practice_opportunity_id);
    }

    private function resolveTotalInstallments(PracticeOpportunity $opportunity): ?int
    {
        $opportunity->loadMissing('installment');

        if ($opportunity->installment?->value) {
            return (int) $opportunity->installment->value;
        }

        if ($opportunity->first_installment_date && $opportunity->last_installment_date) {
            $firstDate = Carbon::parse($opportunity->first_installment_date)->startOfDay();
            $lastDate = Carbon::parse($opportunity->last_installment_date)->startOfDay();

            if ($lastDate->greaterThanOrEqualTo($firstDate)) {
                return max(1, $firstDate->diffInMonths($lastDate));
            }
        }

        return null;
    }
}
