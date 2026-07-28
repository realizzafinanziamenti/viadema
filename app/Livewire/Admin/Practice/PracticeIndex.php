<?php

namespace App\Livewire\Admin\Practice;

use App\Enums\PracticeOrderBy;
use App\Enums\PracticeStatus;
use App\Exports\PracticesExport;
use App\Imports\PracticesImport;
use App\Livewire\Layout\NotificationButton;
use App\Livewire\Layout\NotificationModal;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\FinancialTable;
use App\Models\Installment;
use App\Models\Insurance;
use App\Models\Practice;
use App\Models\ProductSubtype;
use App\Models\ProductType;
use App\Models\User;
use App\Notifications\PracticeStatusChanged;
use App\Rules\ExceptEnumValues;
use App\Traits\EnumHelper;
use App\Traits\HandlesEntityActions;
use App\Traits\InteractsWithDropdowns;
use App\Traits\WithBulkSelection;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Masmerise\Toaster\Toaster;
use App\Enums\ImportReportRowStatus;
use App\Enums\ImportReportType;
use App\Jobs\FinalizeImportReport;
use App\Models\ImportReport;
use App\Models\ImportReportRow;
use App\Services\Imports\ImportReportService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Throwable;
use App\Traits\AcceptedFileTypes;
class PracticeIndex extends Component
{
    use WithPagination;
    use WithoutUrlPagination;
    use HandlesEntityActions;
    use EnumHelper;
    use InteractsWithDropdowns;
    use WithFileUploads;
    use WithBulkSelection;
    use AcceptedFileTypes;

    public ?ProductType $type = null;
    public ?bool $expired = false;
    public ?Practice $selectedPractice = null;
    public array $practiceStatuses = [];
    public ?string $selectedPracticeStatus = null;
    public ?string $disbursementDate = null;
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
    public array $practiceStatusesForFilter = [];
    public ?string $selectedPracticeStatusForFilter = null;
    public ?string $tempSelectedPracticeStatusForFilter = null;
    // Inserted At Date filters
    public ?string $insertedAtDateMin = null;
    public ?string $tempInsertedAtDateMin = null;
    public ?string $insertedAtDateMax = null;
    public ?string $tempInsertedAtDateMax = null;
    // First Installment Date filters
    public ?string $firstInstallmentDateMin = null;
    public ?string $tempFirstInstallmentDateMin = null;
    public ?string $firstInstallmentDateMax = null;
    public ?string $tempFirstInstallmentDateMax = null;
    // Last Installment Date filters
    public ?string $lastInstallmentDateMin = null;
    public ?string $tempLastInstallmentDateMin = null;
    public ?string $lastInstallmentDateMax = null;
    public ?string $tempLastInstallmentDateMax = null;
    // Renewability Date filters
    public ?string $renewabilityDateMin = null;
    public ?string $tempRenewabilityDateMin = null;
    public ?string $renewabilityDateMax = null;
    public ?string $tempRenewabilityDateMax = null;
    // Disbursement Date filters
    public ?string $disbursementDateMin = null;
    public ?string $tempDisbursementDateMin = null;
    public ?string $disbursementDateMax = null;
    public ?string $tempDisbursementDateMax = null;
    // Amount Disbursed filters
    public ?float $amountDisbursedMin = null;
    public ?float $tempAmountDisbursedMin = null;
    public ?float $amountDisbursedMax = null;
    public ?float $tempAmountDisbursedMax = null;
    // Total Amount filters
    public ?float $totalAmountMin = null;
    public ?float $tempTotalAmountMin = null;
    public ?float $totalAmountMax = null;
    public ?float $tempTotalAmountMax = null;
    // Rate Amount filters
    public ?float $rateAmountMin = null;
    public ?float $tempRateAmountMin = null;
    public ?float $rateAmountMax = null;
    public ?float $tempRateAmountMax = null;
    // Tan Min filters
    public ?float $tanMin = null;
    public ?float $tempTanMin = null;
    public ?float $tanMax = null;
    public ?float $tempTanMax = null;
    // Taeg Min filters
    public ?float $taegMin = null;
    public ?float $tempTaegMin = null;
    public ?float $taegMax = null;
    public ?float $tempTaegMax = null;
    // Order by select
    public array $orderBySelect = [];
    public PracticeOrderBy $selectedOrderBy = PracticeOrderBy::UPDATED_AT_DESC;
    // Import report UI state
    public ?int $activeImportReportId = null;
    public ?int $selectedImportReportId = null;
    public string $importReportFilter = 'all';
    public bool $pollImportReport = false;
    // Import file properties
    public ?TemporaryUploadedFile $temporaryImportFile = null;
    public ?TemporaryUploadedFile $importFile = null;
    public ?int $userId = null;   // user for assigning imported practices
    public string $userSearch = '';

