<?php

namespace App\Jobs;

use App\Models\Practice;
use App\Notifications\PracticeRenewabilityAlert;
use DateTime;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendPracticeRenewabilityAlertJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Practice $practice)
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
            // Refresh the practice to ensure we have the latest data
            $practice = Practice::withTrashed()->find($this->practice->id);

            // Check if the practice still exists in the database
            // If it has been deleted, skip the notification
            if (is_null($practice) || $practice->trashed()) {
                Log::info('Pratica eliminata, notifica skippata.', [
                    'practice_id' => $this->practice->id,
                    'practice_code' => $this->practice->practice_code,
                    'user_id' => $this->practice->user_id,
                ]);
                return;
            }

            // Check if the alert date has changed since the job was scheduled
            // This prevents sending notifications if the alert date has been updated
            if ($this->practice->alert_date != $practice->alert_date) {
                Log::info('La data alert è cambiata, notifica skippata', [
                    'practice_id' => $this->practice->id,
                    'practice_code' => $this->practice->practice_code,
                    'user_id' => $this->practice->user_id,
                    'original_alert_date' => $this->practice->alert_date,
                    'current_alert_date' => $practice->alert_date,
                ]);
                return;
            }

            // practice's owner
            $user = $this->practice->user;
            // retrieve all superadmins
            $superadmins = User::role('superadmin')->get();
            // notify superadmins for every renewability alerts
            $superadmins->each(function ($superadmin) use ($practice) {
                $superadmin->notify(new PracticeRenewabilityAlert($practice));
            });
            // notify the practice's owner only if they are not a superadmin
            // to avoid duplicate notifications
            if ($user && !$superadmins->contains('id', $user->id)) {
                $user->notify(new PracticeRenewabilityAlert($this->practice));
            }
        } catch (Exception $e) {
            Log::error('Errore invio notifica rinnovabilità pratica', [
                'practice_id' => $this->practice->id,
                'practice_code' => $this->practice->practice_code,
                'user_id' => $this->practice->user_id,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
