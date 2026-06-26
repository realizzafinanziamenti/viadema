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
use App\Livewire\Forms\PracticeOpportunityForm;
use Illuminate\Support\Facades\DB;
use App\Enums\ProductionType;
use App\Models\FinancialTable;
use App\Models\ProductType;
use App\Models\ProductSubtype;
use App\Models\Installment;
use App\Models\Insurance;
class LeadUpdate extends Component
{
    use InteractsWithDropdowns, EnumHelper;
    public PracticeOpportunityForm $opportunityForm;

    public Customer $lead;
    // customer form component
    public CustomerForm $form;
    public string $search = '';
    public array $teamMembers = [];
    public array $customerTypes = [];
    public array $leadSources = [];
    public array $leadStatuses = [];
    public array $productTypes = [];
    public array $productSubtypes = [];
    public array $installments = [];
    public array $insurances = [];
    public array $financialTables = [];
    public array $productionTypes = [];
    public function setOpportunityIsRenewal($value = null): void
    {
        $this->opportunityForm->isRenewal = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    public function setOpportunityProductionType(?string $value = null): void
    {
        $this->opportunityForm->productionType = $value;
    }

    public function setOpportunityCustomerType(?int $value = null): void
    {
        $this->opportunityForm->customerTypeId = $value;
    }

    public function setOpportunityFinancialTable(?int $value = null): void
    {
        $this->opportunityForm->financialTableId = $value;
    }

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
     * edit lead
     */
    public function save(): void
    {
        Gate::authorize('update', $this->lead);

        $lead = DB::transaction(function () {
            $lead = $this->form->update();

            $this->opportunityForm->updateOrCreate($lead);

            return $lead;
        });

        $this->redirectRoute('lead.show', ['id' => $lead->id], navigate: true);
    }
    public function setOpportunityProductType(?int $value = null): void
    {
        $this->opportunityForm->productTypeId = $value;
    }

    public function setOpportunityProductSubtype(?int $value = null): void
    {
        $this->opportunityForm->productSubtypeId = $value;
    }

    public function setOpportunityInstallment(?int $value = null): void
    {
        $this->opportunityForm->installmentId = $value;
    }

    public function setOpportunityInsurance(?int $value = null): void
    {
        $this->opportunityForm->insuranceId = $value;
    }
    /**
     * Initialize lists.
     */
    public function initializeLists(): void
    {
        $this->productTypes = ProductType::orderBy('name')->pluck('name', 'id')->toArray();
        $this->productSubtypes = ProductSubtype::orderBy('name')->pluck('name', 'id')->toArray();
        $this->installments = Installment::orderBy('value')->pluck('value', 'id')->toArray();
        $this->insurances = Insurance::orderBy('name')->pluck('name', 'id')->toArray();
        $this->financialTables = FinancialTable::orderBy('percentage')->pluck('percentage', 'id')->toArray();
        $this->productionTypes = $this->getEnumOptions(ProductionType::class);
        $this->customerTypes = CustomerType::orderBy('name')
        ->pluck('name', 'id')
        ->toArray();

        $this->leadSources = $this->getEnumOptions(LeadSource::class);
        $this->leadStatuses = $this->getEnumOptions(LeadStatus::class);
    }

    public function mount($id)
    {
        $this->lead = Customer::findOrFail($id);
        Gate::authorize('update', $this->lead);

        $this->form->setCustomer($this->lead);
        $this->opportunityForm->setOpportunity(
            $this->lead->practiceOpportunities()->latest()->first()
        );
        $this->initializeLists();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $this->teamMembers = User::assignableUsers()
            ->filterBySearch($this->search)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        return view('livewire.admin.lead.lead-update', [
            'teamMembers' => $this->teamMembers,
            'selectedUserId' => $this->form->userId,

            'productTypes' => $this->productTypes,
            'productSubtypes' => $this->productSubtypes,
            'installments' => $this->installments,
            'insurances' => $this->insurances,
            'financialTables' => $this->financialTables,
            'productionTypes' => $this->productionTypes,
        ]);
    }
}