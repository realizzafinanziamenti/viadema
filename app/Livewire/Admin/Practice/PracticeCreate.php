<?php

namespace App\Livewire\Admin\Practice;

use App\Enums\CustomerStatus;
use App\Enums\PracticeStatus;
use App\Enums\ProductionType;
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
use App\Traits\EnumHelper;
use App\Traits\HandlesPracticeInstallments;
use App\Traits\InteractsWithDropdowns;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;
use App\Models\PracticeOpportunity;
use App\Enums\LeadSource;

class PracticeCreate extends Component
{
    use InteractsWithDropdowns, HandlesPracticeInstallments, AcceptedFileTypes, WithFileUploads, EnumHelper;

    public array $leadSources = [];

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
    public array $productionTypes = [];
    public int $step = 1;
    public string $teamMemberSearch = '';
    public string $customerSearch = '';
    public bool $shouldConvertLead = false; // Flag to indicate if converting lead to customer
    public bool $customerPreselected = false; // Flag to indicate if customer is preselected
    public ?string $creationToken = null; // Token to identify preselected customer

    /**
     * Set isRenewal in the form.
     */
    public function setIsRenewal(string $value): void
    {
        $this->practiceForm->isRenewal = ($value === '1');
    }
/**
 * Set acquisition channel on the practice opportunity data.
 */
    public function setOpportunityAcquisitionChannel(
        ?string $value = null
    ): void {
        $this->setFormSelectValue(
            'acquisitionChannel',
            $value,
            'practiceForm'
        );
    }

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
     * Set production type
     */
    public function setProductionType(?string $value = null): void
    {
        $this->setFormSelectValue('productionType', $value, 'practiceForm');
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

        try {
            $practice = DB::transaction(function () {
                // convert lead to customer if needed
                if ($this->shouldConvertLead && $this->selectedCustomer) {
                    Gate::authorize('update', $this->selectedCustomer);

                    try {
                        // re-fetch customer with lock to prevent race conditions
                        $this->selectedCustomer = Customer::where('id', $this->selectedCustomer->id)
                            ->lockForUpdate()
                            ->firstOrFail();
                    } catch (ModelNotFoundException $e) {
                        throw new Exception('Il cliente selezionato non è più disponibile');
                    }

                    // check if still a lead
                    if ($this->selectedCustomer->customer_status !== CustomerStatus::LEAD) {
                        throw new Exception('Il cliente non è più un lead');
                    }

                    $this->selectedCustomer->update([
                        'customer_status' => CustomerStatus::CUSTOMER->value,
                        'lead_status' => null,
                    ]);

                    Log::info("Lead {$this->selectedCustomer->id} convertito in cliente per la pratica");
                }

                // create practice
                $practice = $this->practiceForm->store();

                if (!$practice) {
                    throw new Exception('Errore durante la creazione della pratica');
                }

                // remove token only after successful completion
                if ($this->creationToken) {
                    Cache::forget("practice_creation_{$this->creationToken}");
                    Log::info("Token {$this->creationToken} rimosso dalla cache");
                }

                return $practice;
            });

            if (Gate::allows('view', $practice)) {
                $this->redirectRoute('practice.show', ['id' => $practice->id], navigate: true);
            } else {
                $this->redirectRoute('practice.index', navigate: true);
            }
        } catch (Exception $e) {
            Log::error('Error creating practice: ' . $e->getMessage());

            // specific message for lead conversion errors
            if (str_contains($e->getMessage(), 'lead')) {
                Toaster::error('Errore durante la conversione del lead: ' . $e->getMessage());
            } else {
                Toaster::error('Si è verificato un errore durante la creazione della pratica: ' . $e->getMessage());
            }
        }
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

        $this->productionTypes = $this->getEnumOptions(ProductionType::class);

        $this->leadSources = $this->getEnumOptions(LeadSource::class);
    }

    /**
     * Load customer from token
     */
    private function loadCustomerFromToken(string $token): void
    {
        // retrieve data from cache
        $data = Cache::get("practice_creation_{$token}");

        // if no data or user id does not match, abort
        if (!$data) {
            abort(403, 'Sessione di creazione pratica scaduta o non valida. Riprova dal lead.');
        }

        if ($data['user_id'] !== auth()->id()) {
            abort(403, 'Non sei autorizzato ad accedere a questa sessione di creazione pratica.');
        }

        $customer = Customer::find($data['customer_id']);

        if (!$customer) {
            abort(404, 'Lead non trovato.');
        }

        Gate::authorize('view', $customer);

        $this->selectedCustomer = $customer;
        $this->practiceForm->customerId = $customer->id;
        $this->customerPreselected = true;
        $this->shouldConvertLead = $data['convert_lead'] && $customer->customer_status?->value === CustomerStatus::LEAD->value;
        $this->creationToken = $token;

        $this->customerForm->setCustomer($this->selectedCustomer);
        $opportunityId = $data['practice_opportunity_id'] ?? null;

if ($opportunityId) {
    $opportunity = PracticeOpportunity::where('customer_id', $customer->id)
        ->findOrFail($opportunityId);

    $this->practiceForm->setOpportunity($opportunity);

    $this->setRenewabilityAndAlertPercentage($this->practiceForm);
    $this->recalculateLastInstallmentDate($this->practiceForm, $this->installments);
    $this->recalculateRenewabilityDate($this->practiceForm);
}
    }


    public function mount(?string $token = null)
    {
        Gate::authorize('create', Practice::class);
        $this->initSelectValues();

        // Initialize customer status to CUSTOMER
        $this->customerForm->customerStatus = CustomerStatus::CUSTOMER->value;

        // If token is provided, load customer from token
        if ($token) {
            $this->loadCustomerFromToken($token);
        }
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