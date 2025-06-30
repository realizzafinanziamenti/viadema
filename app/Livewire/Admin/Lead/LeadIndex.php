<?php

namespace App\Livewire\Admin\Lead;

use App\Enums\CustomerStatus;
use App\Enums\LeadStatus;
use App\Models\Customer;
use App\Traits\EnumHelper;
use App\Traits\HandlesEntityActions;
use App\Traits\InteractsWithDropdowns;
use Exception;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class LeadIndex extends Component
{
    use WithPagination, WithoutUrlPagination, HandlesEntityActions, InteractsWithDropdowns, EnumHelper;

    public ?Customer $selectedLead = null;
    public array $leadStatuses = [];
    public ?string $selectedLeadStatus = null;
    public $search = '';

    /**
     * Set lead status for the selected lead.
     */
    public function setLeadStatus(?string $value = null): void
    {
        $this->setSelectValue('selectedLeadStatus', $value);
    }

    /**
     * This method is called when the user clicks the update status button.
     * It sets the selected lead and opens the modal for updating the status.
     */
    public function selectLeadForStatus(int $id)
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Customer::class,
            property: 'selectedLead',
            modalName: 'update-lead-status',
            notFoundMessage: 'Profilo non trovata'
        );
        $this->setLeadStatus($this->selectedLead->lead_status?->value);
    }

    /**
     * This method is called when the user clicks the update button in the modal.
     * It updates the lead status and resets the selected lead to null.
     */
    public function updateLeadStatus(): void
    {
        Gate::authorize('update', $this->selectedLead);

        try {
            $this->selectedLead->update(['lead_status' => $this->selectedLeadStatus]);
            Toaster::success('Stato profilo aggiornato con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante l\'aggiornamento dello stato del profilo: ' . $e->getMessage());
        }

        $this->selectedLead = null;
        $this->dispatch('close-modal', 'update-lead-status');
    }

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
            notFoundMessage: 'Profilo non trovato'
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
        Gate::authorize('viewAny', [Customer::class, CustomerStatus::LEAD]);

        $this->leadStatuses = $this->getEnumOptions(LeadStatus::class);
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