    protected function rules(): array
    {
        return [
            'tempSelectedTeamMemberForFilter' => ['nullable', 'integer', 'exists:users,id'],
            'tempSelectedCustomerForFilter' => ['nullable', 'integer', 'exists:customers,id'],
            'tempSelectedProductTypeForFilter' => ['nullable', 'integer', 'exists:product_types,id'],
            'tempSelectedProductSubtypeForFilter' => ['nullable', 'integer', 'exists:product_subtypes,id'],
            'tempSelectedFinancialTableForFilter' => ['nullable', 'integer', 'exists:financial_tables,id'],
            'tempSelectedInsuranceForFilter' => ['nullable', 'integer', 'exists:insurances,id'],
            'tempSelectedInstallmentForFilter' => ['nullable', 'integer', 'exists:installments,id'],
            'tempSelectedCustomerTypeForFilter' => ['nullable', 'integer', 'exists:customer_types,id'],
            'tempSelectedPracticeStatusForFilter' => ['nullable', 'string', new ExceptEnumValues(PracticeStatus::class, [PracticeStatus::DISBURSED->value])],
            'tempInsertedAtDateMin' => ['nullable', 'date', 'before_or_equal:tempInsertedAtDateMax'],
            'tempInsertedAtDateMax' => ['nullable', 'date', 'after_or_equal:tempInsertedAtDateMin'],
            'tempFirstInstallmentDateMin' => ['nullable', 'date', 'before_or_equal:tempFirstInstallmentDateMax'],
            'tempFirstInstallmentDateMax' => ['nullable', 'date', 'after_or_equal:tempFirstInstallmentDateMin'],
            'tempLastInstallmentDateMin' => ['nullable', 'date', 'before_or_equal:tempLastInstallmentDateMax'],
            'tempLastInstallmentDateMax' => ['nullable', 'date', 'after_or_equal:tempLastInstallmentDateMin'],
            'tempRenewabilityDateMin' => ['nullable', 'date', 'before_or_equal:tempRenewabilityDateMax'],
            'tempRenewabilityDateMax' => ['nullable', 'date', 'after_or_equal:tempRenewabilityDateMin'],
            'tempDisbursementDateMin' => ['nullable', 'date', 'before_or_equal:tempDisbursementDateMax'],
            'tempDisbursementDateMax' => ['nullable', 'date', 'after_or_equal:tempDisbursementDateMin'],
            'tempAmountDisbursedMin' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'lte:tempAmountDisbursedMax'],
            'tempAmountDisbursedMax' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:tempAmountDisbursedMin'],
            'tempTotalAmountMin' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'lte:tempTotalAmountMax'],
            'tempTotalAmountMax' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:tempTotalAmountMin'],
            'tempRateAmountMin' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'lte:tempRateAmountMax'],
            'tempRateAmountMax' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:tempRateAmountMin'],
            'tempTanMin' => ['nullable', 'numeric', 'min:0', 'max:10000', 'lte:tempTanMax'],
            'tempTanMax' => ['nullable', 'numeric', 'min:0', 'max:10000', 'gte:tempTanMin'],
            'tempTaegMin' => ['nullable', 'numeric', 'min:0', 'max:10000', 'lte:tempTaegMax'],
            'tempTaegMax' => ['nullable', 'numeric', 'min:0', 'max:10000', 'gte:tempTaegMin'],
        ];
    }

    protected function messages(): array
    {
        return [
            'tempSelectedTeamMemberForFilter.exists' => 'Il membro del team selezionato non esiste.',
            'tempSelectedCustomerForFilter.exists' => 'Il cliente selezionato non esiste.',
            'tempSelectedProductTypeForFilter.exists' => 'Il tipo di prodotto selezionato non esiste.',
            'tempSelectedProductSubtypeForFilter.exists' => 'Il sottotipo di prodotto selezionato non esiste.',
            'tempSelectedFinancialTableForFilter.exists' => 'La tabella finanziaria selezionata non esiste.',
            'tempSelectedInsuranceForFilter.exists' => 'L\'assicurazione selezionata non esiste.',
            'tempSelectedInstallmentForFilter.exists' => 'L\'installment selezionata non esiste.',
            'tempSelectedCustomerTypeForFilter.exists' => 'La tipologia cliente selezionata non esiste.',
            'tempSelectedPracticeStatusForFilter.enum' => 'Lo stato della pratica selezionato non è valido.',
            'tempInsertedAtDateMin.before_or_equal' => 'La data minima di inserimento deve essere prima o uguale alla data massima.',
            'tempInsertedAtDateMax.after_or_equal' => 'La data massima di inserimento deve essere dopo o uguale alla data minima.',
            'tempFirstInstallmentDateMin.before_or_equal' => 'La data minima della prima rata deve essere prima o uguale alla data massima.',
            'tempFirstInstallmentDateMax.after_or_equal' => 'La data massima della prima rata deve essere dopo o uguale alla data minima.',
            'tempLastInstallmentDateMin.before_or_equal' => 'La data minima dell\'ultima rata deve essere prima o uguale alla data massima.',
            'tempLastInstallmentDateMax.after_or_equal' => 'La data massima dell\'ultima rata deve essere dopo o uguale alla data minima.',
            'tempRenewabilityDateMin.before_or_equal' => 'La data minima di rinnovabilità deve essere prima o uguale alla data massima.',
            'tempRenewabilityDateMax.after_or_equal' => 'La data massima di rinnovabilità deve essere dopo o uguale alla data minima.',
            'tempDisbursementDateMin.before_or_equal' => 'La data minima di liquidazione deve essere prima o uguale alla data massima.',
            'tempDisbursementDateMax.after_or_equal' => 'La data massima di liquidazione deve essere dopo o uguale alla data minima.',
            'tempAmountDisbursedMin.lte' => 'L\'importo finanziato minimo deve essere minore o uguale all\'importo finanziato massimo.',
            'tempAmountDisbursedMax.gte' => 'L\'importo finanziato massimo deve essere maggiore o uguale all\'importo finanziato minimo.',
            'tempTotalAmountMin.lte' => 'Il montante minimo deve essere minore o uguale al montante massimo.',
            'tempTotalAmountMax.gte' => 'Il montante massimo deve essere maggiore o uguale al montante minimo.',
            'tempRateAmountMin.lte' => 'L\'importo rata minimo deve essere minore o uguale all\'importo rata massimo.',
            'tempRateAmountMax.gte' => 'L\'importo rata massimo deve essere maggiore o uguale all\'importo rata minimo.',
            'tempTanMin.lte' => 'Il TAN minimo deve essere minore o uguale al TAN massimo.',
            'tempTanMax.gte' => 'Il TAN massimo deve essere maggiore o uguale al TAN minimo.',
            'tempTaegMin.lte' => 'Il TAEG minimo deve essere minore o uguale al TAEG massimo.',
            'tempTaegMax.gte' => 'Il TAEG massimo deve essere maggiore o uguale al TAEG minimo.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'tempSelectedTeamMemberForFilter' => 'membro del team',
            'tempSelectedCustomerForFilter' => 'cliente',
            'tempSelectedProductTypeForFilter' => 'tipo di prodotto',
            'tempSelectedProductSubtypeForFilter' => 'sottotipo di prodotto',
            'tempSelectedFinancialTableForFilter' => 'tabella finanziaria',
            'tempSelectedInsuranceForFilter' => 'assicurazione',
            'tempSelectedInstallmentForFilter' => 'numero rate',
            'tempSelectedCustomerTypeForFilter' => 'tipologia cliente',
            'tempSelectedPracticeStatusForFilter' => 'stato pratica',
            'tempInsertedAtDateMin' => 'data inserimento minima',
            'tempInsertedAtDateMax' => 'data inserimento massima',
            'tempFirstInstallmentDateMin' => 'data prima rata minima',
            'tempFirstInstallmentDateMax' => 'data prima rata massima',
            'tempLastInstallmentDateMin' => 'data ultima rata minima',
            'tempLastInstallmentDateMax' => 'data ultima rata massima',
            'tempRenewabilityDateMin' => 'data rinnovabilità minima',
            'tempRenewabilityDateMax' => 'data rinnovabilità massima',
            'tempDisbursementDateMin' => 'data liquidazione minima',
            'tempDisbursementDateMax' => 'data liquidazione massima',
            'tempAmountDisbursedMin' => 'importo finanziato minimo',
            'tempAmountDisbursedMax' => 'importo finanziato massimo',
            'tempTotalAmountMin' => 'montante minimo',
            'tempTotalAmountMax' => 'montante massimo',
            'tempRateAmountMin' => 'importo rata minimo',
            'tempRateAmountMax' => 'importo rata massimo',
            'tempTanMin' => 'TAN minimo',
            'tempTanMax' => 'TAN massimo',
            'tempTaegMin' => 'TAEG minimo',
            'tempTaegMax' => 'TAEG massimo',
        ];
    }
