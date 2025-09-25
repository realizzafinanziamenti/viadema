<?php

namespace App\Livewire\Admin\Lead;

use App\Enums\CustomerStatus;
use App\Enums\LeadStatus;
use App\Imports\LeadsImport;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\ImportExcelCompleted;
use App\Traits\EnumHelper;
use App\Traits\HandlesEntityActions;
use App\Traits\InteractsWithDropdowns;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Masmerise\Toaster\Toaster;

class LeadIndex extends Component
{
    use WithPagination, WithoutUrlPagination, HandlesEntityActions, InteractsWithDropdowns, EnumHelper, WithFileUploads;

    public ?Customer $selectedLead = null;
    public array $leadStatuses = [];
    public ?string $selectedLeadStatus = null;
    public $search = '';
    public $importFile = null;

    /**
     * Handle the file upload and import.
     */
    public function updatedImportFile()
    {
        if ($this->importFile) {
            $this->importLeads();
        }
    }

    /**
     * Import the leads from the uploaded file.
     */
    public function importLeads()
    {
        Gate::authorize('importLead', Customer::class);

        try {
            $this->validate([
                'importFile' => ['required', 'file', 'mimes:xlsx,xls']
            ]);
        } catch (Exception $e) {
            Toaster::error('Errore durante la validazione del file. Assicurati che sia un file Excel valido (.xlsx, .xls).');
            $this->reset('importFile');
            return;
        }

        $import = new LeadsImport;
        $users = User::role('superadmin')->get();

        Excel::queueImport($import, $this->importFile)
            ->chain([
                function () use ($import, $users) {
                    // Invio notifica
                    Notification::send($users, new ImportExcelCompleted('leads'));
                }
            ]);

        Toaster::success('Import avviato! Riceverai una notifica al termine.');
        $this->reset('importFile');
    }

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
     * This method is called when the user clicks the notes button.
     * It sets the selected lead to shows notes.
     */
    public function selectLeadForNotes(int $id): void
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Customer::class,
            property: 'selectedLead',
            modalName: 'lead-notes',
            notFoundMessage: 'Note non trovate'
        );
    }

    /**
     * This method is called when the user clicks the update button in the modal.
     * It updates the lead status and resets the selected lead to null.
     */
    public function updateLeadStatus(): void
    {
        Gate::authorize('updateLeadStatus', $this->selectedLead);

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
