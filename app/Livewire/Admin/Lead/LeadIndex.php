<?php

namespace App\Livewire\Admin\Lead;

use App\Models\Installment;
use App\Models\Insurance;
use App\Models\ProductSubtype;
use App\Models\ProductType;
use Illuminate\Database\Eloquent\Builder;

use App\Enums\CustomerStatus;
use App\Enums\LeadStatus;
use App\Exports\LeadsExport;
use App\Imports\LeadsImport;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\ImportExcelCompleted;
use App\Traits\AcceptedFileTypes;
use App\Traits\EnumHelper;
use App\Traits\HandlesEntityActions;
use App\Traits\InteractsWithDropdowns;
use App\Traits\WithBulkSelection;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\Auth;
use App\Enums\LeadSource;
use App\Models\CustomerType;
use Illuminate\Validation\Rules\Enum;
class LeadIndex extends Component
{
    use WithPagination, WithoutUrlPagination, HandlesEntityActions, InteractsWithDropdowns, EnumHelper, WithFileUploads, WithBulkSelection, AcceptedFileTypes;

    public ?Customer $selectedLead = null;
    public array $leadStatuses = [];
    public ?string $selectedLeadStatus = null;
    public ?string $selectedLeadRecontactDate = null;
    public $search = '';

        // Team Member Filter
    public string $teamMemberSearch = '';
    public ?int $selectedTeamMemberForFilter = null;
    public ?int $tempSelectedTeamMemberForFilter = null;

    // Lead Status Filter
    public ?string $selectedLeadStatusForFilter = null;
    public ?string $tempSelectedLeadStatusForFilter = null;

    // Lead Source Filter
    public array $leadSourcesForFilter = [];
    public ?string $selectedLeadSourceForFilter = null;
    public ?string $tempSelectedLeadSourceForFilter = null;

    // Customer Type Filter
    public array $customerTypes = [];
    public ?int $selectedCustomerTypeForFilter = null;
    public ?int $tempSelectedCustomerTypeForFilter = null;

    // Created At Date filters
    public ?string $createdAtDateMin = null;
    public ?string $tempCreatedAtDateMin = null;
    public ?string $createdAtDateMax = null;
    public ?string $tempCreatedAtDateMax = null;

    // Updated At Date filters
    public ?string $updatedAtDateMin = null;
    public ?string $tempUpdatedAtDateMin = null;
    public ?string $updatedAtDateMax = null;
    public ?string $tempUpdatedAtDateMax = null;

    // Recontact Date filters
    public ?string $recontactDateMin = null;
    public ?string $tempRecontactDateMin = null;
    public ?string $recontactDateMax = null;
    public ?string $tempRecontactDateMax = null;
    // Import file properties
    public ?TemporaryUploadedFile $temporaryImportFile = null;
    public ?TemporaryUploadedFile $importFile = null;
    public ?int $userId = null;   // user for assigning imported practices
    public string $userSearch = '';

    // Product Type Filter
public array $productTypes = [];
public ?int $selectedProductTypeForFilter = null;
public ?int $tempSelectedProductTypeForFilter = null;

// Product Subtype Filter
public array $productSubtypes = [];
public ?int $selectedProductSubtypeForFilter = null;
public ?int $tempSelectedProductSubtypeForFilter = null;

// Insurance Filter
public array $insurances = [];
public ?int $selectedInsuranceForFilter = null;
public ?int $tempSelectedInsuranceForFilter = null;

// Installment Filter
public array $installments = [];
public ?int $selectedInstallmentForFilter = null;
public ?int $tempSelectedInstallmentForFilter = null;

// First Installment Date Filters
public ?string $firstInstallmentDateMin = null;
public ?string $tempFirstInstallmentDateMin = null;
public ?string $firstInstallmentDateMax = null;
public ?string $tempFirstInstallmentDateMax = null;

// Last Installment Date Filters
public ?string $lastInstallmentDateMin = null;
public ?string $tempLastInstallmentDateMin = null;
public ?string $lastInstallmentDateMax = null;
public ?string $tempLastInstallmentDateMax = null;

// Amount Disbursed Filters
public ?float $amountDisbursedMin = null;
public ?float $tempAmountDisbursedMin = null;
public ?float $amountDisbursedMax = null;
public ?float $tempAmountDisbursedMax = null;

// Total Amount Filters
public ?float $totalAmountMin = null;
public ?float $tempTotalAmountMin = null;
public ?float $totalAmountMax = null;
public ?float $tempTotalAmountMax = null;

// Rate Amount Filters
public ?float $rateAmountMin = null;
public ?float $tempRateAmountMin = null;
public ?float $rateAmountMax = null;
public ?float $tempRateAmountMax = null;

// TAN Filters
public ?float $tanMin = null;
public ?float $tempTanMin = null;
public ?float $tanMax = null;
public ?float $tempTanMax = null;

// TAEG Filters
public ?float $taegMin = null;
public ?float $tempTaegMin = null;
public ?float $taegMax = null;
public ?float $tempTaegMax = null;