/**
 * Initialize the practice import report state.
 */
public function initializeImportReportState(
    ImportReportService $reportService
): void {
    if (!Gate::allows('importPractice', Practice::class)) {
        return;
    }

    $user = Auth::user();

    if (!$user instanceof User) {
        return;
    }

    $report = $reportService->findForUserAndType(
        user: $user,
        type: ImportReportType::PRACTICES,
    );

    if ($report === null) {
        $this->activeImportReportId = null;
        $this->pollImportReport = false;

        return;
    }

    if ($report->isRunning()) {
        $this->activeImportReportId = $report->getKey();
        $this->pollImportReport = true;

        return;
    }

    $this->activeImportReportId = null;
    $this->pollImportReport = false;

    if (
        $report->isFinished()
        && $report->viewed_at === null
    ) {
        $this->showImportReport(
            report: $report,
            reportService: $reportService,
        );
    }
}

/**
 * Open the latest practice import report.
 */
public function openLatestImportReport(
    ImportReportService $reportService
): void {
    Gate::authorize('importPractice', Practice::class);

    $user = Auth::user();

    if (!$user instanceof User) {
        return;
    }

    $report = $reportService->findForUserAndType(
        user: $user,
        type: ImportReportType::PRACTICES,
    );

    if ($report === null) {
        Toaster::error(
            'Non è disponibile alcun report di importazione.'
        );

        return;
    }

    if ($report->isRunning()) {
        Toaster::error(
            'L\'importazione delle pratiche è ancora in corso.'
        );

        return;
    }

    $this->showImportReport(
        report: $report,
        reportService: $reportService,
    );
}

