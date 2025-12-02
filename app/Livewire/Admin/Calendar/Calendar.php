<?php

namespace App\Livewire\Admin\Calendar;

use App\Livewire\Forms\EventForm;
use App\Models\Event;
use App\Models\User;
use App\Notifications\EventUpdated;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class Calendar extends Component
{
    public ?Carbon $currentDate; // currentDate property to hold the current date
    public ?int $currentYear = null; // property to hold the current year
    public ?int $currentMonth = null; // property to hold the current month
    public ?Carbon $currentWeekStart = null; // property to hold the start of the current week
    public ?Carbon $currentWeekEnd = null; // property to hold the end of the current week
    public string $viewMode = 'month'; // view mode property to determine the view mode (day, week, month)
    public ?int $daysInCurrentMonth = null; // property to hold the number of days in the current month
    public ?int $daysInPrevMonth = null; // property to hold the number of days in the previous month
    public ?int $daysInNextMonth = null; // property to hold the number of days in the next month
    public ?int $firstDayOfMonth = null; // property to hold the first day of the month (0 = Monday, 6 = Sunday)
    public ?int $prevMonthStart = null; // property to hold the start of the previous month
    public ?int $totalDays = null; // property to hold the total number of days in the calendar
    public ?int $totalWeeks = null; // property to hold the total number of weeks in the calendar
    public ?string $firstCalendarDate = null; // property to hold the first date in the calendar
    public ?string $lastCalendarDate = null; // property to hold the last date in the calendar
    public  $previousMonthEvents = []; //  to hold events from the previous month - view mode month
    public  $currentMonthEvents = []; //  to hold events from the current month - view mode month
    public  $nextMonthEvents = []; //  to hold events from the next month - view mode month
    public  $currentWeekEvents = []; //  to hold events from the current week - view mode week
    public  $currentDayEvents = []; //  to hold events from the current day - view mode day
    public string $search = ''; // search property to hold the search term
    public EventForm $form; // form property to hold the event form
    public ?Event $selectedEvent = null; // property to hold the selected event
    public Collection $possibleParticipants; // property to hold the possible participants for events

    /**
     * Set the initial date to the current date.
     */
    public function setInitialDate()
    {
        // check if there's a date query parameter in the URL
        $date = request()->query('date');
        // set current date to the date from the URL or to today if not present or invalid
        $this->currentDate = (!empty($date)) ? Carbon::parse($date) : Carbon::now();
        $this->currentYear = $this->currentDate->year;
        $this->currentMonth = $this->currentDate->month;
    }

    /**
     * Change calendar view mode
     */
    public function setViewMode(string $mode)
    {
        $this->viewMode = $mode;
        // $this->setInitialDate();  // decommenta se vuoi resettare la data iniziale quando cambi modalità
        $this->refreshCalendar();
    }

    /**
     * Set the current date to today
     */
    public function setToday()
    {
        $this->currentDate = Carbon::now();
        $this->refreshCalendar();
    }

    /**
     * aggiorna i dati in base alla data corrente
     */
    public function applyViewData()
    {
        if ($this->viewMode === 'month') {
            $this->currentMonth = $this->currentDate->month;
            $this->currentYear = $this->currentDate->year;
            $this->calculateMonthData();
        }

        if ($this->viewMode === 'week') {
            $this->currentWeekStart = $this->currentDate->copy()->startOfWeek();
            $this->currentWeekEnd = $this->currentDate->copy()->endOfWeek();
            $this->calculateWeekData();
        }
    }

    /**
     * Calculate month data
     */
    public function calculateMonthData()
    {
        // get days in current month
        $this->daysInCurrentMonth = $this->currentDate->daysInMonth;
        // get first day of current month (0 = Monday, 6 = Sunday)
        $firstDay = $this->currentDate->copy()->startOfMonth();
        $this->firstDayOfMonth = $firstDay->dayOfWeek === 0 ? 6 : $firstDay->dayOfWeek - 1;
        // get previous month days
        $prevMonth = $this->currentDate->copy()->subMonth();
        $this->daysInPrevMonth = $prevMonth->daysInMonth;
        // get previous month start
        $this->prevMonthStart = $this->daysInPrevMonth - $this->firstDayOfMonth + 1;
        // calculate total days and weeks in calendar
        $this->totalDays = $this->firstDayOfMonth + $this->daysInCurrentMonth;
        $this->totalWeeks = ceil($this->totalDays / 7);
        // get first calendar date
        $this->firstCalendarDate = Carbon::create($this->currentYear, $this->currentMonth, 1)
            ->subDays($this->firstDayOfMonth)
            ->toDateString();
        // get last calendar date
        $this->lastCalendarDate = Carbon::create($this->currentYear, $this->currentMonth, $this->daysInCurrentMonth)
            ->addDays(($this->totalWeeks * 7) - $this->totalDays)
            ->toDateString();
        // get days in next month
        $this->daysInNextMonth = ($this->totalWeeks * 7) - $this->totalDays;
    }

    /**
     * Calculate week data
     */
    public function calculateWeekData()
    {
        //
    }

    /**
     * Navigate to previous month, week, day
     */
    public function prev()
    {
        $this->currentDate = match ($this->viewMode) {
            'month' => $this->currentDate->copy()->subMonth(),
            'week'  => $this->currentDate->copy()->subWeek(),
            'day'   => $this->currentDate->copy()->subDay(),
            default => $this->currentDate,
        };

        $this->refreshCalendar();
    }

    /**
     * Navigate to next month, week, day
     */
    public function next()
    {
        $this->currentDate = match ($this->viewMode) {
            'month' => $this->currentDate->copy()->addMonth(),
            'week'  => $this->currentDate->copy()->addWeek(),
            'day'   => $this->currentDate->copy()->addDay(),
            default => $this->currentDate,
        };

        $this->refreshCalendar();
    }

    /**
     * Load events
     */
    public function loadEvents()
    {
        $query = Event::with('user')
            ->visibleByUser(auth()->user())
            ->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc');

        if ($this->viewMode === 'month') {
            // 🔹 Eventi del mese precedente
            $this->previousMonthEvents = (clone $query)
                ->whereBetween('start_date', [$this->firstCalendarDate, Carbon::create($this->currentYear, $this->currentMonth, 1)->subDay()->toDateString()])
                ->get();

            // 🔹 Eventi del mese corrente
            $this->currentMonthEvents = (clone $query)
                ->whereMonth('start_date', $this->currentMonth)
                ->whereYear('start_date', $this->currentYear)
                ->get();

            // 🔹 Eventi del mese successivo
            $nextMonthYear = $this->currentMonth == 12 ? $this->currentYear + 1 : $this->currentYear;
            $nextMonth = $this->currentMonth == 12 ? 1 : $this->currentMonth + 1;

            $this->nextMonthEvents = (clone $query)
                ->whereBetween('start_date', [Carbon::create($nextMonthYear, $nextMonth, 1)->toDateString(), $this->lastCalendarDate])
                ->get();
        }

        if ($this->viewMode === 'week') {
            $this->currentWeekEvents = (clone $query)
                ->whereBetween('start_date', [$this->currentWeekStart, $this->currentWeekEnd])
                ->get();
        }

        if ($this->viewMode === 'day') {
            $this->currentDayEvents = (clone $query)
                ->whereDate('start_date', $this->currentDate->toDateString())
                ->get();
        }
    }

    /**
     * load possible participants for events
     */
    public function loadPossibleParticipants(string $action = 'create')
    {
        $query = User::assignableUsers()
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($action === 'create') {
            // Per la creazione, escludi solo l'utente autenticato
            $query->excludeAuthenticatedUser();
        } elseif ($action === 'update') {
            // Per l'aggiornamento, escludi l'owner dell'evento
            $query->excludeEventOwner($this->selectedEvent?->user_id);
        }

        $this->possibleParticipants = $query->get();
    }

    /**
     * refresh calendar function
     */
    protected function refreshCalendar(): void
    {
        $this->applyViewData();
        $this->loadEvents();
    }

    /**
     * open create event modal
     */
    public function openCreateEventModal(): void
    {
        Gate::authorize('create', Event::class);
        $this->resetErrorBag();
        $this->form->reset();
        $this->dispatch('open-modal', 'event-create');
    }

    /**
     * open edit event modal
     */
    public function openEditEventModal(): void
    {
        Gate::authorize('update', $this->selectedEvent);
        $this->resetErrorBag();
        $this->form->setEvent($this->selectedEvent);
        $this->loadPossibleParticipants('update');
        $this->dispatch('open-modal', 'event-edit');
    }

    /**
     * open detail event modal
     */
    public function openDetailEventModal(int $id): void
    {
        $this->selectedEvent = Event::findOrFail($id);
        Gate::authorize('view', $this->selectedEvent);
        $this->dispatch('open-modal', 'event-detail');
    }

    /**
     * save event
     */
    public function save()
    {
        Gate::authorize('create', Event::class);
        $this->form->store();
        $this->loadEvents();
        $this->dispatch('close-modal', 'event-create');
    }

    /**
     * edit event
     */
    public function edit()
    {
        Gate::authorize('update', $this->selectedEvent);
        $this->selectedEvent = $this->form->update();
        $this->loadEvents();
        $this->dispatch('close-modal', 'event-edit');
        $this->dispatch('close-modal', 'event-detail');
    }

    /**
     * Delete event function
     */
    public function delete(): void
    {
        try {
            Gate::authorize('delete', $this->selectedEvent);

            DB::transaction(function () {
                $this->sendNotificationOnDeleteEvent($this->selectedEvent);
                $this->selectedEvent->delete();
            });

            Toaster::success('Evento eliminato con successo');
        } catch (Exception $e) {
            Log::error('Errore durante l\'eliminazione dell\'evento: ' . $e->getMessage());
            Toaster::error('Si è verificato un errore: ' . $e->getMessage());
        }

        $this->selectedEvent = null;
        $this->loadEvents();
        $this->dispatch('close-modal', 'event-detail');
        $this->dispatch('close-modal', 'event-delete');
    }

    /**
     * Send notification to participants on event deletion
     */
    protected function sendNotificationOnDeleteEvent(Event $event): void
    {
        // notify participants about event deletion
        $participants = $event->participants;
        if ($participants->isNotEmpty()) {
            Notification::send(
                $participants,
                new EventUpdated($event, 'cancelled')
            );
        }

        // notify owner if deleted by admin
        if (auth()->id() !== $event->user_id) {
            if ($event->user) {
                Notification::send(
                    $event->user,
                    new EventUpdated($event, 'cancelled')
                );
            }
        }
    }

    public function mount()
    {
        Gate::authorize('access calendar');
        $this->setInitialDate();
        $this->refreshCalendar();
        $this->loadPossibleParticipants('create');
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.calendar.calendar');
    }
}