        protected function rules(): array
        {
            return [
                'tempSelectedTeamMemberForFilter' => ['nullable', 'integer', 'exists:users,id'],
                'tempSelectedLeadStatusForFilter' => ['nullable', new Enum(LeadStatus::class)],
                'tempSelectedLeadSourceForFilter' => ['nullable', new Enum(LeadSource::class)],
                'tempSelectedCustomerTypeForFilter' => ['nullable', 'integer', 'exists:customer_types,id'],

                'tempCreatedAtDateMin' => ['nullable', 'date', 'before_or_equal:tempCreatedAtDateMax'],
                'tempCreatedAtDateMax' => ['nullable', 'date', 'after_or_equal:tempCreatedAtDateMin'],

                'tempUpdatedAtDateMin' => ['nullable', 'date', 'before_or_equal:tempUpdatedAtDateMax'],
                'tempUpdatedAtDateMax' => ['nullable', 'date', 'after_or_equal:tempUpdatedAtDateMin'],

                'tempRecontactDateMin' => ['nullable', 'date', 'before_or_equal:tempRecontactDateMax'],
                'tempRecontactDateMax' => ['nullable', 'date', 'after_or_equal:tempRecontactDateMin'],
                'tempSelectedProductTypeForFilter' => [
                    'nullable',
                    'integer',
                    'exists:product_types,id',
                ],
                'tempSelectedProductSubtypeForFilter' => [
                    'nullable',
                    'integer',
                    'exists:product_subtypes,id',
                ],
                'tempSelectedInsuranceForFilter' => [
                    'nullable',
                    'integer',
                    'exists:insurances,id',
                ],
                'tempSelectedInstallmentForFilter' => [
                    'nullable',
                    'integer',
                    'exists:installments,id',
                ],

                'tempFirstInstallmentDateMin' => [
                    'nullable',
                    'date',
                    'before_or_equal:tempFirstInstallmentDateMax',
                ],
                'tempFirstInstallmentDateMax' => [
                    'nullable',
                    'date',
                    'after_or_equal:tempFirstInstallmentDateMin',
                ],

                'tempLastInstallmentDateMin' => [
                    'nullable',
                    'date',
                    'before_or_equal:tempLastInstallmentDateMax',
                ],
                'tempLastInstallmentDateMax' => [
                    'nullable',
                    'date',
                    'after_or_equal:tempLastInstallmentDateMin',
                ],

                'tempAmountDisbursedMin' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                    'lte:tempAmountDisbursedMax',
                ],
                'tempAmountDisbursedMax' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                    'gte:tempAmountDisbursedMin',
                ],