/**
 * Poll the active practice import.
 */
public function checkImportStatus(
    ImportReportService $reportService
): void {
    if (!Gate::allows('importPractice', Practice::class)) {
        $this->activeImportReportId = null;
        $this->pollImportReport = false;

        return;
    }

    if (
        !$this->pollImportReport
        || $this->activeImportReportId === null
    ) {
        return;
    }

    $report = ImportReport::query()
        ->whereKey($this->activeImportReportId)
        ->where('user_id', Auth::id())
        ->where(
            'type',
            ImportReportType::PRACTICES->value
        )
        ->first();

    if ($report === null) {
        $this->activeImportReportId = null;
        $this->pollImportReport = false;

        return;
    }

    if ($report->isRunning()) {
        return;
    }

    $this->activeImportReportId = null;
    $this->pollImportReport = false;

    $this->showImportReport(
        report: $report,
        reportService: $reportService,
    );
}

    /**
     * Display the selected practice import report.
     */
    private function showImportReport(
        ImportReport $report,
        ImportReportService $reportService
    ): void {
        if (
            (int) $report->user_id !== (int) Auth::id()
            || $report->type !== ImportReportType::PRACTICES
        ) {
            return;
        }

        $this->selectedImportReportId = $report->getKey();
        $this->importReportFilter = 'all';

        $this->resetPage('importReportPage');

        if (
            $report->isFinished()
            && $report->viewed_at === null
        ) {
            $reportService->markAsViewed(
                reportId: $report->getKey(),
                runUuid: $report->run_uuid,
            );
        }

        $this->dispatch(
            'open-modal',
            'practice-import-report-modal'
        );
    }

    /**
     * Filter report rows by their outcome.
     */
    public function setImportReportFilter(string $filter): void
    {
        if (!in_array(
            $filter,
            ['all', 'imported', 'failed'],
            true
        )) {
            return;
        }

        $this->importReportFilter = $filter;

        $this->resetPage('importReportPage');
    }

    /**
     * Report displayed in the modal.
     */
    #[Computed]
    public function selectedImportReport(): ?ImportReport
    {
        if ($this->selectedImportReportId === null) {
            return null;
        }

        return ImportReport::query()
            ->whereKey($this->selectedImportReportId)
            ->where('user_id', Auth::id())
            ->where(
                'type',
                ImportReportType::PRACTICES->value
            )
            ->first();
    }

    /**
     * Paginated rows of the selected report.
     */
    #[Computed]
    public function importReportRows()
    {
        $report = $this->selectedImportReport;

        if ($report === null) {
            return null;
        }

        $query = ImportReportRow::query()
            ->where(
                'import_report_id',
                $report->getKey()
            )
            ->where(
                'run_uuid',
                $report->run_uuid
            )
            ->orderBy('row_number');

        if ($this->importReportFilter === 'imported') {
            $query->where(
                'status',
                ImportReportRowStatus::IMPORTED->value
            );
        }

        if ($this->importReportFilter === 'failed') {
            $query->where(
                'status',
                ImportReportRowStatus::FAILED->value
            );
        }

        return $query->paginate(
            perPage: 10,
            pageName: 'importReportPage',
        );
    }

    /**
     * Latest practice import report of the current user.
     */
    #[Computed]
    public function latestPracticeImportReport(): ?ImportReport
    {
        return ImportReport::query()
            ->where('user_id', Auth::id())
            ->where(
                'type',
                ImportReportType::PRACTICES->value
            )
            ->first();
    }

    /**
     * Determine whether a practice import is running.
     */
    #[Computed]
    public function isPracticeImportRunning(): bool
    {
        return $this->latestPracticeImportReport?->isRunning()
            ?? false;
    }
    /**
 * Open the practice import modal.
 */
