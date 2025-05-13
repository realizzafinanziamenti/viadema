<?php

namespace App\Livewire\Admin\Calendar;

use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Calendar extends Component
{
    public ?Carbon $currentDate;
    public string $currentDay = '';
    public string $currentMonth = '';
    public string $currentYear = '';

    /**
     * Set the initial date to the current date.
     */
    public function setInitialDate()
    {
        $this->currentDate = Carbon::now();
        $this->currentDay = $this->currentDate->format('d');
        $this->currentMonth = $this->currentDate->format('F');
        $this->currentYear = $this->currentDate->format('Y');
    }

    public function mount()
    {
        $this->setInitialDate();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.calendar.calendar');
    }
}
