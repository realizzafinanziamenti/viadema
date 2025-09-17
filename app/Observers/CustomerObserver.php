<?php

namespace App\Observers;

use App\Enums\LeadStatus;
use App\Jobs\SendLeadFollowUpAlertJob;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

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
                ->delay(now()->addMinutes(6))
                ->afterCommit();

            Log::info("Follow-up notification programmata per lead {$customer->id} tra 6 ore");
        }
    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        //
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
