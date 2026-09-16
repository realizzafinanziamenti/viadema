<?php

namespace App\Livewire\Admin\Practice;

use App\Enums\CustomerStatus;
use App\Enums\LeadSource;
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
use App\Models\PracticeOpportunity;
use App\Models\ProductSubtype;
use App\Models\ProductType;
use App\Models\User;
use App\Traits\AcceptedFileTypes;
use App\Traits\EnumHelper;
use App\Traits\HandlesPracticeInstallments;
use App\Traits\InteractsWithDropdowns;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class PracticeCreate extends Component
{
    use InteractsWithDropdowns;
    use HandlesPracticeInstallments;
    use AcceptedFileTypes;
    use WithFileUploads;
    use EnumHelper;

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

    public bool $shouldConvertLead = false;

    public bool $customerPreselected = false;

    public ?string $creationToken = null;

    /**
     * Set renewal flag.
     */
    public function setIsRenewal(
        string $value
    ): void {
        $this->practiceForm->isRenewal =
            $value === '1';
    }

    /**
     * Set acquisition channel.
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

    public function setTeamMember(
        ?int $value = null
    ): void {
        $this->setFormSelectValue(
            'userId',
            $value,
            'customerForm'
        );
    }

    public function setPracticeTeamMember(
        ?int $value = null
    ): void {
        $this->setFormSelectValue(
            'userId',
            $value,
            'practiceForm'
        );
    }

    /**
     * Select customer.
     */
    public function setCustomer(
        ?int $value = null
    ): void {
        $this->setFormSelectValue(
            'customerId',
            $value,
            'practiceForm'
        );

        $this->resetValidation(
            'practiceForm.customerId'
        );

        $this->selectedCustomer =
            Customer::find(
                $this->practiceForm->customerId
            );

        if ($this->selectedCustomer === null) {
            $this->customerForm->setCustomer(
                null
            );

            $this->shouldConvertLead = false;

            $this->resetValidation(
                'practiceForm.productTypeId'
            );

            return;
        }

        $this->shouldConvertLead =
            $this->selectedCustomer->isLead();

        $this->customerForm->setCustomer(
            $this->selectedCustomer
        );

        $this->customerForm->customerStatus =
            CustomerStatus::CUSTOMER->value;

        $this->customerForm->leadStatus = null;

        $this->customerSearch = '';

        /*
         * Customer changes can change the uniqueness
         * of an already selected product.
         */
        if ($this->practiceForm->productTypeId) {
            $this->validatePracticeProductUniqueness();
        }
    }

    /**
     * Set product and immediately verify duplicates.
     */
    public function setProductType(
        ?int $value = null
    ): void {
        $this->setFormSelectValue(
            'productTypeId',
            $value,
            'practiceForm'
        );

        $this->setRenewabilityAndAlertPercentage(
            $this->practiceForm
        );

        $this->recalculateRenewabilityDate(
            $this->practiceForm
        );

        $this->validatePracticeProductUniqueness();
    }

    public function setProductSubtype(
        ?int $value = null
    ): void {
        $this->setFormSelectValue(
            'productSubtypeId',
            $value,
            'practiceForm'
        );
    }

    public function setProductionType(
        ?string $value = null
    ): void {
        $this->setFormSelectValue(
            'productionType',
            $value,
            'practiceForm'
        );
    }

    public function setFinancialTable(
        ?int $value = null
    ): void {
        $this->setFormSelectValue(
            'financialTableId',
            $value,
            'practiceForm'
        );
    }

    public function setInsurance(
        ?int $value = null
    ): void {
        $this->setFormSelectValue(
            'insuranceId',
            $value,
            'practiceForm'
        );
    }

    public function setInstallment(
        ?int $value = null
    ): void {
        $this->setFormSelectValue(
            'installmentId',
            $value,
            'practiceForm'
        );

        $this->recalculateLastInstallmentDate(
            $this->practiceForm,
            $this->installments
        );

        $this->setRenewabilityAndAlertPercentage(
            $this->practiceForm
        );

        $this->recalculateRenewabilityDate(
            $this->practiceForm
        );
    }

    public function updatedPracticeFormFirstInstallmentDate(): void
    {
        if ($this->practiceForm->firstInstallmentDate) {
            $this->recalculateLastInstallmentDate(
                $this->practiceForm,
                $this->installments
            );

            $this->recalculateRenewabilityDate(
                $this->practiceForm
            );

            return;
        }

        $this->practiceForm->lastInstallmentDate =
            null;

        $this->practiceForm->renewabilityDate =
            null;
    }

    public function updatedPracticeFormRenewabilityPercentage(): void
    {
        $this->recalculateRenewabilityDate(
            $this->practiceForm
        );
    }

    public function setCustomerType(
        ?int $value = null
    ): void {
        $this->setFormSelectValue(
            'customerTypeId',
            $value,
            'practiceForm'
        );
    }

    /**
 * Open the create customer modal.
 */
public function openCreateCustomerModal(): void
{
    /*
     * Avoid carrying data from a previously selected
     * customer into the "create new customer" modal.
     */
    $this->customerForm->setCustomer(null);

    $this->customerForm->customerStatus =
        CustomerStatus::CUSTOMER->value;

    $this->customerForm->leadStatus = null;

    $this->teamMemberSearch = '';

    $this->dispatch(
        'open-modal',
        'customer-create'
    );
}
/**
 * Close create customer modal.
 */
public function closeCreateCustomerModal(): void
{
    $this->dispatch(
        'close-modal',
        'customer-create'
    );
}

    /**
     * Validate selected customer data before Step 2.
     */
    public function firstNextStep(): void
    {
        if (! $this->practiceForm->customerId) {
            $this->addError(
                'practiceForm.customerId',
                'Seleziona prima un cliente.'
            );

            return;
        }

        $this->customerForm->customerStatus =
            CustomerStatus::CUSTOMER->value;

        $this->customerForm->leadStatus = null;

        $this->customerForm
            ->validatedCustomerData();

        $this->teamMemberSearch = '';

        $this->step = 2;

        $this->dispatch('step-changed');
    }

    public function firstPrevStep(): void
    {
        $this->step = 1;

        $this->dispatch('step-changed');
    }

    /**
     * Validate Step 2 before summary.
     */
    public function secondNextStep(): void
    {
        if (! $this->practiceForm->practiceStatus) {
            $this->practiceForm->practiceStatus =
                PracticeStatus::UNDER_REVIEW->value;
        }

        $this->practiceForm->validate();

        if (! $this->validatePracticeProductUniqueness()) {
            return;
        }

        $this->step = 3;

        $this->dispatch('step-changed');
    }

    public function secondPrevStep(): void
    {
        $this->step = 2;

        $this->dispatch('step-changed');
    }

    /**
     * Realtime/UI duplicate validation.
     */
    private function validatePracticeProductUniqueness(): bool
    {
        $this->resetValidation(
            'practiceForm.productTypeId'
        );

        if (! $this->practiceForm->hasDuplicatePractice()) {
            return true;
        }

        $message =
            PracticeForm::DUPLICATE_PRODUCT_MESSAGE;

        $this->addError(
            'practiceForm.productTypeId',
            $message
        );

        Toaster::error($message);

        return false;
    }

    /**
     * Create a customer directly from Practice creation.
     */
    public function saveCustomer(): void
    {
        Gate::authorize(
            'create',
            [
                Customer::class,
                CustomerStatus::CUSTOMER,
            ]
        );

        $customer =
            $this->customerForm->store();

        if (! $customer instanceof Customer) {
            return;
        }

        $this->selectedCustomer =
            $customer;

        $this->practiceForm->customerId =
            $customer->getKey();

        $this->customerForm->setCustomer(
            $customer
        );

        $this->customerForm->customerStatus =
            CustomerStatus::CUSTOMER->value;

        $this->customerForm->leadStatus =
            null;

        $this->customerSearch = '';

        $this->shouldConvertLead = false;

        $this->dispatch(
            'close-modal',
            'customer-create'
        );
    }

    /**
     * Persist Customer and Practice atomically.
     */
    public function savePractice(): void
    {
        Gate::authorize(
            'create',
            Practice::class
        );

        try {
            $practice = DB::transaction(
                function (): Practice {
                    $customer =
                        Customer::query()
                            ->whereKey(
                                $this->practiceForm
                                    ->customerId
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                    Gate::authorize(
                        'update',
                        $customer
                    );

                    $wasLead =
                        $customer->isLead();

                    /*
                     * Do not call setCustomer() here because
                     * it would overwrite Step 1 edits.
                     */
                    $this->customerForm->customer =
                        $customer;

                    $this->customerForm->customerStatus =
                        CustomerStatus::CUSTOMER->value;

                    $this->customerForm->leadStatus =
                        null;

                    $customerData =
                        $this->customerForm
                            ->validatedCustomerData();

                    $customer->update(
                        $customerData
                    );

                    $this->selectedCustomer =
                        $customer;

                    $this->practiceForm->customerId =
                        $customer->getKey();

                    if ($wasLead) {
                        Log::info(
                            "Lead {$customer->id} convertito in cliente per la pratica"
                        );
                    }

                    $practice =
                        $this->practiceForm->store();

                    if (! $practice instanceof Practice) {
                        throw new Exception(
                            'Errore durante la creazione della pratica.'
                        );
                    }

                    if ($this->creationToken) {
                        Cache::forget(
                            "practice_creation_{$this->creationToken}"
                        );

                        Log::info(
                            "Token {$this->creationToken} rimosso dalla cache"
                        );
                    }

                    return $practice;
                },
                3
            );

            if (Gate::allows('view', $practice)) {
                $this->redirectRoute(
                    'practice.show',
                    [
                        'id' =>
                            $practice->getKey(),
                    ],
                    navigate: true
                );

                return;
            }

            $this->redirectRoute(
                'practice.index',
                navigate: true
            );
        } catch (ValidationException $exception) {
            $this->handleValidationException(
                $exception
            );
        } catch (Exception $exception) {
            Log::error(
                'Errore durante la creazione della pratica: '
                . $exception->getMessage(),
                [
                    'customer_id' =>
                        $this->practiceForm
                            ->customerId,

                    'product_type_id' =>
                        $this->practiceForm
                            ->productTypeId,

                    'exception' =>
                        $exception,
                ]
            );

            Toaster::error(
                'Si è verificato un errore durante la creazione della pratica: '
                . $exception->getMessage()
            );
        }
    }

    /**
     * Handle validation that may still fail during
     * the final transactional save.
     */
    private function handleValidationException(
        ValidationException $exception
    ): void {
        $errors = $exception->errors();

        foreach ($errors as $field => $messages) {
            foreach ($messages as $message) {
                $this->addError(
                    $field,
                    $message
                );
            }
        }

        $fields =
            array_keys($errors);

        $hasCustomerError =
            collect($fields)
                ->contains(
                    fn (string $field): bool =>
                        str_starts_with(
                            $field,
                            'customerForm.'
                        )
                        || $field
                            === 'practiceForm.customerId'
                );

        if ($hasCustomerError) {
            $this->step = 1;
        } else {
            $hasPracticeError =
                collect($fields)
                    ->contains(
                        fn (string $field): bool =>
                            str_starts_with(
                                $field,
                                'practiceForm.'
                            )
                    );

            if ($hasPracticeError) {
                $this->step = 2;
            }
        }

        $firstMessage =
            collect($errors)
                ->flatten()
                ->first();

        if ($firstMessage) {
            Toaster::error(
                (string) $firstMessage
            );
        }

        $this->dispatch('step-changed');
    }

    public function updatedTemporaryFiles(): void
    {
        $this->validate(
            [
                'temporaryFiles' => [
                    'nullable',
                    'array',
                    'max:10',
                ],

                'temporaryFiles.*' => [
                    'nullable',
                    'file',
                    'mimetypes:'
                        . implode(
                            ',',
                            $this->acceptedFileTypesArray()
                        ),
                    'max:10240',
                ],
            ],
            [
                'temporaryFiles.max' =>
                    'Puoi caricare al massimo 10 file.',

                'temporaryFiles.*.max' =>
                    'Ogni file non può superare i 10MB.',

                'temporaryFiles.*.mimetypes' =>
                    'Formato file non valido.',
            ]
        );

        foreach ($this->temporaryFiles as $file) {
            $this->practiceForm
                ->attachments[] = $file;
        }
    }

    public function deleteTemporaryFile(
        int $index
    ): void {
        if (
            ! isset(
                $this->practiceForm
                    ->attachments[$index]
            )
        ) {
            return;
        }

        unset(
            $this->practiceForm
                ->attachments[$index]
        );

        $this->practiceForm->attachments =
            array_values(
                $this->practiceForm
                    ->attachments
            );
    }

    /**
     * Initialize select options.
     */
    protected function initSelectValues(): void
    {
        $this->productTypes =
            ProductType::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();

        $this->productSubtypes =
            ProductSubtype::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();

        $this->installments =
            Installment::query()
                ->orderBy('value')
                ->pluck('value', 'id')
                ->toArray();

        $this->financialTables =
            FinancialTable::query()
                ->orderBy('percentage')
                ->pluck('percentage', 'id')
                ->toArray();

        $this->insurances =
            Insurance::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();

        $this->customerTypes =
            CustomerType::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();

        $this->productionTypes =
            $this->getEnumOptions(
                ProductionType::class
            );

        $this->leadSources =
            $this->getEnumOptions(
                LeadSource::class
            );
    }

    /**
     * Load Customer/Lead from creation token.
     */
    private function loadCustomerFromToken(
        string $token
    ): void {
        $data = Cache::get(
            "practice_creation_{$token}"
        );

        if (! $data) {
            abort(
                403,
                'Sessione di creazione pratica scaduta o non valida. Riprova dal lead.'
            );
        }

        if (
            ($data['user_id'] ?? null)
            !== auth()->id()
        ) {
            abort(
                403,
                'Non sei autorizzato ad accedere a questa sessione di creazione pratica.'
            );
        }

        $customer = Customer::find(
            $data['customer_id'] ?? null
        );

        if (! $customer) {
            abort(
                404,
                'Lead non trovato.'
            );
        }

        Gate::authorize(
            'view',
            $customer
        );

        $this->selectedCustomer =
            $customer;

        $this->practiceForm->customerId =
            $customer->getKey();

        $this->customerPreselected =
            true;

        $this->shouldConvertLead =
            ($data['convert_lead'] ?? false)
            && $customer->isLead();

        $this->creationToken =
            $token;

        $this->customerForm->setCustomer(
            $customer
        );

        $this->customerForm->customerStatus =
            CustomerStatus::CUSTOMER->value;

        $this->customerForm->leadStatus =
            null;

        $opportunityId =
            $data['practice_opportunity_id']
                ?? null;

        if ($opportunityId) {
            $opportunity =
                PracticeOpportunity::query()
                    ->where(
                        'customer_id',
                        $customer->getKey()
                    )
                    ->findOrFail(
                        $opportunityId
                    );

            $this->practiceForm
                ->setOpportunity(
                    $opportunity
                );

            $this->practiceForm->customerId =
                $customer->getKey();

            $this->setRenewabilityAndAlertPercentage(
                $this->practiceForm
            );

            $this->recalculateLastInstallmentDate(
                $this->practiceForm,
                $this->installments
            );

            $this->recalculateRenewabilityDate(
                $this->practiceForm
            );
        }
    }

    public function mount(
        ?string $token = null
    ): void {
        Gate::authorize(
            'create',
            Practice::class
        );

        $this->initSelectValues();

        $this->customerForm->customerStatus =
            CustomerStatus::CUSTOMER->value;

        if ($token) {
            $this->loadCustomerFromToken(
                $token
            );
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $teamMembers =
            User::assignableUsers()
                ->filterBySearch(
                    $this->teamMemberSearch
                )
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
                ->pluck('full_name', 'id')
                ->toArray();

        $customers =
            Customer::filterBySearch(
                $this->customerSearch
            )
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
                ->pluck('full_name', 'id')
                ->toArray();

        return view(
            'livewire.admin.practice.practice-create',
            [
                'teamMembers' =>
                    $teamMembers,

                'customers' =>
                    $customers,
            ]
        );
    }
}