                'tempTotalAmountMin' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                    'lte:tempTotalAmountMax',
                ],
                'tempTotalAmountMax' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                    'gte:tempTotalAmountMin',
                ],

                'tempRateAmountMin' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                    'lte:tempRateAmountMax',
                ],
                'tempRateAmountMax' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                    'gte:tempRateAmountMin',
                ],

                'tempTanMin' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:10000',
                    'lte:tempTanMax',
                ],
                'tempTanMax' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:10000',
                    'gte:tempTanMin',
                ],

                'tempTaegMin' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:10000',
                    'lte:tempTaegMax',
                ],
                'tempTaegMax' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:10000',
                    'gte:tempTaegMin',
                ],
            ];
        }

        protected function messages(): array
        {
            return [
                'tempSelectedTeamMemberForFilter.exists' => 'Il collaboratore selezionato non esiste.',
                'tempSelectedLeadStatusForFilter.enum' => 'Lo stato lead selezionato non è valido.',
                'tempSelectedLeadSourceForFilter.enum' => 'La provenienza selezionata non è valida.',
                'tempSelectedCustomerTypeForFilter.exists' => 'La tipologia cliente selezionata non esiste.',

                'tempCreatedAtDateMin.before_or_equal' => 'La data creazione minima deve essere prima o uguale alla data massima.',
                'tempCreatedAtDateMax.after_or_equal' => 'La data creazione massima deve essere dopo o uguale alla data minima.',

                'tempUpdatedAtDateMin.before_or_equal' => 'La data ultimo contatto minima deve essere prima o uguale alla data massima.',
                'tempUpdatedAtDateMax.after_or_equal' => 'La data ultimo contatto massima deve essere dopo o uguale alla data minima.',

                'tempRecontactDateMin.before_or_equal' => 'La data ricontatto minima deve essere prima o uguale alla data massima.',
                'tempRecontactDateMax.after_or_equal' => 'La data ricontatto massima deve essere dopo o uguale alla data minima.',
                'tempSelectedProductTypeForFilter.exists' =>
                    'Il prodotto selezionato non esiste.',

                'tempSelectedProductSubtypeForFilter.exists' =>
                    'Il tipo prodotto selezionato non esiste.',

                'tempSelectedInsuranceForFilter.exists' =>
                    'L\'assicurazione selezionata non esiste.',

                'tempSelectedInstallmentForFilter.exists' =>
                    'Il numero di rate selezionato non esiste.',

                'tempFirstInstallmentDateMin.before_or_equal' =>
                    'La data iniziale minima deve essere prima o uguale alla data massima.',

                'tempFirstInstallmentDateMax.after_or_equal' =>
                    'La data iniziale massima deve essere dopo o uguale alla data minima.',

                'tempLastInstallmentDateMin.before_or_equal' =>
                    'La data finale minima deve essere prima o uguale alla data massima.',

                'tempLastInstallmentDateMax.after_or_equal' =>
                    'La data finale massima deve essere dopo o uguale alla data minima.',

                'tempAmountDisbursedMin.lte' =>
                    'L\'importo minimo deve essere minore o uguale all\'importo massimo.',

                'tempAmountDisbursedMax.gte' =>
                    'L\'importo massimo deve essere maggiore o uguale all\'importo minimo.',

                'tempTotalAmountMin.lte' =>
                    'Il totale dovuto minimo deve essere minore o uguale al massimo.',

                'tempTotalAmountMax.gte' =>
                    'Il totale dovuto massimo deve essere maggiore o uguale al minimo.',

                'tempRateAmountMin.lte' =>
                    'La rata minima deve essere minore o uguale alla rata massima.',

                'tempRateAmountMax.gte' =>
                    'La rata massima deve essere maggiore o uguale alla rata minima.',

                'tempTanMin.lte' =>
                    'Il TAN minimo deve essere minore o uguale al TAN massimo.',

                'tempTanMax.gte' =>
                    'Il TAN massimo deve essere maggiore o uguale al TAN minimo.',

                'tempTaegMin.lte' =>
                    'Il TAEG minimo deve essere minore o uguale al TAEG massimo.',

                'tempTaegMax.gte' =>
                    'Il TAEG massimo deve essere maggiore o uguale al TAEG minimo.',
            ];
        }

        protected function validationAttributes(): array
        {
            return [
                'tempSelectedTeamMemberForFilter' => 'collaboratore',
                'tempSelectedLeadStatusForFilter' => 'stato lead',
                'tempSelectedLeadSourceForFilter' => 'provenienza',
                'tempSelectedCustomerTypeForFilter' => 'tipologia cliente',

                'tempCreatedAtDateMin' => 'data creazione minima',
                'tempCreatedAtDateMax' => 'data creazione massima',
                'tempUpdatedAtDateMin' => 'data ultimo contatto minima',
                'tempUpdatedAtDateMax' => 'data ultimo contatto massima',
                'tempRecontactDateMin' => 'data ricontatto minima',
                'tempRecontactDateMax' => 'data ricontatto massima',
                'tempSelectedProductTypeForFilter' => 'prodotto',
                'tempSelectedProductSubtypeForFilter' => 'tipo prodotto',
                'tempSelectedInsuranceForFilter' => 'assicurazione',
                'tempSelectedInstallmentForFilter' => 'numero rate',

                'tempFirstInstallmentDateMin' => 'data iniziale minima',
                'tempFirstInstallmentDateMax' => 'data iniziale massima',

                'tempLastInstallmentDateMin' => 'data finale minima',
                'tempLastInstallmentDateMax' => 'data finale massima',

                'tempAmountDisbursedMin' => 'importo minimo',
                'tempAmountDisbursedMax' => 'importo massimo',

                'tempTotalAmountMin' => 'totale dovuto minimo',
                'tempTotalAmountMax' => 'totale dovuto massimo',

                'tempRateAmountMin' => 'rata minima',
                'tempRateAmountMax' => 'rata massima',

                'tempTanMin' => 'TAN minimo',
                'tempTanMax' => 'TAN massimo',

                'tempTaegMin' => 'TAEG minimo',
                'tempTaegMax' => 'TAEG massimo',
            ];
        }

    /**
     * Open the import modal
     */
    public function openImportModal(): void
    {
        $this->reset(['temporaryImportFile', 'importFile', 'userId', 'userSearch']);
        $this->dispatch('open-modal', 'import-leads-modal');
    }

    /**
     * Handle the file upload and import.
     */
    public function updatedTemporaryImportFile()
    {
        if ($this->temporaryImportFile) {
            $this->validate([
                'temporaryImportFile' => ['nullable', 'file', 'mimetypes:' . implode(',', $this->acceptedFileTypesArray()), 'max:20480']
            ], [
                'temporaryImportFile.file' => 'File non valido.',
                'temporaryImportFile.max' => 'Ogni file non può superare i 20MB.',
                'temporaryImportFile.mimetypes' => 'Formato file non valido.',
            ]);

            $this->importFile = $this->temporaryImportFile;
        }
    }

    /**
     * Remove the uploaded import file.
     */
    public function removeImportFile()
    {
        $this->importFile = null;
        $this->temporaryImportFile = null;
    }

    /**
     * Set user for import
     */
    public function setUserForImport(?int $value = null): void
    {
        $this->setSelectValue('userId', $value);
    }

    public function setProductType(?int $value = null): void
    {
        $this->setSelectValue(
            'tempSelectedProductTypeForFilter',
            $value,
            reset: false
        );
    }

    public function setProductSubtype(?int $value = null): void
    {
        $this->setSelectValue(
            'tempSelectedProductSubtypeForFilter',
            $value,
            reset: false
        );
    }

    public function setInsurance(?int $value = null): void
    {
        $this->setSelectValue(
            'tempSelectedInsuranceForFilter',
            $value,
            reset: false
        );
    }


    public function setInstallment(?int $value = null): void
    {
        $this->setSelectValue(
            'tempSelectedInstallmentForFilter',
            $value,
            reset: false
        );
    }
    /**
     * Import the leads from the uploaded file.
     */
    public function importLeads()
    {
        Gate::authorize('importLead', Customer::class);

        try {
            $this->validate([
                'importFile' => ['required', 'file', 'mimes:xlsx,xls'],
                'userId' => ['nullable', 'integer', 'exists:users,id'],
            ], [
                'importFile.required' => 'Devi selezionare un file da importare.',
                'importFile.file' => 'File non valido.',
                'importFile.mimes' => 'Il file deve essere un file Excel valido (.xlsx, .xls).',
                'userId.exists' => 'L\'utente selezionato non esiste.',
            ]);

            // Ottieni l'utente di default se è stato selezionato
            $defaultUser = $this->userId
            ? User::find($this->userId)
            : Auth::user();

            $import = new LeadsImport($defaultUser);

            // Prepara la lista degli utenti da notificare
            $users = User::role('superadmin')->get()
                ->push(auth()->user())
                ->unique('id')
                ->values();

            Excel::queueImport($import, $this->importFile)
                ->chain([
                    function () use ($import, $users) {
                        // Invio notifica
                        Notification::send($users, new ImportExcelCompleted('leads'));
                    }
                ]);

            Toaster::success('Import avviato! Riceverai una notifica al termine.');
        } catch (Exception $e) {
            Toaster::error('Errore durante la validazione del file. Assicurati che sia un file Excel valido (.xlsx, .xls).');
        }

        $this->reset(['temporaryImportFile', 'importFile', 'userId']);
        $this->dispatch('close-modal', 'import-leads-modal');
    }

    /**
     * Ensure that at least one lead is selected.
     */
    private function ensureSelectedLeads(): bool
    {
        if (empty($this->selected)) {
            Toaster::error("Seleziona almeno un profilo per procedere con l'esportazione.");
            return false;
        }

        return true;
    }

    /**
     * Export leads based on selected IDs
     */
    public function exportSelectedLeads()
    {
        Gate::authorize('exportLead', Customer::class);

        if (!$this->ensureSelectedLeads()) {
            return;
        }

        try {
            $query = Customer::whereIn('id', $this->selected);

            return Excel::download(
                new LeadsExport($query),
                'leads_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
            );
        } catch (Exception $e) {
            Log::error('Errore durante l\'export lead: ' . $e->getMessage(), [
                'selected_leads' => $this->selected,
                'user_id' => auth()->id(),
            ]);

            Toaster::error('Errore durante l\'esportazione dei profili. Riprova più tardi.');
            return;
        }
    }

    /**
     * Set lead status for the selected lead.
     */
    public function setLeadStatus(?string $value = null): void
    {
        $this->setSelectValue('selectedLeadStatus', $value, reset: false);
    }
    public function setTeamMember(?int $value = null): void
{
    $this->setSelectValue('tempSelectedTeamMemberForFilter', $value, reset: false);
}