public function openImportModal(): void
{
    Gate::authorize('importPractice', Practice::class);

    if ($this->isPracticeImportRunning) {
        Toaster::error(
            'È già in corso un import di pratiche. Attendi il completamento.'
        );

        return;
    }

    $this->reset([
        'temporaryImportFile',
        'importFile',
        'userId',
        'userSearch',
    ]);

    $this->resetValidation();

    $this->dispatch(
        'open-modal',
        'import-practices-modal'
    );
}
    /**
     * Handle the file upload and import.
     */
/**
 * Validate the temporarily uploaded Excel file.
 */
public function updatedTemporaryImportFile(): void
{
    if ($this->temporaryImportFile === null) {
        return;
    }

    $this->validate([
        'temporaryImportFile' => [
            'file',
            'mimes:xlsx',
            'max:20480',
        ],
    ], [
        'temporaryImportFile.file' =>
            'File non valido.',

        'temporaryImportFile.mimes' =>
            'Il file deve essere un file Excel valido (.xlsx).',

        'temporaryImportFile.max' =>
            'Il file non può superare i 20 MB.',
    ]);

    $this->importFile = $this->temporaryImportFile;
}

/**
 * Remove the uploaded import file.
 */
public function removeImportFile(): void
{
    $this->importFile = null;
    $this->temporaryImportFile = null;

    $this->resetValidation('temporaryImportFile');
    $this->resetValidation('importFile');
}

    /**
     * Set user for import
     */
    public function setUserForImport(?int $value = null): void
    {
        $this->setSelectValue('userId', $value);
    }

/**
 * Import practices from the uploaded Excel file.
 */
