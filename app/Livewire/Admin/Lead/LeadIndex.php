<?php

namespace App\Livewire\Admin\Lead;

use App\Models\Customer;
use App\Traits\HandlesEntityActions;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class LeadIndex extends Component
{
    use WithPagination, WithoutUrlPagination, HandlesEntityActions;

    public ?Customer $selectedLead = null;
    public $search = '';

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected lead to be deleted.
     */
    public function selectLeadForDelete(int $id)
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Customer::class,
            property: 'selectedLead',
            modalName: 'delete-lead',
            notFoundMessage: 'Lead non trovato'
        );
    }

    /**
     * This method is called when the user clicks the delete button in the modal.
     * It deletes the selected lead and resets the selected lead to null.
     */
    public function deleteLead()
    {
        Gate::authorize('delete', $this->selectedLead);

        $this->deleteSelectedEntity(
            property: 'selectedLead',
            modalName: 'delete-lead',
            successMessage: 'Lead eliminato con successo',
        );
    }

    /**
     * Updated search bar callback function
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function mount()
    {
        Gate::authorize('viewAny', Customer::class);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = Customer::with('user', 'customerType')
            ->leads()
            ->orderByDesc('updated_at');

        $query = $query->filterBySearch($this->search);
        $leads = $query->paginate(15);

        return view('livewire.admin.lead.lead-index', [
            'leads' => $leads,
        ]);
    }
}
