<?php

namespace App\Livewire\Admin\Practice;

use App\Enums\CustomerStatus;
use App\Enums\PracticeStatus;
use App\Livewire\Forms\CustomerForm;
use App\Livewire\Forms\PracticeForm;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\FinancialTable;
use App\Models\Installment;
use App\Models\Insurance;
use App\Models\Practice;
use App\Models\ProductSubtype;
use App\Models\ProductType;
use App\Models\User;
use App\Traits\AcceptedFileTypes;
use App\Traits\HandlesPracticeInstallments;
use App\Traits\InteractsWithDropdowns;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class PracticeCreate extends Component
{
    use InteractsWithDropdowns, HandlesPracticeInstallments, AcceptedFileTypes, WithFileUploads;

    public CustomerForm $customerForm;
    public PracticeForm $practiceForm;
    public ?Customer $selectedCustomer = null;
    public array $temporaryFiles = [];
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
     * second next step function
     */
    public function secondNextStep(): void
    {
        // set practice status to UNDER_REVIEW if not set
        if (! $this->practiceForm->practiceStatus) {
            $this->practiceForm->practiceStatus = PracticeStatus::UNDER_REVIEW->value;
        }

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
        Gate::authorize('create', [Customer::class, CustomerStatus::CUSTOMER]);
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
        Gate::authorize('create', Practice::class);
        $practice = $this->practiceForm->store();

        $this->redirectRoute('practice.show', ['id' => $practice->id], navigate: true);
    }

    /**
     * This method is called when the user uploads new files.
     * It updates the practice form attachments with the temporary files.
     */
    public function updatedTemporaryFiles(): void
    {
        $this->validate([
            'temporaryFiles' => ['nullable', 'array', 'max:10'],
            'temporaryFiles.*' => ['nullable', 'file', 'mimetypes:' . implode(',', $this->acceptedFileTypesArray()), 'max:10240']
        ], [
            'temporaryFiles.max' => 'Puoi caricare al massimo 10 file.',
            'temporaryFiles.*.max' => 'Ogni file non può superare i 10MB.',
            'temporaryFiles.*.mimetypes' => 'Formato file non valido.',
        ]);

        foreach ($this->temporaryFiles as $file) {
            $this->practiceForm->attachments[] = $file;
        }
    }

    /**
     * This method is called when the user deletes a temporary file.
     * It removes the file from the temporary files array and practice form attachments.
     *
     * @param int|string $index The index of the file to delete
     */
    public function deleteTemporaryFile(int $index): void
    {
        // Remove from practice form attachments
        if (isset($this->practiceForm->attachments[$index])) {
            unset($this->practiceForm->attachments[$index]);
            $this->practiceForm->attachments = array_values($this->practiceForm->attachments);
        }
    }

    /**
     * Initialize the selects
     */
    protected function initSelectValues(): void
    {
        $this->productTypes = ProductType::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->productSubtypes = ProductSubtype::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->installments = Installment::orderBy('value')
            ->pluck('value', 'id')
            ->toArray();

        $this->financialTables = FinancialTable::orderBy('percentage')
            ->pluck('percentage', 'id')
            ->toArray();

        $this->insurances = Insurance::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->customerTypes = CustomerType::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function mount()
    {
        Gate::authorize('create', Practice::class);
        $this->initSelectValues();

        // Initialize customer status to CUSTOMER
        $this->customerForm->customerStatus = CustomerStatus::CUSTOMER->value;
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $teamMembers = User::assignableUsers()
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

        return view('livewire.admin.practice.practice-create', [
            'teamMembers' => $teamMembers,
            'customers' => $customers,
        ]);
    }
}
