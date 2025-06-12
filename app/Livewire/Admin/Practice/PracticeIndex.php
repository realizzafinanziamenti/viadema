<?php

namespace App\Livewire\Admin\Practice;

use App\Enums\PracticeStatus;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\FinancialTable;
use App\Models\Installment;
use App\Models\Insurance;
use App\Models\Practice;
use App\Models\ProductSubtype;
use App\Models\ProductType;
use App\Models\User;
use App\Traits\EnumHelper;
use App\Traits\HandlesEntityActions;
use App\Traits\InteractsWithDropdowns;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class PracticeIndex extends Component
{
    use WithPagination, WithoutUrlPagination, HandlesEntityActions, EnumHelper, InteractsWithDropdowns;

    public ?ProductType $type = null;
    public ?bool $expired = false;
    public Practice|null $selectedPractice = null;
    public ?int $selectedPracticeStatus = null;
    public string $search = '';
    // Team Member Filter
    public string $teamMemberSearch = '';
    public ?int $selectedTeamMemberForFilter = null;
    public ?int $tempSelectedTeamMemberForFilter = null;
    // Customer Filter
    public string $customerSearch = '';
    public ?int $selectedCustomerForFilter = null;
    public ?int $tempSelectedCustomerForFilter = null;
    // Product Filters
    public array $productTypes = [];
    public ?int $selectedProductTypeForFilter = null;
    public ?int $tempSelectedProductTypeForFilter = null;
    // Product Subtype Filter
    public array $productSubtypes = [];
    public ?int $selectedProductSubtypeForFilter = null;
    public ?int $tempSelectedProductSubtypeForFilter = null;
    // Financial Filter
    public array $financialTables = [];
    public ?int $selectedFinancialTableForFilter = null;
    public ?int $tempSelectedFinancialTableForFilter = null;
    // Insurance Filter
    public array $insurances = [];
    public ?int $selectedInsuranceForFilter = null;
    public ?int $tempSelectedInsuranceForFilter = null;
    // Installment Filter
    public array $installments = [];
    public ?int $selectedInstallmentForFilter = null;
    public ?int $tempSelectedInstallmentForFilter = null;
    // Customer Type Filter
    public array $customerTypes = [];
    public ?int $selectedCustomerTypeForFilter = null;
    public ?int $tempSelectedCustomerTypeForFilter = null;
    // Practice Status Filter
    public array $practiceStatuses = [];
    public ?int $selectedPracticeStatusForFilter = null;
    public ?int $tempSelectedPracticeStatusForFilter = null;
    // Date filters
    public ?string $firstInstallmentDateMin = null;
    public ?string $tempFirstInstallmentDateMin = null;
    public ?string $firstInstallmentDateMax = null;
    public ?string $tempFirstInstallmentDateMax = null;
    public ?string $lastInstallmentDateMin = null;
    public ?string $tempLastInstallmentDateMin = null;
    public ?string $lastInstallmentDateMax = null;
    public ?string $tempLastInstallmentDateMax = null;
    public ?string $renewabilityDateMin = null;
    public ?string $tempRenewabilityDateMin = null;
    public ?string $renewabilityDateMax = null;
    public ?string $tempRenewabilityDateMax = null;
    // amounts filters
    public ?float $amountDisbursedMin = null;
    public ?float $tempAmountDisbursedMin = null;
    public ?float $amountDisbursedMax = null;
    public ?float $tempAmountDisbursedMax = null;
    public ?float $totalAmountMin = null;
    public ?float $tempTotalAmountMin = null;
    public ?float $totalAmountMax = null;
    public ?float $tempTotalAmountMax = null;
    public ?float $rateAmountMin = null;
    public ?float $tempRateAmountMin = null;
    public ?float $rateAmountMax = null;
    public ?float $tempRateAmountMax = null;
    // financial rates filters
    public ?float $tanMin = null;
    public ?float $tempTanMin = null;
    public ?float $tanMax = null;
    public ?float $tempTanMax = null;
    public ?float $taegMin = null;
    public ?float $tempTaegMin = null;
    public ?float $taegMax = null;
    public ?float $tempTaegMax = null;
    // Order by select
    public array $orderBySelect = [];

    /**
     * Set team member for customer form
     */
    public function setTeamMember(?int $value = null): void
    {
        $this->setSelectValue('tempSelectedTeamMemberForFilter', $value);
    }

    /**
     * Set customer
     */
    public function setCustomer(?int $value = null): void
    {
        $this->setSelectValue('tempSelectedCustomerForFilter', $value);
    }

    /**
     * Set product type
     */
    public function setProductType(?int $value = null): void
    {
        $this->setSelectValue('tempSelectedProductTypeForFilter', $value);
    }

    /**
     * Set product subtype
     */
    public function setProductSubtype(?int $value = null): void
    {
        $this->setSelectValue('tempSelectedProductSubtypeForFilter', $value);
    }

    /**
     * Set financial table
     */
    public function setFinancialTable(?int $value = null): void
    {
        $this->setSelectValue('tempSelectedFinancialTableForFilter', $value);
    }

    /**
     * Set insurance
     */
    public function setInsurance(?int $value = null): void
    {
        $this->setSelectValue('tempSelectedInsuranceForFilter', $value);
    }

    /**
     * Set installment
     */
    public function setInstallment(?int $value = null): void
    {
        $this->setSelectValue('tempSelectedInstallmentForFilter', $value);
    }

    /**
     * Set customer type
     */
    public function setCustomerType(?int $value = null): void
    {
        $this->setSelectValue('tempSelectedCustomerTypeForFilter', $value);
    }

    /**
     * This method is called when the user selects a practice status from the dropdown.
     * It sets the selected practice status.
     */
    public function setPracticeStatus(?int $value = null): void
    {
        $this->setSelectValue('selectedPracticeStatus', $value);
    }

    /**
     * This method is called when the user selects a practice status for filtering.
     * It sets the selected practice status for filter.
     */
    public function setPracticeStatusForFilter(?int $value = null): void
    {
        $this->setSelectValue('tempSelectedPracticeStatusForFilter', $value);
    }

    /**
     * This method is called when the user clicks the update status button.
     * It sets the selected practice and opens the modal for updating the status.
     */
    public function selectPracticeForStatus(int $id)
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Practice::class,
            property: 'selectedPractice',
            modalName: 'update-practice-status',
            notFoundMessage: 'Pratica non trovata'
        );
        $this->setPracticeStatus($this->selectedPractice->practice_status?->value);
    }

    /**
     * This method is called when the user clicks the update button in the modal.
     * It updates the practice status and resets the selected practice to null.
     */
    public function updatePracticeStatus(): void
    {
        Gate::authorize('update', $this->selectedPractice);

        try {
            $this->selectedPractice->update(['practice_status' => $this->selectedPracticeStatus]);
            Toaster::success('Stato della pratica aggiornato con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante l\'aggiornamento dello stato della pratica');
        }

        $this->selectedPractice = null;
        $this->dispatch('close-modal', 'update-practice-status');
    }

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected practice to be deleted.
     */
    public function selectPracticeForDelete(int $id): void
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Practice::class,
            property: 'selectedPractice',
            modalName: 'delete-practice',
            notFoundMessage: 'Pratica non trovata'
        );
    }

    /**
     * This method is called when the user clicks the delete button in the modal.
     * It deletes the selected practice and resets the selected practice to null.
     */
    public function deletePractice(): void
    {
        Gate::authorize('delete', $this->selectedPractice);

        $this->deleteSelectedEntity(
            property: 'selectedPractice',
            modalName: 'delete-practice',
            successMessage: 'Pratica eliminata con successo'
        );
    }

    /**
     * Updated search bar callback function
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * This method is called when the component is mounted.
     * It initializes the selects.
     */
    protected function initializeSelects(): void
    {
        $this->practiceStatuses = $this->getEnumOptions(PracticeStatus::class);

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

    /**
     * This method is called when the user clicks the filter button.
     * It resets the page and closes the filter modal.
     */
    public function filter(): void
    {
        $this->selectedTeamMemberForFilter = $this->tempSelectedTeamMemberForFilter;
        $this->selectedCustomerForFilter = $this->tempSelectedCustomerForFilter;
        $this->selectedProductTypeForFilter = $this->tempSelectedProductTypeForFilter;
        $this->selectedProductSubtypeForFilter = $this->tempSelectedProductSubtypeForFilter;
        $this->selectedFinancialTableForFilter = $this->tempSelectedFinancialTableForFilter;
        $this->selectedInsuranceForFilter = $this->tempSelectedInsuranceForFilter;
        $this->selectedInstallmentForFilter = $this->tempSelectedInstallmentForFilter;
        $this->selectedCustomerTypeForFilter = $this->tempSelectedCustomerTypeForFilter;
        $this->selectedPracticeStatusForFilter = $this->tempSelectedPracticeStatusForFilter;
        $this->firstInstallmentDateMin = $this->tempFirstInstallmentDateMin;
        $this->firstInstallmentDateMax = $this->tempFirstInstallmentDateMax;
        $this->lastInstallmentDateMin = $this->tempLastInstallmentDateMin;
        $this->lastInstallmentDateMax = $this->tempLastInstallmentDateMax;
        $this->renewabilityDateMin = $this->tempRenewabilityDateMin;
        $this->renewabilityDateMax = $this->tempRenewabilityDateMax;
        $this->amountDisbursedMin = $this->tempAmountDisbursedMin;
        $this->amountDisbursedMax = $this->tempAmountDisbursedMax;
        $this->totalAmountMin = $this->tempTotalAmountMin;
        $this->totalAmountMax = $this->tempTotalAmountMax;
        $this->rateAmountMin = $this->tempRateAmountMin;
        $this->rateAmountMax = $this->tempRateAmountMax;
        $this->tanMin = $this->tempTanMin;
        $this->tanMax = $this->tempTanMax;
        $this->taegMin = $this->tempTaegMin;
        $this->taegMax = $this->tempTaegMax;

        $this->resetPage();
        $this->dispatch('close-modal', 'filter-modal');
    }

    /**
     * This method is called when the user clicks the reset button.
     * It resets the filters and closes the filter modal.
     */
    public function resetFilter(): void
    {
        $this->reset([
            'selectedTeamMemberForFilter',
            'tempSelectedTeamMemberForFilter',
            'selectedCustomerForFilter',
            'tempSelectedCustomerForFilter',
            'selectedProductTypeForFilter',
            'tempSelectedProductTypeForFilter',
            'selectedProductSubtypeForFilter',
            'tempSelectedProductSubtypeForFilter',
            'selectedFinancialTableForFilter',
            'tempSelectedFinancialTableForFilter',
            'selectedInsuranceForFilter',
            'tempSelectedInsuranceForFilter',
            'selectedInstallmentForFilter',
            'tempSelectedInstallmentForFilter',
            'selectedCustomerTypeForFilter',
            'tempSelectedCustomerTypeForFilter',
            'selectedPracticeStatusForFilter',
            'tempSelectedPracticeStatusForFilter',
            'firstInstallmentDateMin',
            'tempFirstInstallmentDateMin',
            'firstInstallmentDateMax',
            'tempFirstInstallmentDateMax',
            'lastInstallmentDateMin',
            'tempLastInstallmentDateMin',
            'lastInstallmentDateMax',
            'tempLastInstallmentDateMax',
            'renewabilityDateMin',
            'tempRenewabilityDateMin',
            'renewabilityDateMax',
            'tempRenewabilityDateMax',
            'amountDisbursedMin',
            'tempAmountDisbursedMin',
            'amountDisbursedMax',
            'tempAmountDisbursedMax',
            'totalAmountMin',
            'tempTotalAmountMin',
            'totalAmountMax',
            'tempTotalAmountMax',
            'rateAmountMin',
            'tempRateAmountMin',
            'rateAmountMax',
            'tempRateAmountMax',
            'tanMin',
            'tempTanMin',
            'tanMax',
            'tempTanMax',
            'taegMin',
            'tempTaegMin',
            'taegMax',
            'tempTaegMax',
        ]);

        $this->resetPage();
        $this->dispatch('close-modal', 'filter-modal');
    }

    public function applyFilters($query)
    {
        if ($this->selectedPracticeStatusForFilter) {
            $query->where('practice_status', $this->selectedPracticeStatusForFilter);
        }
        if ($this->selectedTeamMemberForFilter) {
            $query->where('user_id', $this->selectedTeamMemberForFilter);
        }
        if ($this->selectedCustomerForFilter) {
            $query->where('customer_id', $this->selectedCustomerForFilter);
        }
        if ($this->selectedProductTypeForFilter && $this->type === null) {
            $query->where('product_type_id', $this->selectedProductTypeForFilter);
        }
        if ($this->selectedProductSubtypeForFilter) {
            $query->where('product_subtype_id', $this->selectedProductSubtypeForFilter);
        }
        if ($this->selectedFinancialTableForFilter) {
            $query->where('financial_table_id', $this->selectedFinancialTableForFilter);
        }
        if ($this->selectedInsuranceForFilter) {
            $query->where('insurance_id', $this->selectedInsuranceForFilter);
        }
        if ($this->selectedInstallmentForFilter) {
            $query->where('installment_id', $this->selectedInstallmentForFilter);
        }
        if ($this->selectedCustomerTypeForFilter) {
            $query->where('customer_type_id', $this->selectedCustomerTypeForFilter);
        }
        if ($this->firstInstallmentDateMin) {
            $query->whereDate('first_installment_date', '>=', $this->firstInstallmentDateMin);
        }
        if ($this->firstInstallmentDateMax) {
            $query->whereDate('first_installment_date', '<=', $this->firstInstallmentDateMax);
        }
        if ($this->lastInstallmentDateMin) {
            $query->whereDate('last_installment_date', '>=', $this->lastInstallmentDateMin);
        }
        if ($this->lastInstallmentDateMax) {
            $query->whereDate('last_installment_date', '<=', $this->lastInstallmentDateMax);
        }
        if ($this->renewabilityDateMin) {
            $query->whereDate('renewability_date', '>=', $this->renewabilityDateMin);
        }
        if ($this->renewabilityDateMax) {
            $query->whereDate('renewability_date', '<=', $this->renewabilityDateMax);
        }
        if ($this->amountDisbursedMin) {
            $query->where('amount_disbursed', '>=', $this->amountDisbursedMin);
        }
        if ($this->amountDisbursedMax) {
            $query->where('amount_disbursed', '<=', $this->amountDisbursedMax);
        }
        if ($this->totalAmountMin) {
            $query->where('total_amount', '>=', $this->totalAmountMin);
        }
        if ($this->totalAmountMax) {
            $query->where('total_amount', '<=', $this->totalAmountMax);
        }
        if ($this->rateAmountMin) {
            $query->where('rate_amount', '>=', $this->rateAmountMin);
        }
        if ($this->rateAmountMax) {
            $query->where('rate_amount', '<=', $this->rateAmountMax);
        }
        if ($this->tanMin) {
            $query->where('tan', '>=', $this->tanMin);
        }
        if ($this->tanMax) {
            $query->where('tan', '<=', $this->tanMax);
        }
        if ($this->taegMin) {
            $query->where('taeg', '>=', $this->taegMin);
        }
        if ($this->taegMax) {
            $query->where('taeg', '<=', $this->taegMax);
        }

        return $query;
    }

    public function mount(Request $request): void
    {
        Gate::authorize('viewAny', Practice::class);

        // Get the slug from the route parameters
        // This allows the component to be used with or without a specific ProductType slug
        $slug = $request->route('slug');

        // If a slug is provided, fetch the corresponding ProductType, otherwise set it to null
        $this->type = $slug
            ? ProductType::where('slug', $slug)->firstOrFail()
            : null;

        // the expired status based on the request parameter
        // This allows the component to be used with or without the expired filter
        $this->expired = $request->boolean('expired');
        $this->initializeSelects();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = Practice::with('customer', 'user', 'productType')
            ->filterByProductType($this->type)
            ->isExpired($this->expired)
            ->orderByDesc('updated_at');

        $query = $query->filterBySearch($this->search);
        $query = $this->applyFilters($query);
        $practices = $query->paginate(15);

        // Fetch team members and customers for the dropdowns
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

        return view('livewire.admin.practice.practice-index', [
            'practices' => $practices,
            'productType' => $this->type,
            'expired' => $this->expired,
            'teamMembers' => $teamMembers,
            'customers' => $customers,
        ]);
    }
}
