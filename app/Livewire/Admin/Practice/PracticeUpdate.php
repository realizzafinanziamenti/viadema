<?php

namespace App\Livewire\Admin\Practice;

use App\Enums\CustomerStatus;
use App\Enums\LeadSource;
use App\Enums\ProductionType;
use App\Livewire\Forms\CustomerForm;
use App\Livewire\Forms\PracticeForm;
use App\Models\Attachment;
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
use App\Traits\HandlesEntityActions;
use App\Traits\HandlesPracticeInstallments;
use App\Traits\InteractsWithDropdowns;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PracticeUpdate extends Component
{
    use InteractsWithDropdowns;
    use HandlesPracticeInstallments;
    use AcceptedFileTypes;
    use WithFileUploads;
    use HandlesEntityActions;
    use EnumHelper;

    public Practice $practice;

    public PracticeForm $practiceForm;

    public CustomerForm $customerForm;

    public ?Customer $selectedCustomer = null;

    public ?Attachment $selectedAttachment = null;

    public array $temporaryFiles = [];

    public array $productTypes = [];

    public array $productSubtypes = [];

    public array $financialTables = [];

    public array $insurances = [];

    public array $installments = [];

    public array $customerTypes = [];

    public array $practiceStatuses = [];

    public array $productionTypes = [];

    public array $leadSources = [];

    public int $step = 1;

    public string $teamMemberSearch = '';

    public string $customerSearch = '';

    public bool $selectsInitialized = false;

    public bool $shouldConvertLead = false;

    public bool $customerPreselected = false;

    /**
     * Set renewal flag.
     */
    public function setIsRenewal(string $value): void
    {
        $this->practiceForm->isRenewal = $value === '1';
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
     * Set customer.
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

        if (! $this->practiceForm->customerId) {
            $this->selectedCustomer = null;

            $this->customerForm->reset();

            $this->customerForm->customerStatus =
                CustomerStatus::CUSTOMER->value;

            $this->resetValidation(
                'practiceForm.productTypeId'
            );

            return;
        }

        $customer = Customer::findOrFail(
            $this->practiceForm->customerId
        );

        $this->selectedCustomer = $customer;

        $this->customerForm->setCustomer($customer);

        $this->customerForm->customerStatus =
            CustomerStatus::CUSTOMER->value;

        $this->customerForm->leadStatus = null;

        $this->customerSearch = '';

        /*
         * If a product was already selected, changing the
         * customer may change the duplicate condition.
         */
        if ($this->practiceForm->productTypeId) {
            $this->validatePracticeProductUniqueness();
        }
    }

    /**
     * Set product type and immediately verify duplicates.
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
     * Validate customer data before moving to step 2.
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

        $customer = Customer::findOrFail(
            $this->practiceForm->customerId
        );

        Gate::authorize('update', $customer);

        $this->customerForm->customer = $customer;

        $this->customerForm->customerStatus =
            CustomerStatus::CUSTOMER->value;

        $this->customerForm->leadStatus = null;

        $this->customerForm->validatedCustomerData();

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
     * Validate Practice data before moving to summary.
     */
    public function secondNextStep(): void
    {
        $this->practiceForm->validate();

        /*
         * The wizard must never reach Step 3 with an
         * already existing customer + product combination.
         */
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
     *
     * The transactional validation inside PracticeForm
     * still remains the final integrity guarantee.
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
     * Initialize selects.
     */
    protected function initializeSelects(): void
    {
        $this->productTypes = ProductType::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->productSubtypes = ProductSubtype::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->financialTables = FinancialTable::query()
            ->orderBy('percentage')
            ->pluck('percentage', 'id')
            ->toArray();

        $this->insurances = Insurance::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->installments = Installment::query()
            ->orderBy('value')
            ->pluck('value', 'id')
            ->toArray();

        $this->customerTypes = CustomerType::query()
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
     * Create a customer from Practice update.
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

        if (! $customer instanceof Customer) {
            return;
        }

        $this->selectedCustomer = $customer;

        $this->practiceForm->customerId =
            $customer->getKey();

        $this->customerForm->setCustomer(
            $customer
        );

        $this->customerForm->customerStatus =
            CustomerStatus::CUSTOMER->value;

        $this->customerForm->leadStatus = null;

        $this->customerSearch = '';

        $this->dispatch(
            'close-modal',
            'customer-create'
        );
    }

    /**
     * Save practice and customer atomically.
     */
    public function savePractice(): void
    {
        Gate::authorize(
            'update',
            $this->practice
        );

        try {
            $practice = DB::transaction(
                function (): Practice {
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

                    /*
                     * Do not call setCustomer() here:
                     * it would overwrite edits made in Step 1.
                     */
                    $this->customerForm->customer =
                        $customer;

                    $this->customerForm->customerStatus =
                        CustomerStatus::CUSTOMER->value;

                    $this->customerForm->leadStatus = null;

                    $customerData =
                        $this->customerForm
                            ->validatedCustomerData();

                    $customer->update(
                        $customerData
                    );

                    $this->practiceForm->customerId =
                        $customer->getKey();

                    $practice =
                        $this->practiceForm->update();

                    if (! $practice instanceof Practice) {
                        throw new Exception(
                            'Impossibile aggiornare la pratica.'
                        );
                    }

                    $this->selectedCustomer =
                        $customer->fresh();

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
                'Errore durante l\'aggiornamento della pratica.',
                [
                    'practice_id' =>
                        $this->practice->getKey(),

                    'error' =>
                        $exception->getMessage(),

                    'exception' =>
                        $exception,
                ]
            );

            Toaster::error(
                'Errore durante l\'aggiornamento della pratica.'
            );
        }
    }

    /**
     * Put validation errors in the Livewire error bag
     * and return the wizard to the relevant step.
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

        $fields = array_keys($errors);

        $hasCustomerError = collect($fields)
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
            $hasPracticeError = collect($fields)
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

    public function download(
        int $id
    ): ?StreamedResponse {
        Gate::authorize(
            'view',
            $this->practice
        );

        try {
            $attachment =
                Attachment::findOrFail($id);

            return Storage::download(
                $attachment->file_path,
                $attachment->file_name
            );
        } catch (Exception $exception) {
            Toaster::error(
                'File non trovato o errore: '
                . $exception->getMessage()
            );

            return null;
        }
    }

    public function selectAttachmentForDelete(
        int $id
    ): void {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Attachment::class,
            property: 'selectedAttachment',
            modalName: 'delete-attachment',
            notFoundMessage: 'Allegato non trovato'
        );
    }

    public function deleteAttachment(): void
    {
        Gate::authorize(
            'delete',
            $this->practice
        );

        try {
            DB::transaction(function (): void {
                Storage::disk('public')
                    ->delete(
                        $this->selectedAttachment
                            ->file_path
                    );

                $this->selectedAttachment
                    ->delete();
            });

            Toaster::success(
                'Allegato eliminato con successo'
            );

            $this->dispatch(
                'close-modal',
                'delete-attachment'
            );
        } catch (Exception $exception) {
            Toaster::error(
                'Errore durante l\'eliminazione dell\'allegato: '
                . $exception->getMessage()
            );
        }
    }

    public function mount($id): void
    {
        $this->practice =
            Practice::query()
                ->with([
                    'customer',
                    'opportunity',
                ])
                ->findOrFail($id);

        Gate::authorize(
            'update',
            $this->practice
        );

        $this->practiceForm
            ->setPractice(
                $this->practice
            );

        $this->selectedCustomer =
            $this->practice->customer;

        if ($this->selectedCustomer) {
            $this->customerForm
                ->setCustomer(
                    $this->selectedCustomer
                );

            $this->customerForm->customerStatus =
                CustomerStatus::CUSTOMER->value;

            $this->customerForm->leadStatus =
                null;
        }

        $this->initializeSelects();
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
            'livewire.admin.practice.practice-update',
            [
                'teamMembers' =>
                    $teamMembers,

                'customers' =>
                    $customers,

                'selectedUserId' =>
                    $this->customerForm->userId,
            ]
        );
    }
}