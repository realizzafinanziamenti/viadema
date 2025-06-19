<?php

namespace App\Livewire\Admin\Lead;

use App\Enums\LeadCommunication;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Livewire\Forms\CustomerForm;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\User;
use App\Traits\EnumHelper;
use App\Traits\InteractsWithDropdowns;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

class LeadUpdate extends Component
{
    use InteractsWithDropdowns, EnumHelper;

    public Customer $lead;
    // customer form component
    public CustomerForm $form;
    public string $search = '';
    public array $teamMembers = [];
    public array $customerTypes = [];
    public array $leadSources = [];
    public array $leadStatuses = [];
    public array $leadCommunications = [];

    /**
     * Set title customer
     */
    public function setTeamMember(?int $value = null): void
    {
        $this->setFormSelectValue('userId', $value);
    }

    /**
     * Set customer type
     */
    public function setCustomerType(?int $value = null): void
    {
        $this->setFormSelectValue('customerTypeId', $value);
    }

    /**
     * Set lead source
     */
    public function setLeadSource(?string $value = null): void
    {
        $this->setFormSelectValue('leadSource', $value);
    }

    /**
     * Set lead status
     */
    public function setLeadStatus(?string $value = null): void
    {
        $this->setFormSelectValue('leadStatus', $value);
    }

    /**
     * Set lead communication
     */
    public function setLeadCommunication(?string $value = null): void
    {
        $this->setFormSelectValue('leadCommunication', $value);
    }

    /**
     * edit lead
     */
    public function save(): void
    {
        Gate::authorize('update', $this->lead);
        $lead = $this->form->update();

        $this->redirectRoute('lead.show', ['id' => $lead->id], navigate: true);
    }

    /**
     * Initialize lists.
     */
    public function initializeLists(): void
    {
        $this->customerTypes = CustomerType::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->leadSources = $this->getEnumOptions(LeadSource::class);
        $this->leadStatuses = $this->getEnumOptions(LeadStatus::class);
        $this->leadCommunications = $this->getEnumOptions(LeadCommunication::class);
    }

    public function mount($id)
    {
        $this->lead = Customer::findOrFail($id);
        Gate::authorize('update', $this->lead);

        $this->form->setCustomer($this->lead);
        $this->initializeLists();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $this->teamMembers = User::teamMembers()
            ->filterBySearch($this->search)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        return view('livewire.admin.lead.lead-update', [
            'teamMembers' => $this->teamMembers,
            'selectedUserId' => $this->form->userId,
        ]);
    }
}
