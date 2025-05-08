<?php

namespace App\Livewire\Admin\Practice;

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
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PracticeCreate extends Component
{
    public CustomerForm $customerForm;
    public PracticeForm $practiceForm;
    public ?Customer $selectedCustomer = null;
    public array $productTypes = [];
    public array $productSubtypes = [];
    public array $financialTables = [];
    public array $insurances = [];
    public array $installments = [];
    public array $customerTypes = [];
    public array $practiceStatuses = [];
    public array $teamMembers = [];
    public array $customers = [];
    public int $step = 1;
    public string $teamMemberSearch = '';
    public string $customerSearch = '';

    /**
     * updated practice form userId callback function
     */
    public function updatedPracticeFormCustomerId($id): void
    {
        $this->resetValidation('practiceForm.customerId');
        $this->selectedCustomer = Customer::find($id);
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

        $this->step = 2;
    }

    /**
     * first previous step function
     */
    public function firstPrevStep(): void
    {
        $this->step = 1;
    }

    /**
     * second next step function
     */
    public function secondNextStep(): void
    {
        // $this->practiceForm->validate();
        $this->step = 3;
    }

    /**
     * second previous step function
     */
    public function secondPrevStep(): void
    {
        $this->step = 2;
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
        //
    }

    /**
     * Initialize the selects
     */
    protected function initSelectValues(): void
    {
        $this->productTypes = ProductType::all()
            ->pluck('name', 'id')
            ->toArray();

        $this->productSubtypes = ProductSubtype::all()
            ->pluck('name', 'id')
            ->toArray();

        $this->financialTables = FinancialTable::all()
            ->pluck('percentage', 'id')
            ->toArray();

        $this->insurances = Insurance::all()
            ->pluck('name', 'id')
            ->toArray();

        $this->installments = Installment::all()
            ->pluck('value', 'id')
            ->toArray();

        $this->customerTypes = CustomerType::all()
            ->pluck('name', 'id')
            ->toArray();

        $this->practiceStatuses = [
            PracticeStatus::UNDER_REVIEW->value => PracticeStatus::UNDER_REVIEW->getLabelText(),
            PracticeStatus::REJECTED->value => PracticeStatus::REJECTED->getLabelText(),
            PracticeStatus::APPROVED->value => PracticeStatus::APPROVED->getLabelText(),
            PracticeStatus::SUSPENDED->value => PracticeStatus::SUSPENDED->getLabelText(),
            PracticeStatus::PENDING->value => PracticeStatus::PENDING->getLabelText(),
            PracticeStatus::DISBURSED->value => PracticeStatus::DISBURSED->getLabelText(),
        ];

        $this->teamMembers = User::teamMembers()
            ->filterBySearch($this->teamMemberSearch)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        $this->customers = Customer::filterBySearch($this->customerSearch)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();
    }

    public function mount()
    {
        Gate::authorize('create', Practice::class);
        $this->initSelectValues();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.practice.practice-create', [
            'teamMembers' => $this->teamMembers,
            'customers' => $this->customers,
        ]);
    }
}
