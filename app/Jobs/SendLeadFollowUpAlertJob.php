<?php

namespace App\Jobs;

use App\Enums\LeadStatus;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\LeadFollowUp;
use DateTime;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendLeadFollowUpAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $leadId)
    {
        //
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [1, 5, 10];
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): DateTime
    {
        return now()->addMinutes(30);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $lead = Customer::with('user')->find($this->leadId);

            // Controlli di sicurezza
            if (!$lead || !$lead->isLead() || $lead->lead_status !== LeadStatus::NEW) {
                Log::info("Lead {$this->leadId} non più valido per follow-up notification");
                return;
            }

            // Trova utenti da notificare
            if ($lead->user) {
                $lead->user->notify(new LeadFollowUp($lead));
            } else {
                // retrieve all superadmins
                $superadmins = User::role('superadmin')
                    ->get();

                // notify superadmins for every renewability alerts
                $superadmins->each(function ($superadmin) use ($lead) {
                    $superadmin->notify(new LeadFollowUp($lead));
                });
            }

            Log::info("Follow-up notification inviata per lead {$this->leadId}");
        } catch (Exception $e) {
            Log::error("Errore nell'invio della follow-up notification per lead {$this->leadId}: " . $e->getMessage());
        }
    }
}