public function importPractices(): void
{
    Gate::authorize('importPractice', Practice::class);

    /*
     * Leave validation outside the try/catch so Livewire can
     * display field errors without closing the modal.
     */
    $this->validate([
        'importFile' => [
            'required',
            'file',
            'mimes:xlsx',
        ],
        'userId' => [
            'nullable',
            'integer',
            'exists:users,id',
        ],
    ], [
        'importFile.required' =>
            'Devi selezionare un file da importare.',

        'importFile.file' =>
            'File non valido.',

        'importFile.mimes' =>
            'Il file deve essere un file Excel valido (.xlsx).',

        'userId.exists' =>
            'L\'utente selezionato non esiste.',
    ]);

    $initiatedBy = User::query()
        ->findOrFail(Auth::id());

    /*
     * Preserve the existing practice import behaviour:
     * without an explicit selection, PracticesImport tries
     * nome_agenzia and then falls back to the initiating user.
     */
    $defaultUser = $this->userId !== null
        ? User::query()->findOrFail($this->userId)
        : null;

    $fileName = $this->importFile
        ->getClientOriginalName();

    $reportService = app(ImportReportService::class);

    /** @var ImportReport|null $report */
    $report = null;

    try {
        $report = $reportService->start(
            user: $initiatedBy,
            type: ImportReportType::PRACTICES,
            fileName: $fileName,
        );

        $import = new PracticesImport(
            defaultUser: $defaultUser,
            importReportId: $report->getKey(),
            runUuid: $report->run_uuid,
            initiatedByUserId: $initiatedBy->getKey(),
            fileName: $fileName,
        );

        Excel::queueImport(
            $import,
            $this->importFile
        )->chain([
            new FinalizeImportReport(
                importReportId: $report->getKey(),
                runUuid: $report->run_uuid,
            ),
        ]);
    } catch (DomainException $exception) {
        Toaster::error($exception->getMessage());

        return;
    } catch (Throwable $exception) {
        if ($report !== null) {
            try {
                $reportService->fail(
                    reportId: $report->getKey(),
                    runUuid: $report->run_uuid,
                    errorMessage: $exception->getMessage(),
                );
            } catch (Throwable $reportException) {
                Log::error(
                    'Unable to mark practice import report as failed.',
                    [
                        'import_report_id' =>
                            $report->getKey(),

                        'run_uuid' =>
                            $report->run_uuid,

                        'exception' =>
                            $reportException,
                    ]
                );
            }
        }

        Log::error(
            'Unable to queue practice import.',
            [
                'user_id' => $initiatedBy->getKey(),
                'file_name' => $fileName,
                'exception' => $exception,
            ]
        );

        Toaster::error(
            'Non è stato possibile avviare l\'importazione. Riprova più tardi.'
        );

        return;
    }

    $this->activeImportReportId = $report->getKey();
    $this->pollImportReport = true;

    $this->reset([
        'temporaryImportFile',
        'importFile',
        'userId',
        'userSearch',
    ]);

    $this->dispatch(
        'close-modal',
        'import-practices-modal'
    );

    Toaster::success(
        'Import avviato! Il report sarà disponibile al termine.'
    );
}

    /**
     * Ensure that at least one practice is selected.
     */
    private function ensureSelectedPractices(): bool
    {
        if (empty($this->selected)) {
            Toaster::error("Seleziona almeno un profilo per procedere con l'esportazione.");
            return false;
        }

        return true;
    }

    /**
     * Export practices based on selected IDs
     */
    public function exportSelectedPractices()
    {
        Gate::authorize('exportPractice', Practice::class);
        if (!$this->ensureSelectedPractices()) {
            return;
        }

        try {
            $query = Practice::whereIn('id', $this->selected);

            return Excel::download(
                new PracticesExport($query),
                'pratiche_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
            );
        } catch (Exception $e) {
            Log::error('Errore durante l\'export practice: ' . $e->getMessage(), [
                'selected_practices' => $this->selected,
                'user_id' => auth()->id(),
            ]);

            Toaster::error('Errore durante l\'esportazione delle pratiche. Riprova più tardi.');
            return;
        }
    }

    /**
     * Set the selected order by value.
     */
    public function setOrderBy(?string $value = null): void
    {
        $this->selectedOrderBy = PracticeOrderBy::tryFrom($value) ?? PracticeOrderBy::UPDATED_AT_DESC;
    }

    /**
     * Set team member
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
    public function setPracticeStatus(?string $value = null): void
    {
        $this->setSelectValue('selectedPracticeStatus', $value, reset: false);
    }

    /**
     * This method is called when the user selects a practice status for filtering.
     * It sets the selected practice status for filter.
     */
    public function setPracticeStatusForFilter(?string $value = null): void
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
        Gate::authorize('updateStatus', $this->selectedPractice);

        $rules = [
            'selectedPracticeStatus' => ['required', 'string', new Enum(PracticeStatus::class)],
        ];

        // If the selected practice status is DISBURSED, validate the disbursement date
        if ($this->selectedPracticeStatus === PracticeStatus::DISBURSED->value) {
            $rules['disbursementDate'] = ['required', 'date'];
        }

        $this->validate($rules);

        try {
            $updateData = ['practice_status' => $this->selectedPracticeStatus];

            // If the selected practice status is DISBURSED, set the disbursement date
            if ($this->selectedPracticeStatus === PracticeStatus::DISBURSED->value) {
                $updateData['disbursement_date'] = $this->disbursementDate ?? now();
            }

            // Store old and new status for notification
            $oldStatus = $this->selectedPractice->practice_status;
            $newStatus = PracticeStatus::from($this->selectedPracticeStatus);

            DB::transaction(function () use ($updateData, $oldStatus, $newStatus) {
                $this->selectedPractice->update($updateData);

                // Notify relevant users if the status has changed
                if ($oldStatus !== $newStatus) {
                    $this->notifyPracticeStatusChanged($this->selectedPractice, $oldStatus, $newStatus);
                }
            });

            Toaster::success('Stato della pratica aggiornato con successo');
        } catch (Exception $e) {
            Log::error('Errore durante l\'aggiornamento dello stato della pratica: ' . $e->getMessage());
            Toaster::error('Errore durante l\'aggiornamento dello stato della pratica');
        }

        $this->selectedPractice = null;
        $this->disbursementDate = now()->format('Y-m-d');
        $this->dispatch('close-modal', 'update-practice-status');
    }

    /**
     * Notify relevant users about the practice status change.
     */
    protected function notifyPracticeStatusChanged(Practice $practice, PracticeStatus $oldStatus, PracticeStatus $newStatus): void
    {
        $usersToNotify = collect([
            $practice->user,
            User::role('superadmin')->get(),
        ])
            ->flatten()
            ->unique('id')
            ->reject(fn($user) => $user->id === auth()->id());

        Notification::send(
            $usersToNotify,
            new PracticeStatusChanged(
                $practice,
                $oldStatus->getLabelText(),
                $newStatus->getLabelText()
            )
        );
    }

    /**
     * This method is called when the user clicks the notes button.
     * It sets the selected practice to shows notes.
     */
    public function selectPracticeForNotes(int $id): void
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Practice::class,
            property: 'selectedPractice',
            modalName: 'practice-notes',
            notFoundMessage: 'Note non trovate'
        );
        $this->selectedPractice?->loadMissing('opportunity');
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

        // Dispatch an event to refresh the notification modal
        $this->dispatch('refresh-notification-modal')->to(NotificationModal::class);
        $this->dispatch('refresh-notification-button')->to(NotificationButton::class);
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
        $this->practiceStatusesForFilter = PracticeStatus::labelsWithoutDisbursed();

        // Set the initial order by select options based on whether the practice is expired or not
        if ($this->expired === false) {
            $this->orderBySelect = PracticeOrderBy::options($excluded = [PracticeOrderBy::DISBURSEMENT_DATE_DESC, PracticeOrderBy::DISBURSEMENT_DATE_ASC]);
        } else {
            $this->orderBySelect = PracticeOrderBy::options($excluded = [PracticeOrderBy::RENEWABILITY_DATE_DESC, PracticeOrderBy::RENEWABILITY_DATE_ASC]);
        }

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
     * This method is called when the component is mounted.
     * It initializes the selects and sets the disbursement date if expired.
     */
    protected function setDisbursementDateIfExpired(): void
    {
        if ($this->expired === true) {
            $this->disbursementDateMin = now()->subYear()->format('Y-m-d');
            $this->tempDisbursementDateMin = $this->disbursementDateMin;
            $this->disbursementDateMax = now()->format('Y-m-d');
            $this->tempDisbursementDateMax = $this->disbursementDateMax;
        }
    }

    /**
     * This method is called when the user clicks the filter button.
     * It opens the filter modal and resets the validation errors.
     */
    public function openFilterModal(): void
    {
        $this->resetValidation();
        $this->dispatch('open-modal', 'filter-modal');
    }

    /**
     * This method is called when the user clicks the filter button.
     * It resets the page and closes the filter modal.
     */
    public function filter(): void
    {
        $this->validate();

        $this->selectedTeamMemberForFilter = $this->tempSelectedTeamMemberForFilter;
        $this->selectedCustomerForFilter = $this->tempSelectedCustomerForFilter;
        $this->selectedProductTypeForFilter = $this->tempSelectedProductTypeForFilter;
        $this->selectedProductSubtypeForFilter = $this->tempSelectedProductSubtypeForFilter;
        $this->selectedFinancialTableForFilter = $this->tempSelectedFinancialTableForFilter;
        $this->selectedInsuranceForFilter = $this->tempSelectedInsuranceForFilter;
        $this->selectedInstallmentForFilter = $this->tempSelectedInstallmentForFilter;
        $this->selectedCustomerTypeForFilter = $this->tempSelectedCustomerTypeForFilter;
        $this->selectedPracticeStatusForFilter = $this->tempSelectedPracticeStatusForFilter;
        $this->insertedAtDateMin = $this->tempInsertedAtDateMin;
        $this->insertedAtDateMax = $this->tempInsertedAtDateMax;
        $this->firstInstallmentDateMin = $this->tempFirstInstallmentDateMin;
        $this->firstInstallmentDateMax = $this->tempFirstInstallmentDateMax;
        $this->lastInstallmentDateMin = $this->tempLastInstallmentDateMin;
        $this->lastInstallmentDateMax = $this->tempLastInstallmentDateMax;
        $this->renewabilityDateMin = $this->tempRenewabilityDateMin;
        $this->renewabilityDateMax = $this->tempRenewabilityDateMax;
        $this->disbursementDateMin = $this->tempDisbursementDateMin;
        $this->disbursementDateMax = $this->tempDisbursementDateMax;
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
            'insertedAtDateMin',
            'tempInsertedAtDateMin',
            'insertedAtDateMax',
            'tempInsertedAtDateMax',
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
            'disbursementDateMin',
            'tempDisbursementDateMin',
            'disbursementDateMax',
            'tempDisbursementDateMax',
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

    /**
     * Apply filters to the query based on the selected filters.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyFilters($query)
    {
        if ($this->selectedPracticeStatusForFilter && $this->expired === false) {
            $query->where('practice_status', $this->selectedPracticeStatusForFilter);
        }

        if ($this->selectedTeamMemberForFilter) {
            $query->where('user_id', $this->selectedTeamMemberForFilter);
        }

        if ($this->selectedCustomerForFilter) {
            $query->where('customer_id', $this->selectedCustomerForFilter);
        }

        if ($this->selectedProductTypeForFilter && $this->type === null) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('product_type_id', $this->selectedProductTypeForFilter)
            );
        }

        if ($this->selectedProductSubtypeForFilter) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('product_subtype_id', $this->selectedProductSubtypeForFilter)
            );
        }

        if ($this->selectedFinancialTableForFilter) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('financial_table_id', $this->selectedFinancialTableForFilter)
            );
        }

        if ($this->selectedInsuranceForFilter) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('insurance_id', $this->selectedInsuranceForFilter)
            );
        }

        if ($this->selectedInstallmentForFilter) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('installment_id', $this->selectedInstallmentForFilter)
            );
        }

        if ($this->selectedCustomerTypeForFilter) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('customer_type_id', $this->selectedCustomerTypeForFilter)
            );
        }

        if ($this->insertedAtDateMin) {
            $query->whereDate('inserted_at', '>=', $this->insertedAtDateMin);
        }

        if ($this->insertedAtDateMax) {
            $query->whereDate('inserted_at', '<=', $this->insertedAtDateMax);
        }

        if ($this->firstInstallmentDateMin) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->whereDate('first_installment_date', '>=', $this->firstInstallmentDateMin)
            );
        }

        if ($this->firstInstallmentDateMax) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->whereDate('first_installment_date', '<=', $this->firstInstallmentDateMax)
            );
        }

        if ($this->lastInstallmentDateMin) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->whereDate('last_installment_date', '>=', $this->lastInstallmentDateMin)
            );
        }

        if ($this->lastInstallmentDateMax) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->whereDate('last_installment_date', '<=', $this->lastInstallmentDateMax)
            );
        }

        if ($this->renewabilityDateMin) {
            $query->whereDate('renewability_date', '>=', $this->renewabilityDateMin);
        }

        if ($this->renewabilityDateMax) {
            $query->whereDate('renewability_date', '<=', $this->renewabilityDateMax);
        }

        if ($this->disbursementDateMin && $this->expired === true) {
            $query->whereDate('disbursement_date', '>=', $this->disbursementDateMin);
        }

        if ($this->disbursementDateMax && $this->expired === true) {
            $query->whereDate('disbursement_date', '<=', $this->disbursementDateMax);
        }

        if ($this->amountDisbursedMin) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('amount_disbursed', '>=', $this->amountDisbursedMin)
            );
        }

        if ($this->amountDisbursedMax) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('amount_disbursed', '<=', $this->amountDisbursedMax)
            );
        }

        if ($this->totalAmountMin) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('total_amount', '>=', $this->totalAmountMin)
            );
        }

        if ($this->totalAmountMax) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('total_amount', '<=', $this->totalAmountMax)
            );
        }

        if ($this->rateAmountMin) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('rate_amount', '>=', $this->rateAmountMin)
            );
        }

        if ($this->rateAmountMax) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('rate_amount', '<=', $this->rateAmountMax)
            );
        }

        if ($this->tanMin) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('tan', '>=', $this->tanMin)
            );
        }

        if ($this->tanMax) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('tan', '<=', $this->tanMax)
            );
        }

        if ($this->taegMin) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('taeg', '>=', $this->taegMin)
            );
        }

        if ($this->taegMax) {
            $query->whereHas('opportunity', fn ($q) =>
                $q->where('taeg', '<=', $this->taegMax)
            );
        }

        return $query;
    }
    #[Computed]
    public function query()
    {
        $query = Practice::with([
            'customer',
            'user',
            'opportunity.productType',
            'opportunity.productSubtype',
            'opportunity.financialTable',
            'opportunity.insurance',
            'opportunity.installment',
            'opportunity.customerType',
        ])
            ->filteredForDepartment()
            ->filterByProductType($this->type)
            ->isExpired($this->expired)
            ->orderBy($this->selectedOrderBy->field(), $this->selectedOrderBy->direction())
            ->filterBySearch($this->search);

        $query = $this->applyFilters($query);
        return $query;
    }

    #[Computed]
    public function rows()
    {
        return $this->query()
            ->paginate(15);
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

        // Initialize the disbursement date to now
        // This is used to set the disbursement date when updating the practice status
        $this->disbursementDate = now()->format('Y-m-d');

        // Set the practice status based on the request status parameter
        // This allows the component to be used with or without a specific practice status (for dashboard links)
        if ($practiceStatus = $request->query('status')) {
            $this->setPracticeStatusForFilter($practiceStatus);
            $this->selectedPracticeStatusForFilter = $practiceStatus;
        }

        // the expired status based on the request parameter
        // This allows the component to be used with or without the expired filter
        $this->expired = $request->boolean('expired');
        $this->setDisbursementDateIfExpired();

        $this->initializeSelects();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        // Fetch team members and customers for the dropdowns
        $teamMembers = User::assignableUsers()
            ->filterBySearch($this->teamMemberSearch)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        $users = User::assignableUsers()
            ->filterBySearch($this->userSearch)
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
            'productType' => $this->type,
            'expired' => $this->expired,
            'teamMembers' => $teamMembers,
            'users' => $users,
            'customers' => $customers,
        ]);
    }
}