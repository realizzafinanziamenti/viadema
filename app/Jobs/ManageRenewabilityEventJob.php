<?php

namespace App\Jobs;

use App\Enums\EventAction;
use App\Enums\EventType;
use App\Models\Event;
use App\Models\Practice;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ManageRenewabilityEventJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Practice $practice, public EventAction $action)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $event = $this->practice->event;

            switch ($this->action) {
                case EventAction::CREATE:
                    $this->createRenewabilityEvent($this->practice);
                    break;

                case EventAction::UPDATE:
                    if ($event) {
                        $event->update(['start_date' => $this->practice->renewability_date]);
                    } else {
                        if ($this->practice->renewability_date) {
                            $this->createRenewabilityEvent($this->practice);
                        }
                    }
                    break;

                case EventAction::DELETE:
                    if ($event) {
                        $event->delete();
                    }
                    break;
            }
        } catch (Exception $e) {
            Log::error('Errore gestione evento pratica', [
                'practice_id' => $this->practice->id,
                'action' => $this->action,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a renewability event for the practice.
     */
    protected function createRenewabilityEvent(Practice $practice)
    {
        $practice->event()->create([
            'user_id' => $practice->user_id,
            'practice_id' => $practice->id,
            'event_type' => EventType::RENEWABILITY_PRACTICE->value,
            'title' => 'Rinnovo pratica ' . $practice->practice_code,
            'start_date' => $practice->renewability_date,
            'start_time' => Carbon::parse($practice->renewability_date)->format('H:i:s'),
            'end_time' => Carbon::parse($practice->renewability_date)->addHour()->format('H:i:s'),
        ]);
    }
}
