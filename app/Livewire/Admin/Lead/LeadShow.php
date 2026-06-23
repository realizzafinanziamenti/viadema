<?php

namespace App\Livewire\Admin\Lead;

use App\Enums\LeadStatus;
use App\Models\Customer;
use App\Models\Practice;
use App\Traits\EnumHelper;
use App\Traits\HandlesEntityActions;
use App\Traits\InteractsWithDropdowns;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Str;

class LeadShow extends Component
{
    use HandlesEntityActions, InteractsWithDropdowns, EnumHelper;

    public Customer $lead;
    public array $leadStatuses = [];
    public ?string $selectedLeadStatus = null;

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
    public function openUpdateLeadStatusModal()
    {
        $this->dispatch('open-modal', 'update-lead-status');
    }

    /**
     * This method is called when the user clicks the update button in the modal.
     * It updates the lead status.
     */
    public function updateLeadStatus(): void
    {
        Gate::authorize('update', $this->lead);

        try {
            $this->lead->update(['lead_status' => $this->selectedLeadStatus]);
            Toaster::success('Stato profilo aggiornato con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante l\'aggiornamento dello stato del profilo: ' . $e->getMessage());
        }

        $this->dispatch('close-modal', 'update-lead-status');
    }

    /**
     * Create practice from lead
     */
    public function createPracticeFromLead(): void
    {
        Gate::authorize('create', Practice::class);

        $opportunity = $this->lead->practiceOpportunities()
        ->latest()
        ->first();
        if (! $opportunity) {
            Toaster::error('Non è presente nessuna opportunità pratica associata a questo lead.');
            return;
        }
        // create a unique token
        $token = Str::random(32);

        // save in cache for 10 minutes
        Cache::put("practice_creation_{$token}", [
            'customer_id' => $this->lead->id,
            'practice_opportunity_id' => $opportunity->id,
            'convert_lead' => true,
            'user_id' => auth()->id(),
            'expires_at' => now()->addMinutes(10),
        ], 600);

        $this->redirectRoute('practice.create', ['token' => $token], navigate: true);
    }

    /**
     * This method is called when the component is mounted.
     * It initializes the lead statuses select.
     */
    protected function initializeLeadStatuses(): void
    {
        $this->leadStatuses = $this->getEnumOptions(LeadStatus::class);
    }

    public function mount($id)
    {
        $this->lead = Customer::with([
            'customerType',
            'practiceOpportunities.productType',
            'practiceOpportunities.productSubtype',
            'practiceOpportunities.financialTable',
            'practiceOpportunities.insurance',
            'practiceOpportunities.installment',
            'practiceOpportunities.customerType',
        ])->findOrFail($id);
        Gate::authorize('view', $this->lead);

        $this->initializeLeadStatuses();
        $this->setLeadStatus($this->lead->lead_status?->value);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.lead.lead-show');
    }
}