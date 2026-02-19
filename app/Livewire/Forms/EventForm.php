<?php

namespace App\Livewire\Forms;

use App\Enums\UserDepartment;
use App\Models\Event;
use App\Models\User;
use App\Notifications\EventUpdated;
use App\Notifications\UserAddedToEvent;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Livewire\Form;
use Masmerise\Toaster\Toaster;

class EventForm extends Form
{
    public ?Event $event;
    public string $title = '';
    public ?string $description = null;
    public ?string $startDate = null;
    public ?string $startTime = null;
    public ?string $endTime = null;
    public $repeatUntil = null;
    public array $participants = [];

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:65535',
            'startDate' => 'required|date|after_or_equal:today',
            'startTime' => 'required|date_format:H:i|after_or_equal:08:00|before_or_equal:21:00',
            'endTime' => 'required|date_format:H:i|after:startTime|after_or_equal:08:30|before_or_equal:22:00',
            'repeatUntil' => 'nullable|date|after:startDate|before_or_equal:' . Carbon::parse($this->startDate)->addMonths(1)->format('Y-m-d'),
            'participants' => 'nullable|array',
            'participants.*' => [
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if ($user && $user->hasRole(UserDepartment::OBSERVER->value)) {
                        $fail('Gli utenti con ruolo osservatore non possono partecipare agli eventi.');
                    }

                    if ($user && $user->id === auth()->id()) {
                        $fail('Non puoi aggiungere te stesso come partecipante all\'evento.');
                    }
                }
            ],
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
            'participants' => 'partecipanti',
        ];
    }

    protected function messages()
    {
        return [
            'startDate.after_or_equal' => 'La data deve essere uguale o successiva alla data odierna',
            'endTime.after' => 'L\'orario di fine evento deve essere uguale o successivo all\'orario di inizio evento',
            'repeatUntil.after' => 'La data deve essere successiva alla data di inizio evento',
            'repeatUntil.before_or_equal' => 'La data selezionata è oltre il consentito',
            'participants.*.exists' => 'Uno o più partecipanti selezionati non sono validi',
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
        $this->startDate = $event->start_date?->format('Y-m-d');
        $this->startTime = $event->start_time?->format('H:i');
        $this->endTime = $event->end_time?->format('H:i');
        $this->repeatUntil = null;
        $this->participants = $event->participants()->pluck('user_id')->toArray();
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

                // attach participants if any
                if (!empty($this->participants)) {
                    $event->participants()->attach($this->participants);
                }

                $notifiedUserIds = array_values(array_unique(array_merge($this->participants, [$event->user_id])));

                Notification::send(
                    User::whereIn('id', $notifiedUserIds)->get(),
                    new UserAddedToEvent($event)
                );

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
                // get previous participants before update
                $previousParticipants = $this->event->participants()->pluck('user_id')->toArray();

                $this->event->update([
                    'title' => $this->title,
                    'description' => $this->description ?: null,
                    'start_date' => $this->startDate,
                    'start_time' => $this->startTime,
                    'end_time' => $this->endTime,
                ]);

                // Sync participants
                $this->event->participants()->sync($this->participants);

                // handle participant notifications
                $this->handleParticipantNotifications($previousParticipants);

                // log participant changes
                $this->logParticipantChanges($previousParticipants);
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

            // attach participants if any
            if (!empty($this->participants)) {
                $newEvent->participants()->attach($this->participants);
            }

            $notifiedUserIds = array_values(array_unique(array_merge($this->participants, [$newEvent->user_id])));

            Notification::send(
                User::whereIn('id', $notifiedUserIds)->get(),
                new UserAddedToEvent($newEvent)
            );

            $nextDate->addDay();
        }
    }

    /**
     * Handle participant notifications
     */
    protected function handleParticipantNotifications(array $previousParticipants)
    {
        $addedParticipants = array_diff($this->participants, $previousParticipants);
        $removedParticipants = array_diff($previousParticipants, $this->participants);
        $unchangedParticipants = array_intersect($previousParticipants, $this->participants);

        // notify new participants
        if (!empty($addedParticipants)) {
            Notification::send(
                User::whereIn('id', $addedParticipants)->get(),
                new UserAddedToEvent($this->event) // use the same notification as for new event
            );
        }

        // notify removed participants
        if (!empty($removedParticipants)) {
            Notification::send(
                User::whereIn('id', $removedParticipants)->get(),
                new EventUpdated($this->event, 'removed')
            );
        }

        // notify unchanged participants
        if (!empty($unchangedParticipants)) {
            Notification::send(
                User::whereIn('id', $unchangedParticipants)->get(),
                new EventUpdated($this->event, 'modified')
            );
        }

        // notify owner if not in participants and event modified
        Notification::send(
            $this->event->user,
            new EventUpdated($this->event, 'modified')
        );
    }

    /**
     * Log participant changes
     */
    protected function logParticipantChanges(array $previousParticipants)
    {
        $currentParticipants = $this->participants;

        sort($previousParticipants);
        sort($currentParticipants);

        // Se sono identici (stesso contenuto, stesso ordine), esci
        if ($previousParticipants === $currentParticipants) {
            return;
        }

        $previousParticipantsNames = User::whereIn('id', $previousParticipants)
            ->get()
            ->map(fn($user) => $user->full_name)
            ->toArray();

        $currentParticipantsNames = User::whereIn('id', $currentParticipants)
            ->get()
            ->map(fn($user) => $user->full_name)
            ->toArray();

        activity('event_participants')
            ->causedBy(auth()->user())
            ->performedOn($this->event)
            ->event('updated')
            ->withProperties([
                'participants' => [
                    'old' => $previousParticipantsNames,
                    'new' => $currentParticipantsNames,
                ],
                'event_info' => [
                    'title' => $this->event->title,
                    'start_date' => $this->event->start_date?->format('d/m/Y'),
                    'start_time' => $this->event->start_time?->format('H:i'),
                ],
                'url' => url('/calendar?date=' . $this->event->start_date->format('Y-m-d')),
            ])
            ->log('Partecipanti evento aggiornati');
    }
}
