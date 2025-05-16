<?php

namespace App\Livewire\Admin\Event;

use App\Models\Event;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class EventIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $search = '';

    /**
     * Updated search bar callback function
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        Gate::authorize('viewAny', Event::class);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $pastEventsQuery = Event::where('start_date', '<', now())
            ->visibleByUser(auth()->user())
            ->filterBySearch($this->search)
            ->orderBy('start_date', 'desc');

        $upcomingEventsQuery = Event::where('start_date', '>=', now())
            ->visibleByUser(auth()->user())
            ->filterBySearch($this->search)
            ->orderBy('start_date', 'asc');

        $pastEvents = $pastEventsQuery->paginate(15);
        $upcomingEvents = $upcomingEventsQuery->paginate(15);

        return view('livewire.admin.event.event-index', [
            'pastEvents' => $pastEvents,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
