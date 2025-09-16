<?php

namespace App\Livewire\Admin\Event;

use App\Livewire\Forms\EventForm;
use App\Models\Event;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class EventIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public ?Event $selectedEvent = null;
    public EventForm $form;
    public $search = '';
    public Collection $possibleParticipants;

    /**
     * Updated search bar callback function
     */
    public function updatedSearch()
    {
        $this->resetPage();
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
    public function openEditEventModal(int $id): void
    {
        $this->selectedEvent = Event::findOrFail($id);
        Gate::authorize('update', $this->selectedEvent);
        $this->resetErrorBag();
        $this->form->setEvent($this->selectedEvent);
        $this->dispatch('open-modal', 'event-edit');
    }

    /**
     * save event
     */
    public function save()
    {
        Gate::authorize('create', Event::class);
        $this->form->store();
        // $this->loadEvents();
        $this->dispatch('close-modal', 'event-create');
    }

    /**
     * edit event
     */
    public function edit()
    {
        Gate::authorize('update', $this->selectedEvent);
        $this->selectedEvent = $this->form->update();
        // $this->loadEvents();
        $this->dispatch('close-modal', 'event-edit');
    }

    /**
     * Delete event function
     */
    public function delete(int $id): void
    {
        try {
            $event = Event::findOrFail($id);
            Gate::authorize('delete', $event);
            $event->delete();

            Toaster::success('Evento eliminato con successo');
        } catch (Exception $e) {
            Toaster::error('Si è verificato un errore: ' . $e->getMessage());
        }

        $this->resetPage();
        $this->dispatch('close-modal', 'event-detail');
        $this->dispatch('close-modal', 'event-delete');
    }

    /**
     * load possible participants for events
     */
    public function loadPossibleParticipants()
    {
        $this->possibleParticipants = User::assignableUsers()
            ->excludeAuthenticatedUser()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function mount()
    {
        Gate::authorize('viewAny', Event::class);
        $this->loadPossibleParticipants();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $pastEventsQuery = Event::with('user')
            ->visibleByUser(auth()->user())
            ->filterBySearch($this->search)
            ->pastEvents();

        $upcomingEventsQuery = Event::with('user')
            ->visibleByUser(auth()->user())
            ->filterBySearch($this->search)
            ->upcomingEvents();

        $pastEvents = $pastEventsQuery->paginate(15);
        $upcomingEvents = $upcomingEventsQuery->paginate(15);

        return view('livewire.admin.event.event-index', [
            'pastEvents' => $pastEvents,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