public function setLeadStatusForFilter(?string $value = null): void
{
    $this->setSelectValue('tempSelectedLeadStatusForFilter', $value, reset: false);
}

public function setLeadSourceForFilter(?string $value = null): void
{
    $this->setSelectValue('tempSelectedLeadSourceForFilter', $value, reset: false);
}

public function setCustomerType(?int $value = null): void
{
    $this->setSelectValue('tempSelectedCustomerTypeForFilter', $value, reset: false);
}

    /**
     * This method is called when the user clicks the update status button.
     * It sets the selected lead and opens the modal for updating the status.
     */
    private function leadStatusesWithRecontactDate(): array
    {
        return [
            LeadStatus::NOT_FEASIBLE->value,
            LeadStatus::NOT_INTERESTED->value,
        ];
    }
    /**
     * Check if the selected status should show the recontact date field.
     */
    public function selectedLeadStatusShowsRecontactDate(): bool
    {
        return filled($this->selectedLeadStatus);
    }

/**
 * This method is called when the user clicks the update status button.
 * It sets the selected lead and opens the modal for updating the status.
 */
    public function selectLeadForStatus(int $id)
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Customer::class,
            property: 'selectedLead',
            modalName: 'update-lead-status',
            notFoundMessage: 'Profilo non trovata'
        );
        $this->setLeadStatus($this->selectedLead->lead_status?->value);
        $this->selectedLeadRecontactDate = $this->selectedLead->recontact_date?->format('Y-m-d');
    }

    /**
     * This method is called when the user clicks the notes button.
     * It sets the selected lead to shows notes.
     */
    public function selectLeadForNotes(int $id): void
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Customer::class,
            property: 'selectedLead',
            modalName: 'lead-notes',
            notFoundMessage: 'Note non trovate'
        );
    }

    /**
     * This method is called when the user clicks the update button in the modal.
     * It updates the lead status and resets the selected lead to null.
     */
    public function updateLeadStatus(): void
    {
        Gate::authorize('updateLeadStatus', $this->selectedLead);

        $showsRecontactDate = $this->selectedLeadStatusShowsRecontactDate();

        $this->validate([
            'selectedLeadStatus' => ['required'],
            'selectedLeadRecontactDate' => ['nullable', 'date'],
        ], [
            'selectedLeadRecontactDate.date' => 'La data ricontatto non è valida.',
        ]);

        try {
            $this->selectedLead->update([
                'lead_status' => $this->selectedLeadStatus,
                'recontact_date' => $showsRecontactDate
                    ? ($this->selectedLeadRecontactDate ?: null)
                    : $this->selectedLead->recontact_date?->format('Y-m-d'),
            ]);

            Toaster::success('Stato profilo aggiornato con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante l\'aggiornamento dello stato del profilo: ' . $e->getMessage());
        }

        $this->selectedLead = null;
        $this->selectedLeadRecontactDate = null;

        $this->resetPage();
        $this->dispatch('close-modal', 'update-lead-status');
    }

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected lead to be deleted.
     */
    public function selectLeadForDelete(int $id)
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Customer::class,
            property: 'selectedLead',
            modalName: 'delete-lead',
            notFoundMessage: 'Profilo non trovato'
        );
    }

    /**
     * This method is called when the user clicks the delete button in the modal.
     * It deletes the selected lead and resets the selected lead to null.
     */
    public function deleteLead()
    {
        Gate::authorize('delete', $this->selectedLead);

        $this->deleteSelectedEntity(
            property: 'selectedLead',
            modalName: 'delete-lead',
            successMessage: 'Lead eliminato con successo',
        );
    }

    /**
     * Updated search bar callback function
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    public function openFilterModal(): void
    {
        $this->syncTempFiltersFromAppliedFilters();

        $this->resetValidation();
        $this->dispatch('open-modal', 'filter-modal');
    }

    private function syncTempFiltersFromAppliedFilters(): void
    {
        $this->tempSelectedTeamMemberForFilter = $this->selectedTeamMemberForFilter;
        $this->tempSelectedLeadStatusForFilter = $this->selectedLeadStatusForFilter;
        $this->tempSelectedLeadSourceForFilter = $this->selectedLeadSourceForFilter;
        $this->tempSelectedCustomerTypeForFilter = $this->selectedCustomerTypeForFilter;

        $this->tempCreatedAtDateMin = $this->createdAtDateMin;
        $this->tempCreatedAtDateMax = $this->createdAtDateMax;

        $this->tempUpdatedAtDateMin = $this->updatedAtDateMin;
        $this->tempUpdatedAtDateMax = $this->updatedAtDateMax;

        $this->tempRecontactDateMin = $this->recontactDateMin;
        $this->tempRecontactDateMax = $this->recontactDateMax;
        $this->tempSelectedProductTypeForFilter =
            $this->selectedProductTypeForFilter;

        $this->tempSelectedProductSubtypeForFilter =
            $this->selectedProductSubtypeForFilter;

        $this->tempSelectedInsuranceForFilter =
            $this->selectedInsuranceForFilter;

        $this->tempSelectedInstallmentForFilter =
            $this->selectedInstallmentForFilter;

        $this->tempFirstInstallmentDateMin =
            $this->firstInstallmentDateMin;

        $this->tempFirstInstallmentDateMax =
            $this->firstInstallmentDateMax;

        $this->tempLastInstallmentDateMin =
            $this->lastInstallmentDateMin;

        $this->tempLastInstallmentDateMax =
            $this->lastInstallmentDateMax;

        $this->tempAmountDisbursedMin =
            $this->amountDisbursedMin;

        $this->tempAmountDisbursedMax =
            $this->amountDisbursedMax;

        $this->tempTotalAmountMin =
            $this->totalAmountMin;

        $this->tempTotalAmountMax =
            $this->totalAmountMax;

        $this->tempRateAmountMin =
            $this->rateAmountMin;

        $this->tempRateAmountMax =
            $this->rateAmountMax;

        $this->tempTanMin = $this->tanMin;
        $this->tempTanMax = $this->tanMax;

        $this->tempTaegMin = $this->taegMin;
        $this->tempTaegMax = $this->taegMax;
    }

    public function filter(): void
    {
        $this->validate();

        $this->selectedTeamMemberForFilter = $this->tempSelectedTeamMemberForFilter;
        $this->selectedLeadStatusForFilter = $this->tempSelectedLeadStatusForFilter;
        $this->selectedLeadSourceForFilter = $this->tempSelectedLeadSourceForFilter;
        $this->selectedCustomerTypeForFilter = $this->tempSelectedCustomerTypeForFilter;

        $this->createdAtDateMin = $this->tempCreatedAtDateMin;
        $this->createdAtDateMax = $this->tempCreatedAtDateMax;

        $this->updatedAtDateMin = $this->tempUpdatedAtDateMin;
        $this->updatedAtDateMax = $this->tempUpdatedAtDateMax;

        $this->recontactDateMin = $this->tempRecontactDateMin;
        $this->recontactDateMax = $this->tempRecontactDateMax;

        $this->selectedProductTypeForFilter =
            $this->tempSelectedProductTypeForFilter;

        $this->selectedProductSubtypeForFilter =
            $this->tempSelectedProductSubtypeForFilter;

        $this->selectedInsuranceForFilter =
            $this->tempSelectedInsuranceForFilter;

        $this->selectedInstallmentForFilter =
            $this->tempSelectedInstallmentForFilter;

        $this->firstInstallmentDateMin =
            $this->tempFirstInstallmentDateMin;

        $this->firstInstallmentDateMax =
            $this->tempFirstInstallmentDateMax;

        $this->lastInstallmentDateMin =
            $this->tempLastInstallmentDateMin;

        $this->lastInstallmentDateMax =
            $this->tempLastInstallmentDateMax;

        $this->amountDisbursedMin =
            $this->tempAmountDisbursedMin;

        $this->amountDisbursedMax =
            $this->tempAmountDisbursedMax;

        $this->totalAmountMin =
            $this->tempTotalAmountMin;

        $this->totalAmountMax =
            $this->tempTotalAmountMax;

        $this->rateAmountMin =
            $this->tempRateAmountMin;

        $this->rateAmountMax =
            $this->tempRateAmountMax;

        $this->tanMin = $this->tempTanMin;
        $this->tanMax = $this->tempTanMax;

        $this->taegMin = $this->tempTaegMin;
        $this->taegMax = $this->tempTaegMax;

        $this->resetPage();
        $this->dispatch('close-modal', 'filter-modal');
    }

    public function resetFilter(): void
    {
        $this->reset([
            'selectedTeamMemberForFilter',
            'tempSelectedTeamMemberForFilter',

            'selectedLeadStatusForFilter',
            'tempSelectedLeadStatusForFilter',

            'selectedLeadSourceForFilter',
            'tempSelectedLeadSourceForFilter',

            'selectedCustomerTypeForFilter',
            'tempSelectedCustomerTypeForFilter',

            'createdAtDateMin',
            'tempCreatedAtDateMin',
            'createdAtDateMax',
            'tempCreatedAtDateMax',

            'updatedAtDateMin',
            'tempUpdatedAtDateMin',
            'updatedAtDateMax',
            'tempUpdatedAtDateMax',

            'recontactDateMin',
            'tempRecontactDateMin',
            'recontactDateMax',
            'tempRecontactDateMax',
            'selectedProductTypeForFilter',
            'tempSelectedProductTypeForFilter',

            'selectedProductSubtypeForFilter',
            'tempSelectedProductSubtypeForFilter',

            'selectedInsuranceForFilter',
            'tempSelectedInsuranceForFilter',

            'selectedInstallmentForFilter',
            'tempSelectedInstallmentForFilter',

            'firstInstallmentDateMin',
            'tempFirstInstallmentDateMin',
            'firstInstallmentDateMax',
            'tempFirstInstallmentDateMax',

            'lastInstallmentDateMin',
            'tempLastInstallmentDateMin',
            'lastInstallmentDateMax',
            'tempLastInstallmentDateMax',

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
    private function hasPracticeOpportunityFilters(): bool
{
    return collect([
        $this->selectedProductTypeForFilter,
        $this->selectedProductSubtypeForFilter,
        $this->selectedInsuranceForFilter,
        $this->selectedInstallmentForFilter,

        $this->firstInstallmentDateMin,
        $this->firstInstallmentDateMax,
        $this->lastInstallmentDateMin,
        $this->lastInstallmentDateMax,

        $this->amountDisbursedMin,
        $this->amountDisbursedMax,
        $this->totalAmountMin,
        $this->totalAmountMax,
        $this->rateAmountMin,
        $this->rateAmountMax,

        $this->tanMin,
        $this->tanMax,
        $this->taegMin,
        $this->taegMax,
    ])->contains(fn ($value) => filled($value));
}

private function applyPracticeOpportunityFilters(
    Builder $query
): void {
    if ($this->selectedProductTypeForFilter !== null) {
        $query->where(
            'product_type_id',
            $this->selectedProductTypeForFilter
        );
    }

    if ($this->selectedProductSubtypeForFilter !== null) {
        $query->where(
            'product_subtype_id',
            $this->selectedProductSubtypeForFilter
        );
    }

    if ($this->selectedInsuranceForFilter !== null) {
        $query->where(
            'insurance_id',
            $this->selectedInsuranceForFilter
        );
    }

    if ($this->selectedInstallmentForFilter !== null) {
        $query->where(
            'installment_id',
            $this->selectedInstallmentForFilter
        );
    }

    if (filled($this->firstInstallmentDateMin)) {
        $query->whereDate(
            'first_installment_date',
            '>=',
            $this->firstInstallmentDateMin
        );
    }

    if (filled($this->firstInstallmentDateMax)) {
        $query->whereDate(
            'first_installment_date',
            '<=',
            $this->firstInstallmentDateMax
        );
    }

    if (filled($this->lastInstallmentDateMin)) {
        $query->whereDate(
            'last_installment_date',
            '>=',
            $this->lastInstallmentDateMin
        );
    }

    if (filled($this->lastInstallmentDateMax)) {
        $query->whereDate(
            'last_installment_date',
            '<=',
            $this->lastInstallmentDateMax
        );
    }

    if ($this->amountDisbursedMin !== null) {
        $query->where(
            'amount_disbursed',
            '>=',
            $this->amountDisbursedMin
        );
    }

    if ($this->amountDisbursedMax !== null) {
        $query->where(
            'amount_disbursed',
            '<=',
            $this->amountDisbursedMax
        );
    }

    if ($this->totalAmountMin !== null) {
        $query->where(
            'total_amount',
            '>=',
            $this->totalAmountMin
        );
    }

    if ($this->totalAmountMax !== null) {
        $query->where(
            'total_amount',
            '<=',
            $this->totalAmountMax
        );
    }

    if ($this->rateAmountMin !== null) {
        $query->where(
            'rate_amount',
            '>=',
            $this->rateAmountMin
        );
    }

    if ($this->rateAmountMax !== null) {
        $query->where(
            'rate_amount',
            '<=',
            $this->rateAmountMax
        );
    }

    if ($this->tanMin !== null) {
        $query->where('tan', '>=', $this->tanMin);
    }

    if ($this->tanMax !== null) {
        $query->where('tan', '<=', $this->tanMax);
    }

    if ($this->taegMin !== null) {
        $query->where('taeg', '>=', $this->taegMin);
    }

    if ($this->taegMax !== null) {
        $query->where('taeg', '<=', $this->taegMax);
    }
}

    public function applyFilters($query)
    {
        if ($this->selectedTeamMemberForFilter) {
            $query->where('user_id', $this->selectedTeamMemberForFilter);
        }

        if ($this->selectedLeadStatusForFilter) {
            $query->where('lead_status', $this->selectedLeadStatusForFilter);
        }

        if ($this->selectedLeadSourceForFilter) {
            $query->where('lead_source', $this->selectedLeadSourceForFilter);
        }

        if ($this->selectedCustomerTypeForFilter) {
            $query->where('customer_type_id', $this->selectedCustomerTypeForFilter);
        }

        if ($this->createdAtDateMin) {
            $query->whereDate('created_at', '>=', $this->createdAtDateMin);
        }

        if ($this->createdAtDateMax) {
            $query->whereDate('created_at', '<=', $this->createdAtDateMax);
        }

        if ($this->updatedAtDateMin) {
            $query->whereDate('updated_at', '>=', $this->updatedAtDateMin);
        }

        if ($this->updatedAtDateMax) {
            $query->whereDate('updated_at', '<=', $this->updatedAtDateMax);
        }

        if ($this->recontactDateMin) {
            $query->whereDate('recontact_date', '>=', $this->recontactDateMin);
        }

        if ($this->recontactDateMax) {
            $query->whereDate('recontact_date', '<=', $this->recontactDateMax);
        }
        if ($this->hasPracticeOpportunityFilters()) {
            $query->whereHas(
                'practiceOpportunities',
                function (Builder $opportunityQuery): void {
                    $this->applyPracticeOpportunityFilters(
                        $opportunityQuery
                    );
                }
            );
        }

        return $query;
    }

    #[Computed]
    public function query()
    {
        $query = Customer::with('user', 'customerType')
        ->leads()
        ->filteredForDepartment()
        ->filterBySearch($this->search);

        $query = $this->applyFilters($query);

        return $query->orderByDesc('updated_at');
    }

    #[Computed]
    public function rows()
    {
        return $this->query()
            ->paginate(15);
    }

    public function mount()
    {
        Gate::authorize('viewAny', [Customer::class, CustomerStatus::LEAD]);

        $this->leadStatuses = $this->getEnumOptions(LeadStatus::class);
        $this->leadSourcesForFilter = $this->getEnumOptions(LeadSource::class);

        $this->customerTypes = CustomerType::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
            $this->productTypes = ProductType::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->productSubtypes = ProductSubtype::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->insurances = Insurance::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->installments = Installment::orderBy('value')
            ->pluck('value', 'id')
            ->toArray();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $users = User::assignableUsers()
            ->filterBySearch($this->userSearch)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

            $teamMembers = User::assignableUsers()
            ->filterBySearch($this->teamMemberSearch)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        return view('livewire.admin.lead.lead-index', [
            'users' => $users,
            'teamMembers' => $teamMembers,
        ]);
    }
}