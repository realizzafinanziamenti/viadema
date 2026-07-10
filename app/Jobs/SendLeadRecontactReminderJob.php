<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\User;
use App\Notifications\LeadRecontactReminder;
use DateTime;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendLeadRecontactReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $leadId,
        public string $recontactDate
    ) {}

    public function backoff(): array
    {
        return [1, 5, 10];
    }

    public function retryUntil(): DateTime
    {
        return now()->addMinutes(30);
    }

    public function handle(): void
    {
        try {
            $lead = Customer::with('user')->find($this->leadId);

            if (! $lead || ! $lead->isLead()) {
                Log::info("Lead {$this->leadId} non valido per recontact reminder");
                return;
            }

            if (! $lead->recontact_date) {
                Log::info("Lead {$this->leadId} senza data ricontatto");
                return;
            }

            if ($lead->recontact_date->toDateString() !== $this->recontactDate) {
                Log::info("Lead {$this->leadId} ha cambiato data ricontatto");
                return;
            }

            if ($lead->recontact_date->isFuture()) {
                Log::info("Lead {$this->leadId} non ancora da ricontattare");
                return;
            }

            if ($lead->recontact_notified_for_date?->toDateString() === $this->recontactDate) {
                Log::info("Lead {$this->leadId} già notificato per {$this->recontactDate}");
                return;
            }

            $notifiables = collect();

            if ($lead->user) {
                $notifiables->push($lead->user);
            }

            $superadmins = User::role('superadmin')->get();

            $notifiables = $notifiables
                ->merge($superadmins)
                ->unique('id');

            $notifiables->each(
                fn (User $user) => $user->notify(new LeadRecontactReminder($lead))
            );

            $lead->update([
                'recontact_notified_for_date' => $this->recontactDate,
            ]);

            Log::info("Recontact reminder inviato per lead {$this->leadId}");
        } catch (Exception $e) {
            Log::error("Errore recontact reminder lead {$this->leadId}: " . $e->getMessage());
        }
    }
}
