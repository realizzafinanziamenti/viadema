<?php

namespace App\Observers;

use App\Enums\LeadStatus;
use App\Jobs\SendLeadFollowUpAlertJob;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendLeadRecontactReminderJob;
use Carbon\Carbon;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        // when a new lead is created with status NEW, schedule a follow-up notification
        if ($customer->isLead() && $customer->lead_status === LeadStatus::NEW) {
            // schedule the notification for 6 hours later
            dispatch(new SendLeadFollowUpAlertJob($customer->id))
                ->delay(now()->addHours(6))
                ->afterCommit();

            Log::info("Follow-up notification programmata per lead {$customer->id} tra 6 ore");
        }
        $this->scheduleLeadRecontactReminder($customer);
    }

    private function scheduleLeadRecontactReminder(Customer $customer): void
        {
            if (! $this->canScheduleLeadRecontactReminder($customer)) {
                return;
            }

            $recontactDate = $customer->recontact_date->toDateString();
            $delay = $this->getLeadRecontactReminderDelay($customer);

            dispatch(new SendLeadRecontactReminderJob(
                leadId: $customer->id,
                recontactDate: $recontactDate
            ))
                ->delay($delay)
                ->afterCommit();

            Log::info("Recontact reminder programmato per lead {$customer->id} in data {$recontactDate}");
        }

private function canScheduleLeadRecontactReminder(Customer $customer): bool
        {
            if (! $customer->isLead()) {
                return false;
            }

            if (! in_array($customer->lead_status, $this->leadRecontactReminderStatuses(), true)) {
                return false;
            }

            if (! $customer->recontact_date) {
                return false;
            }

            if ($customer->recontact_notified_for_date?->toDateString() === $customer->recontact_date->toDateString()) {
                return false;
            }

            return true;
        }

private function leadRecontactReminderStatuses(): array
        {
            return [
                LeadStatus::NOT_FEASIBLE,
                LeadStatus::NOT_INTERESTED,
            ];
        }

        private function getLeadRecontactReminderDelay(Customer $customer): Carbon
        {
            if ($customer->recontact_date->lessThanOrEqualTo(today())) {
                return now();
            }

            return $customer->recontact_date->copy()->setTime(9, 0);
        }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
{
    if (! $customer->wasChanged([
        'customer_status',
        'lead_status',
        'recontact_date',
        'user_id',
    ])) {
        return;
    }

    $this->scheduleLeadRecontactReminder($customer);
}

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "restored" event.
     */
    public function restored(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "force deleted" event.
     */
    public function forceDeleted(Customer $customer): void
    {
        //
    }
}
