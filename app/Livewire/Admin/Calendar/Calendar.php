<?php

namespace App\Livewire\Admin\Calendar;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

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
    public  $previousMonthEvents = []; //  to hold events from the previous month - view mode month
    public  $currentMonthEvents = []; //  to hold events from the current month - view mode month
    public  $nextMonthEvents = []; //  to hold events from the next month - view mode month
    public  $currentWeekEvents = []; //  to hold events from the current week - view mode week
    public  $currentDayEvents = []; //  to hold events from the current day - view mode day
    public string $search = ''; // search property to hold the search term

    /**
     * Set the initial date to the current date.
     */
    public function setInitialDate()
    {
        $this->currentDate = Carbon::now();
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
            ->orderBy('starts_at', 'asc');

        if ($this->viewMode === 'month') {
            // 🔹 Eventi del mese precedente
            $this->previousMonthEvents = (clone $query)
                ->whereBetween('starts_at', [$this->firstCalendarDate, Carbon::create($this->currentYear, $this->currentMonth, 1)->subDay()->toDateString()])
                ->get();

            // 🔹 Eventi del mese corrente
            $this->currentMonthEvents = (clone $query)
                ->whereMonth('starts_at', $this->currentMonth)
                ->whereYear('starts_at', $this->currentYear)
                ->get();

            // 🔹 Eventi del mese successivo
            $nextMonthYear = $this->currentMonth == 12 ? $this->currentYear + 1 : $this->currentYear;
            $nextMonth = $this->currentMonth == 12 ? 1 : $this->currentMonth + 1;

            $this->nextMonthEvents = (clone $query)
                ->whereBetween('starts_at', [Carbon::create($nextMonthYear, $nextMonth, 1)->toDateString(), $this->lastCalendarDate])
                ->get();
        }

        if ($this->viewMode === 'week') {
            $this->currentWeekEvents = (clone $query)
                ->whereBetween('starts_at', [$this->currentWeekStart, $this->currentWeekEnd])
                ->get();
        }

        if ($this->viewMode === 'day') {
            $this->currentDayEvents = (clone $query)
                ->whereDate('starts_at', $this->currentDate->toDateString())
                ->get();
        }
    }

    /**
     * refresh calendar function
     */
    protected function refreshCalendar(): void
    {
        $this->applyViewData();
        $this->loadEvents();
    }

    public function mount()
    {
        Gate::authorize('access calendar');
        $this->setInitialDate();
        $this->refreshCalendar();

        // dd($this->previousMonthEvents, $this->currentMonthEvents, $this->nextMonthEvents, $this->currentWeekEvents, $this->currentDayEvents);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.calendar.calendar');
    }
}
