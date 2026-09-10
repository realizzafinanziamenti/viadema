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

    /**
     * Kept for compatibility with the current workflow.
     *
     * The actual conversion is performed transactionally
     * inside savePractice().
     */
    public bool $shouldConvertLead = false;

    public bool $customerPreselected = false;

    public ?string $creationToken = null;

    /**
     * Set renewal flag.
     */
    public function setIsRenewal(string $value): void
    {
        $this->practiceForm->isRenewal = $value === '1';
    }

    /**
     * Set acquisition channel on the practice opportunity.
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
     * Set team member for customer form.
     */
    public function setTeamMember(?int $value = null): void
    {
        $this->setFormSelectValue(
            'userId',
            $value,
            'customerForm'
        );
    }

    /**
     * Set team member for practice form.
     */
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
     * Select the customer associated with the practice.
     *
     * The same Customer record is also loaded into
     * CustomerForm so its personal data can be edited
     * directly while creating the practice.
     */
    public function setCustomer(?int $value = null): void
    {
        $this->setFormSelectValue(
            'customerId',
            $value,
            'practiceForm'
        );

        $this->resetValidation(
            'practiceForm.customerId'
        );

        $this->selectedCustomer = Customer::find(
            $this->practiceForm->customerId
        );

        if ($this->selectedCustomer === null) {
            $this->customerForm->setCustomer(null);
            $this->shouldConvertLead = false;

            return;
        }

        $this->shouldConvertLead =
            $this->selectedCustomer->isLead();

        /*
         * Load current customer data into the editable
         * customer form.
         */
        $this->customerForm->setCustomer(
            $this->selectedCustomer
        );

        /*
         * Inside the Practice workflow the profile must
         * satisfy CUSTOMER requirements.
         *
         * This makes taxId required even when the selected
         * record is currently a Lead.
         */
        $this->customerForm->customerStatus =
            CustomerStatus::CUSTOMER->value;

        $this->customerForm->leadStatus = null;
    }

    /**
     * Set product type.
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
    }

    /**
     * Set product subtype.
     */
    public function setProductSubtype(
        ?int $value = null
    ): void {
        $this->setFormSelectValue(
            'productSubtypeId',
            $value,
            'practiceForm'
        );
    }

    /**
     * Set production type.
     */
    public function setProductionType(
        ?string $value = null
    ): void {
        $this->setFormSelectValue(
            'productionType',
            $value,
            'practiceForm'
        );
    }

    /**
     * Set financial table.
     */
    public function setFinancialTable(
        ?int $value = null
    ): void {
        $this->setFormSelectValue(
            'financialTableId',
            $value,
            'practiceForm'
        );
    }

    /**
     * Set insurance.
     */
    public function setInsurance(
        ?int $value = null
    ): void {
        $this->setFormSelectValue(
            'insuranceId',
            $value,
            'practiceForm'
        );
    }

    /**
     * Set installment.
     */
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

    /**
     * Recalculate installment and renewability dates.
     */
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

        $this->practiceForm->lastInstallmentDate = null;
        $this->practiceForm->renewabilityDate = null;
    }

    /**
     * Recalculate renewability date.
     */
    public function updatedPracticeFormRenewabilityPercentage(): void
    {
        $this->recalculateRenewabilityDate(
            $this->practiceForm
        );
    }

    /**
     * Set customer type.
     */
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
     * Validate customer data before moving to step 2.
     *
     * No database write happens here.
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

        /*
         * CustomerForm applies CUSTOMER validation here,
         * therefore taxId is mandatory.
         */
        $this->customerForm
            ->validatedCustomerData();

        $this->teamMemberSearch = '';
        $this->step = 2;

        $this->dispatch('step-changed');
    }

    /**
     * Return to step 1.
     */
    public function firstPrevStep(): void
    {
        $this->step = 1;

        $this->dispatch('step-changed');
    }

    /**
     * Validate practice data before moving to summary.
     */
    public function secondNextStep(): void
    {
        if (! $this->practiceForm->practiceStatus) {
            $this->practiceForm->practiceStatus =
                PracticeStatus::UNDER_REVIEW->value;
        }

        $this->practiceForm->validate();

        $this->step = 3;

        $this->dispatch('step-changed');
    }

    /**
     * Return to step 2.
     */
    public function secondPrevStep(): void
    {
        $this->step = 2;

        $this->dispatch('step-changed');
    }

    /**
     * Create a new customer directly from the Practice page.
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

        $customer = $this->customerForm->store();

        /*
         * CustomerForm currently handles generic persistence
         * exceptions internally and can therefore return null.
         */
        if (! $customer instanceof Customer) {
            return;
        }

        $this->selectedCustomer = $customer;

        $this->practiceForm->customerId =
            $customer->getKey();

        /*
         * Keep the form linked to the newly-created record.
         */
        $this->customerForm->customer = $customer;

        $this->customerForm->customerStatus =
            CustomerStatus::CUSTOMER->value;

        $this->customerForm->leadStatus = null;

        $this->shouldConvertLead = false;

        $this->dispatch(
            'close-modal',
            'customer-create'
        );
    }

    /**
     * Persist customer data and create the practice atomically.
     */
    public function savePractice(): void
    {
        Gate::authorize('create', Practice::class);

        try {
            $practice = DB::transaction(function (): Practice {
                /*
                 * Re-fetch and lock the selected Customer so
                 * concurrent requests cannot modify the same
                 * identity while the Practice is being created.
                 */
                $customer = Customer::query()
                    ->whereKey(
                        $this->practiceForm->customerId
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                Gate::authorize(
                    'update',
                    $customer
                );

                $wasLead = $customer->isLead();

                /*
                 * Bind the locked database record to the form.
                 *
                 * Do NOT call setCustomer() here because that
                 * would overwrite the edits performed by the
                 * user in the Practice screen.
                 */
                $this->customerForm->customer =
                    $customer;

                /*
                 * Any profile attached to a Practice must
                 * satisfy CUSTOMER rules.
                 */
                $this->customerForm->customerStatus =
                    CustomerStatus::CUSTOMER->value;

                $this->customerForm->leadStatus = null;

                /*
                 * Validate again at write-time.
                 *
                 * Among other things:
                 * - taxId is required
                 * - taxId must be unique
                 */
                $customerData =
                    $this->customerForm
                        ->validatedCustomerData();

                /*
                 * Same database record:
                 *
                 * LEAD #123
                 * becomes
                 * CUSTOMER #123
                 *
                 * No duplicated Customer is created.
                 */
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

                /*
                 * PracticeForm handles:
                 * - Practice validation
                 * - required product
                 * - customer + product duplicate check
                 * - PracticeOpportunity persistence
                 * - Practice persistence
                 */
                $practice =
                    $this->practiceForm->store();

                if (! $practice instanceof Practice) {
                    throw new Exception(
                        'Errore durante la creazione della pratica.'
                    );
                }

                /*
                 * Consume the Lead -> Practice creation token
                 * only after the entire operation succeeds.
                 */
                if ($this->creationToken) {
                    Cache::forget(
                        "practice_creation_{$this->creationToken}"
                    );

                    Log::info(
                        "Token {$this->creationToken} rimosso dalla cache"
                    );
                }

                return $practice;
            }, 3);

            if (Gate::allows('view', $practice)) {
                $this->redirectRoute(
                    'practice.show',
                    ['id' => $practice->getKey()],
                    navigate: true
                );

                return;
            }

            $this->redirectRoute(
                'practice.index',
                navigate: true
            );
        } catch (ValidationException $exception) {
            /*
             * Let Livewire handle field validation errors
             * instead of hiding them behind a generic toast.
             */
            throw $exception;
        } catch (Exception $exception) {
            Log::error(
                'Errore durante la creazione della pratica: '
                . $exception->getMessage(),
                [
                    'customer_id' =>
                        $this->practiceForm->customerId,
                    'product_type_id' =>
                        $this->practiceForm->productTypeId,
                    'exception' => $exception,
                ]
            );

            Toaster::error(
                'Si è verificato un errore durante la creazione della pratica: '
                . $exception->getMessage()
            );
        }
    }

    /**
     * Handle temporary attachment uploads.
     */
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

        foreach (
            $this->temporaryFiles
            as $file
        ) {
            $this->practiceForm
                ->attachments[] = $file;
        }
    }

    /**
     * Remove a temporary attachment.
     */
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
     * Load a Lead/Customer coming from the
     * "create Practice" action.
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

        $this->customerPreselected = true;

        $this->shouldConvertLead =
            ($data['convert_lead'] ?? false)
            && $customer->isLead();

        $this->creationToken = $token;

        /*
         * Populate editable customer data.
         */
        $this->customerForm->setCustomer(
            $customer
        );

        /*
         * setCustomer() copied the real status from DB.
         * If this is a Lead it therefore copied LEAD.
         *
         * Inside Practice creation we intentionally
         * validate it as CUSTOMER, making taxId required.
         *
         * The actual conversion is NOT persisted yet.
         * It happens only inside savePractice().
         */
        $this->customerForm->customerStatus =
            CustomerStatus::CUSTOMER->value;

        $this->customerForm->leadStatus = null;

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

            /*
             * setOpportunity() also restores its
             * customer_id into PracticeForm.
             */
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

    /**
     * Initialize the Practice creation page.
     */
    public function mount(
        ?string $token = null
    ): void {
        Gate::authorize(
            'create',
            Practice::class
        );

        $this->initSelectValues();

        /*
         * A new customer created from this page is always
         * a CUSTOMER, therefore taxId is required.
         */
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
                'teamMembers' => $teamMembers,
                'customers' => $customers,
            ]
        );
    }
}
