<?php

namespace App\Livewire\Admin\Practice;

use App\Livewire\Forms\CustomerForm;
use App\Livewire\Forms\PracticeForm;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\FinancialTable;
use App\Models\Installment;
use App\Models\InstallmentProductDefault;
use App\Models\Insurance;
use App\Models\Practice;
use App\Models\ProductSubtype;
use App\Models\ProductType;
use App\Models\User;
use App\Traits\HandlesPracticeInstallments;
use App\Traits\InteractsWithDropdowns;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PracticeUpdate extends Component
{
    use InteractsWithDropdowns, HandlesPracticeInstallments;

    public Practice $practice;
    public PracticeForm $practiceForm;
    public CustomerForm $customerForm;
    public ?Customer $selectedCustomer = null;
    public array $productTypes = [];
    public array $productSubtypes = [];
    public array $financialTables = [];
    public array $insurances = [];
    public array $installments = [];
    public array $customerTypes = [];
    public array $practiceStatuses = [];
    public int $step = 1;
    public string $teamMemberSearch = '';
    public string $customerSearch = '';
    public bool $selectsInitialized = false;

    /**
     * Set team member for customer form
     */
    public function setTeamMember(?int $value = null): void
    {
        $this->setFormSelectValue('userId', $value, 'customerForm');
    }

    /**
     * Set team member for practice form
     */
    public function setPracticeTeamMember(?int $value = null): void
    {
        $this->setFormSelectValue('userId', $value, 'practiceForm');
    }

    /**
     * Set customer
     */
    public function setCustomer(?int $value = null): void
    {
        $this->setFormSelectValue('customerId', $value, 'practiceForm');

        $this->resetValidation('practiceForm.customerId');
        $this->selectedCustomer = Customer::find($this->practiceForm->customerId);
    }

    /**
     * Set product type
     */
    public function setProductType(?int $value = null): void
    {
        $this->setFormSelectValue('productTypeId', $value, 'practiceForm');
        $this->setRenewabilityAndAlertPercentage($this->practiceForm);
        $this->recalculateRenewabilityDate($this->practiceForm);
    }

    /**
     * Set product subtype
     */
    public function setProductSubtype(?int $value = null): void
    {
        $this->setFormSelectValue('productSubtypeId', $value, 'practiceForm');
    }

    /**
     * Set financial table
     */
    public function setFinancialTable(?int $value = null): void
    {
        $this->setFormSelectValue('financialTableId', $value, 'practiceForm');
    }

    /**
     * Set insurance
     */
    public function setInsurance(?int $value = null): void
    {
        $this->setFormSelectValue('insuranceId', $value, 'practiceForm');
    }

    /**
     * Set installment
     */
    public function setInstallment(?int $value = null): void
    {
        $this->setFormSelectValue('installmentId', $value, 'practiceForm');
        $this->recalculateLastInstallmentDate($this->practiceForm, $this->installments);
        $this->setRenewabilityAndAlertPercentage($this->practiceForm);
        $this->recalculateRenewabilityDate($this->practiceForm);
    }

    /**
     * Update first installment date callback function and recalculate last installment and renewability date
     */
    public function updatedPracticeFormFirstInstallmentDate(): void
    {
        if ($this->practiceForm->firstInstallmentDate) {
            $this->recalculateLastInstallmentDate($this->practiceForm, $this->installments);
            $this->recalculateRenewabilityDate($this->practiceForm);
        } else {
            $this->practiceForm->lastInstallmentDate = null;
            $this->practiceForm->renewabilityDate = null;
        }
    }

    /**
     * Update practice form renewability percentage and recalculate renewability date
     */
    public function updatedPracticeFormRenewabilityPercentage(): void
    {
        $this->recalculateRenewabilityDate($this->practiceForm);
    }

    /**
     * Set customer type
     */
    public function setCustomerType(?int $value = null): void
    {
        $this->setFormSelectValue('customerTypeId', $value, 'practiceForm');
    }

    /**
     * open create customer modal
     */
    public function openCreateCustomerModal(): void
    {
        $this->teamMemberSearch = '';
        $this->dispatch('open-modal', 'customer-create');
    }

    /**
     * first next step function
     */
    public function firstNextStep(): void
    {
        if (! $this->practiceForm->customerId) {
            $this->addError('practiceForm.customerId', 'Seleziona prima un cliente.');
            return;
        }

        $this->teamMemberSearch = '';
        $this->step = 2;
        $this->dispatch('step-changed');
    }

    /**
     * first previous step function
     */
    public function firstPrevStep(): void
    {
        $this->step = 1;
        $this->dispatch('step-changed');
    }

    /**
     * Initialize the selects
     */
    protected function initializeSelects(): void
    {
        $this->productTypes = ProductType::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->productSubtypes = ProductSubtype::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->financialTables = FinancialTable::orderBy('percentage')
            ->pluck('percentage', 'id')
            ->toArray();

        $this->insurances = Insurance::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->installments = Installment::orderBy('value')
            ->pluck('value', 'id')
            ->toArray();

        $this->customerTypes = CustomerType::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * second next step function
     */
    public function secondNextStep(): void
    {
        $this->practiceForm->validate();
        $this->step = 3;
        $this->dispatch('step-changed');
    }

    /**
     * second previous step function
     */
    public function secondPrevStep(): void
    {
        $this->step = 2;
        $this->dispatch('step-changed');
    }

    /**
     * Save customer
     */
    public function saveCustomer(): void
    {
        Gate::authorize('create', Customer::class);
        $customer = $this->customerForm->store();

        $this->selectedCustomer = $customer;
        $this->practiceForm->customerId = $customer->id;
        $this->dispatch('close-modal', 'customer-create');
    }

    /**
     * Save practice
     */
    public function savePractice(): void
    {
        Gate::authorize('update', $this->practice);
        $practice = $this->practiceForm->update();

        $this->redirectRoute('practice.show', ['id' => $practice->id], navigate: true);
    }

    public function mount($id)
    {
        $this->practice = Practice::find($id);
        $this->selectedCustomer = $this->practice->customer;
        Gate::authorize('update', $this->practice);

        $this->practiceForm->setPractice($this->practice);
        $this->initializeSelects();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $teamMembers = User::teamMembers()
            ->filterBySearch($this->teamMemberSearch)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        $customers = Customer::filterBySearch($this->customerSearch)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        return view('livewire.admin.practice.practice-update', [
            'teamMembers' => $teamMembers,
            'customers' => $customers,
            'selectedUserId' => $this->customerForm->userId,
        ]);
    }
}
