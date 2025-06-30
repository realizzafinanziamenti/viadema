<?php

namespace App\Livewire\Admin\Lead;

use App\Enums\CustomerStatus;
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

class LeadCreate extends Component
{
    use InteractsWithDropdowns, EnumHelper;

    // customer form component
    public CustomerForm $form;
    public string $search = '';
    public array $teamMembers = [];
    public array $customerTypes = [];
    public array $leadSources = [];
    public array $leadStatuses = [];

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
     * Save customer
     */
    public function save(): void
    {
        Gate::authorize('create', [Customer::class, CustomerStatus::LEAD]);
        $lead = $this->form->store();

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
    }

    public function mount()
    {
        Gate::authorize('create', [Customer::class, CustomerStatus::LEAD]);

        // Initialize customer status to LEAD
        $this->form->customerStatus = CustomerStatus::LEAD->value;
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

        return view('livewire.admin.lead.lead-create', [
            'teamMembers' => $this->teamMembers,
            'selectedUserId' => $this->form->userId,
        ]);
    }
}
