<?php

namespace App\Livewire\Forms;

use App\Models\Event;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Masmerise\Toaster\Toaster;

class EventForm extends Form
{
    public ?Event $event;
    public string $title = '';
    public string $description = '';
    public ?Carbon $startDate = null;
    public ?Carbon $startTime = null;
    public ?Carbon $endTime = null;
    public $repeatUntil = null;

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'startDate' => 'required|date',
            'startTime' => 'required|date_format:H:i|after_or_equal:08:00|before_or_equal:21:00',
            'endTime' => 'required|date_format:H:i|after:startTime|after_or_equal:08:30|before_or_equal:22:00',
            'repeatUntil' => 'nullable|date|after:startDate|before_or_equal:' . Carbon::parse($this->startDate)->addMonths(1)->format('Y-m-d'),
        ];
    }

    protected function validationAttributes()
    {
        return [
            'title' => 'titolo',
            'description' => 'descrizione',
            'startDate' => 'data evento',
            'startTime' => 'ora inizio',
            'endTime' => 'ora fine',
            'repeatUntil' => 'ripeti fino a',
        ];
    }

    protected function messages()
    {
        return [
            'endTime.after' => 'L\'orario di fine evento deve essere uguale o successivo all\'orario di inizio evento',
            'repeatUntil.after' => 'La data deve essere successiva alla data di inizio evento',
            'repeatUntil.before_or_equal' => 'La data selezionata è oltre il consentito',
        ];
    }

    /**
     * Set event for update
     */
    public function setEvent(Event $event): void
    {
        $this->event = $event;
        $this->title = $event->title;
        $this->description = $event->description;
        $this->startDate = $event->start_date->format('Y-m-d');
        $this->startTime = $event->start_time->format('H:i');
        $this->endTime = $event->end_time->format('H:i');
        $this->repeatUntil = null;
    }

    /**
     * Create event
     */
    public function store()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $event = Event::create([
                    'user_id' => auth()->id(),
                    'title' => $this->title,
                    'description' => $this->description ?: null,
                    'start_date' => $this->startDate,
                    'start_time' => $this->startTime,
                    'end_time' => $this->endTime,
                ]);

                // generate recurring events
                if ($this->repeatUntil) {
                    $this->generateRecurringEvents($event, $this->repeatUntil);
                }
            });

            Toaster::success('Evento creato con successo');
        } catch (Exception $e) {
            Log::error('Errore durante la creazione dell\'evento: ' . $e->getMessage());
            Toaster::error('Si è verificato un errore: ' . $e->getMessage());
        }
    }

    /**
     * Update event
     */
    public function update()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $this->event->update([
                    'title' => $this->title,
                    'description' => $this->description ?: null,
                    'start_date' => $this->startDate,
                    'start_time' => $this->startTime,
                    'end_time' => $this->endTime,
                ]);
            });

            Toaster::success('Evento aggiornato con successo');
            return $this->event;
        } catch (Exception $e) {
            Log::error('Errore durante l\'aggiornamento dell\'evento: ' . $e->getMessage());
            Toaster::error('Si è verificato un errore: ' . $e->getMessage());
        }
    }

    /**
     * Generate recurring events
     */
    protected function generateRecurringEvents(Event $event, string $repeatUntil)
    {
        $nextDate = Carbon::parse($event->start_date)->addDay();

        while ($nextDate->lessThanOrEqualTo(Carbon::parse($repeatUntil))) {
            $newEvent = Event::create([
                'user_id' => $event->user_id,
                'title' => $event->title,
                'description' => $event->description ?: null,
                'start_date' => $nextDate->toDateString(),
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
            ]);

            $newEvent->secondaryAgents()->attach($event->secondaryAgents->pluck('id')->toArray());

            $nextDate->addDay();
        }
    }
}
