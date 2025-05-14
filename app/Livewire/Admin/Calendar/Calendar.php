<?php

namespace App\Livewire\Admin\Calendar;

use Carbon\Carbon;
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
    public array $events = []; // events property to hold the events for the calendar
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
        $this->setInitialDate();

        match ($mode) {
            'month' => $this->calculateMonthData(),
            'week' => $this->calculateWeekData(),
            'day' => null,
            default => null,
        };
    }

    /**
     * Calculate month data
     */
    public function calculateMonthData()
    {
        if ($this->viewMode === 'month') {
            // get days in current month
            $this->daysInCurrentMonth = Carbon::create($this->currentYear, $this->currentMonth)->daysInMonth;
            // get first day of current month (0 = Monday, 6 = Sunday)
            $firstDay = Carbon::create($this->currentYear, $this->currentMonth, 1);
            $this->firstDayOfMonth = $firstDay->dayOfWeek === 0 ? 6 : $firstDay->dayOfWeek - 1;
            // get previous month days
            $prevMonth = Carbon::create($this->currentYear, $this->currentMonth)->subMonth();
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

        // $this->loadEvents();
    }

    /**
     * Calculate week data
     */
    public function calculateWeekData()
    {
        if ($this->viewMode === 'week') {
            if (!$this->currentWeekStart) {
                $this->currentWeekStart = Carbon::now()->startOfWeek();
            }
            if (!$this->currentWeekEnd) {
                $this->currentWeekEnd = Carbon::now()->endOfWeek();
            }
        }
    }

    /**
     * Navigate to previous month, week, day
     */
    public function prev()
    {
        if ($this->viewMode === 'month') {
            $this->currentMonth--;
            if ($this->currentMonth < 1) {
                $this->currentMonth = 12;
                $this->currentYear--;
            }
            $this->currentDate = Carbon::create($this->currentYear, $this->currentMonth, 1);
            $this->calculateMonthData();
        } elseif ($this->viewMode === 'week') {
            $this->currentWeekStart = $this->currentWeekStart->subWeek();
            $this->currentWeekEnd = $this->currentWeekEnd->subWeek();
            $this->currentDate = $this->currentWeekStart;
            $this->calculateWeekData();
        } elseif ($this->viewMode === 'day') {
            $this->currentDate = $this->currentDate->subDay();
        }
    }

    /**
     * Navigate to next month, week, day
     */
    public function next()
    {
        if ($this->viewMode === 'month') {
            $this->currentMonth++;
            if ($this->currentMonth > 12) {
                $this->currentMonth = 1;
                $this->currentYear++;
            }
            $this->currentDate = Carbon::create($this->currentYear, $this->currentMonth, 1);
            $this->calculateMonthData();
        } elseif ($this->viewMode === 'week') {
            $this->currentWeekStart = $this->currentWeekStart->addWeek();
            $this->currentWeekEnd = $this->currentWeekEnd->addWeek();
            $this->currentDate = $this->currentWeekStart;
            $this->calculateWeekData();
        } elseif ($this->viewMode === 'day') {
            $this->currentDate = $this->currentDate->addDay();
        }
    }

    public function mount()
    {
        $this->setInitialDate();

        match ($this->viewMode) {
            'month' => $this->calculateMonthData(),
            'week' => $this->calculateWeekData(),
            default => null
        };
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.calendar.calendar');
    }
}
