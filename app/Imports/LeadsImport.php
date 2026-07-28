<?php

namespace App\Imports;

use App\Enums\CustomerStatus;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Enums\ProductionType;
use App\Models\FinancialTable;
use App\Models\Installment;
use App\Models\Insurance;
use App\Models\PracticeOpportunity;
use App\Models\ProductSubtype;
use App\Models\ProductType;
use App\Services\Imports\ImportReportService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Throwable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\ImportFailed;

class LeadsImport implements ToModel, WithHeadingRow, SkipsOnFailure, ShouldQueue, WithChunkReading, WithValidation, WithEvents
{
    use RemembersRowNumber;

    protected ?int $defaultUserId;

    protected function updateOrCreatePracticeOpportunity(
        Customer $lead,
        array $row,
        LeadSource $leadSource,
        ?CustomerType $customerType = null
    ): PracticeOpportunity {
        return PracticeOpportunity::updateOrCreate(
            ['customer_id' => $lead->id],
            $this->practiceOpportunityData(
                $row,
                $leadSource,
                $customerType
            )
        );
    }

    protected function practiceOpportunityData(
        array $row,
        LeadSource $leadSource,
        ?CustomerType $customerType = null
    ): array {
        $product = $this->getProduct($row);
        $productSubtype = $this->getProductSubtype($row);
        $installment = $this->getInstallment($row);
        $insurance = $this->getInsurance($row);
        $financialTable = $this->getFinancialTable($row);

        return [
            'product_type_id' => $product?->id,
            'product_subtype_id' => $productSubtype?->id,
            'financial_table_id' => $financialTable?->id,
            'insurance_id' => $insurance?->id,
            'installment_id' => $installment?->id,
            'customer_type_id' => $customerType?->id,
            'acquisition_channel' => $leadSource->value,

            'amount_disbursed' => $this->nullableNumber(
                $row['finanziato'] ?? $row['importo'] ?? null
            ),
            'total_amount' => $this->nullableNumber(
                $row['montante'] ?? $row['totale_dovuto'] ?? null
            ),
            'rate_amount' => $this->nullableNumber(
                $row['importo_rata'] ?? $row['rata_mensile'] ?? null
            ),

            'tan' => $this->nullableNumber($row['tan'] ?? null),
            'teg' => $this->nullableNumber($row['teg'] ?? null),
            'taeg' => $this->nullableNumber($row['taeg'] ?? null),

            'first_installment_date' => $this->parseDate(
                $row['data_prima_rata']
                    ?? $row['data_inizio_finanziamento']
                    ?? $row['data_inizio']
                    ?? null
            ),
            'last_installment_date' => $this->parseDate(
                $row['data_ultima_rata']
                    ?? $row['data_fine']
                    ?? null
            ),

            'renewability_percentage' => $this->nullableNumber(
                $row['percentuale_rinnovabilita']
                    ?? $row['percentuale_rinnovabilità']
                    ?? null
            ) ?? 40.00,

            'percentage_alert' => $this->nullableNumber(
                $row['percentuale_alert'] ?? null
            ) ?? 35.00,

            'is_renewal' => $this->parseRenewalValue(
                $row['rinnovo'] ?? null
            ),
            'production_type' => $this->parseProductionType(
                $row['produzione'] ?? null
            ),

            'disbursing_institution' => $row['ente_erogante'] ?? null,
            'financial_institution' => $row['istituto_finanziario'] ?? null,
            'previous_finance' => $row['finanziaria_estinta'] ?? null,

            'notes' => $row['note_pratica'] ?? null,
        ];
    }

    protected function nullableNumber($value): ?float
{
    if ($value === null || $value === '') {
        return null;
    }

    return (float) str_replace(',', '.', (string) $value);
}

    protected function getProduct(array $row): ?ProductType
{
    $value = strtolower(trim((string) ($row['prodotto'] ?? $row['applicazione'] ?? '')));

    if ($value === '') {
        return null;
    }

    return ProductType::whereRaw('LOWER(name) = ?', [$value])
        ->orWhereRaw('LOWER(slug) = ?', [$value])
        ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $value . '%'])
        ->first();
}

    protected function getProductSubtype(array $row): ?ProductSubtype
{
    $value = strtolower(
        trim((string) ($row['tipo_prodotto'] ?? ''))
    );

    if ($value === '') {
        return null;
    }

    return ProductSubtype::query()
        ->whereRaw('LOWER(name) = ?', [$value])
        ->orWhereRaw('LOWER(name) LIKE ?', [
            '%' . $value . '%',
        ])
        ->first();
}

    protected function getInstallment(array $row): ?Installment
{
    $value = $row['numero_rate'] ?? $row['rate'] ?? null;

    if (!$value) {
        return null;
    }

    return Installment::where('value', (int) $value)->first();
}

    protected function getInsurance(array $row): ?Insurance
{
    $value = strtolower(trim((string) ($row['assicurazione'] ?? '')));

    if ($value === '') {
        return null;
    }

    return Insurance::whereRaw('LOWER(name) = ?', [$value])
        ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $value . '%'])
        ->first();
}

    protected function getFinancialTable(array $row): ?FinancialTable
{
    $value = $row['tabella_provvigionale'] ?? $row['tabella_finanziaria'] ?? null;

    if ($value === null || $value === '') {
        return null;
    }

    return FinancialTable::where('percentage', $this->nullableNumber($value))->first();
}

    protected function parseRenewalValue($value): bool
{
    if (!$value) {
        return false;
    }

    return match (strtoupper(trim((string) $value))) {
        'S', 'SI', 'SÌ', 'YES', 'Y', '1' => true,
        default => false,
    };
}

    protected function parseProductionType($value): ?string
{
    if (!$value) {
        return null;
    }

    $normalized = strtolower(trim((string) $value));

    foreach (ProductionType::cases() as $case) {
        if (
            strtolower($case->value) === $normalized ||
            strtolower($case->getLabelText()) === $normalized
        ) {
            return $case->value;
        }
    }

    return null;
}

    public function __construct(
    ?User $defaultUser,
    protected int $importReportId,
    protected string $runUuid,
    protected int $initiatedByUserId,
    protected string $fileName,
) {
    /*
     * Conserviamo soltanto gli identificativi e altri valori scalari.
     * L'istanza dell'import viene serializzata quando entra in coda.
     */
    $this->defaultUserId = $defaultUser?->getKey();
}

    /**
     * Import a single Excel row.
     */
    public function model(array $row): ?Customer
{
    $rowNumber = $this->getRowNumber();
    $label = $this->buildRowLabel($row, $rowNumber);

    try {
        /*
         * Customer and PracticeOpportunity must be persisted atomically.
         * We do not want a lead without its related opportunity when one
         * of the two operations fails.
         */
        $lead = DB::transaction(function () use ($row): Customer {
            $user = $this->getUser($row);
            $customerType = $this->getCustomerType($row);

            $leadSource = $this->parseLeadSource(
                $row['canale_acquisizione']
                    ?? $row['provenienza_lead']
                    ?? null
            );

            $leadStatus = $this->parseLeadStatus(
                $row['stato_lead'] ?? null
            );

            $firstName = trim((string) ($row['nome'] ?? ''));
            $lastName = trim((string) ($row['cognome'] ?? ''));

            $taxId = trim(
                (string) ($row['codice_fiscale'] ?? '')
            );

            $taxId = $taxId !== '' ? $taxId : null;

            $customerData = [
                'user_id' => $user->getKey(),
                'customer_type_id' => $customerType?->getKey(),

                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $this->cleanPhone(
                    $row['telefono'] ?? null
                ),
                'email' => $row['email'] ?? null,

                'date_of_birth' => $this->parseDate(
                    $row['data_nascita']
                        ?? $row['data_di_nascita']
                        ?? null
                ),

                'tax_id' => $taxId,

                'address' => $row['indirizzo'] ?? null,
                'city' => $row['citta']
                    ?? $row['città']
                    ?? null,
                'state' => $row['provincia'] ?? null,
                'postal_code' => $row['cap'] ?? null,

                'customer_status' => CustomerStatus::LEAD,
                'lead_status' => $leadStatus,

                'recontact_date' => $this->parseDate(
                    $row['data_ricontatto'] ?? null
                ),

                'notes' => $row['note'] ?? null,
            ];

            $lead = $this->findOrCreateCustomer(
                $row,
                $customerData
            );

            $this->updateOrCreatePracticeOpportunity(
                $lead,
                $row,
                $leadSource,
                $customerType
            );

            return $lead;
        }, 3);
    } catch (Throwable $exception) {
        $errors = [$exception->getMessage()];

        $this->reportService()->recordFailedRow(
            reportId: $this->importReportId,
            runUuid: $this->runUuid,
            rowNumber: $rowNumber,
            label: $label,
            message: 'Errore durante l\'importazione del lead.',
            rawData: $row,
            errors: $errors,
        );

        $this->createActivityLog(
            lead: null,
            logName: 'import_failure',
            message: "Errore durante l'importazione del lead alla riga {$rowNumber}",
            row: $row,
            exception: $exception,
            rowNumber: $rowNumber,
            validationErrors: $errors,
        );

        Log::warning(
            "Errore nell'import lead alla riga {$rowNumber}: {$exception->getMessage()}",
            [
                'import_report_id' => $this->importReportId,
                'run_uuid' => $this->runUuid,
                'row_number' => $rowNumber,
                'label' => $label,
                'exception' => $exception,
            ]
        );

        /*
         * L'errore riguarda questa riga, quindi l'import può continuare
         * con le righe successive.
         */
        return null;
    }

    $action = $lead->wasRecentlyCreated
        ? 'creato'
        : 'aggiornato';

    /*
     * Questa scrittura è idempotente grazie alla chiave:
     * report_id + run_uuid + row_number.
     */
    $this->reportService()->recordImportedRow(
        reportId: $this->importReportId,
        runUuid: $this->runUuid,
        rowNumber: $rowNumber,
        label: $lead->full_name,
        rawData: $row,
        entityType: Customer::class,
        entityId: $lead->getKey(),
        message: "Lead {$action} correttamente.",
    );

    $this->createActivityLog(
        lead: $lead,
        logName: 'import_success',
        message: "Lead {$action} correttamente",
        row: $row,
        rowNumber: $rowNumber,
    );

    return $lead;
}
/**
 * Register import lifecycle events.
 */
public function registerEvents(): array
{
    return [
        ImportFailed::class => [
            $this,
            'handleImportFailed',
        ],
    ];
}

/**
 * Mark the report as failed when the queued import crashes globally.
 */
public function handleImportFailed(ImportFailed $event): void
{
    $exception = $event->getException();

    $this->reportService()->fail(
        reportId: $this->importReportId,
        runUuid: $this->runUuid,
        errorMessage: $exception->getMessage(),
    );

    Log::error(
        'Queued lead import failed.',
        [
            'import_report_id' => $this->importReportId,
            'run_uuid' => $this->runUuid,
            'file_name' => $this->fileName,
            'exception' => $exception,
        ]
    );
}
/**
 * Register validation failures grouped by Excel row.
 *
 * @param Failure ...$failures
 */
    public function onFailure(Failure ...$failures): void
{
    $failuresByRow = collect($failures)
        ->groupBy(
            static fn (Failure $failure): int => $failure->row()
        );

    foreach ($failuresByRow as $rowNumber => $failureGroup) {
        $errors = $failureGroup
            ->flatMap(
                static fn (Failure $failure): array =>
                    $failure->errors()
            )
            ->filter()
            ->unique()
            ->values()
            ->all();

        $rowValues = $failureGroup
            ->first()
            ->values();

        $rowNumber = (int) $rowNumber;
        $label = $this->buildRowLabel(
            $rowValues,
            $rowNumber
        );

        $message = $errors !== []
            ? implode(' | ', $errors)
            : 'La riga non ha superato la validazione.';

        $this->reportService()->recordFailedRow(
            reportId: $this->importReportId,
            runUuid: $this->runUuid,
            rowNumber: $rowNumber,
            label: $label,
            message: $message,
            rawData: $rowValues,
            errors: $errors,
        );

        Log::warning(
            "Import lead fallito alla riga {$rowNumber}: {$message}",
            [
                'import_report_id' => $this->importReportId,
                'run_uuid' => $this->runUuid,
                'row_number' => $rowNumber,
                'validation_errors' => $errors,
            ]
        );

        $this->createActivityLog(
            lead: null,
            logName: 'import_validation_failure',
            message: "Import lead fallito alla riga {$rowNumber}",
            row: $rowValues,
            rowNumber: $rowNumber,
            validationErrors: $errors,
        );
    }
}

    public function chunkSize(): int
    {
        return 1000;
    }

    /* Prepara i dati per la validazione */
    public function prepareForValidation(array $row)
    {
        // Assicurati che le colonne opzionali esistano sempre nell'array
        $row['data_nascita'] = $row['data_nascita'] ?? null;
        $row['data_di_nascita'] = $row['data_di_nascita'] ?? null;

        foreach ($row as $key => $value) {
            // Salta le date di Excel solo per i campi data_nascita o data_di_nascita
            if (in_array($key, ['data_nascita', 'data_di_nascita']) && $this->looksLikeExcelDate($value)) {
                continue;
            }

            // Converte tutto in stringa per la validazione
            if (!is_null($value) && !is_array($value)) {
                $row[$key] = (string) $value;
            }
        }

        return $row;
    }

    /**
     * Check if value looks like an Excel date
     */
    protected function looksLikeExcelDate($value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        // Excel date serials are typically between 1 (1900-01-01) and 2958465 (9999-12-31)
        return $value >= 1 && $value <= 2958465;
    }

    /* Regole di validazione */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cognome' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'min:10', 'max:24'],
            'email' => ['nullable', 'email', 'max:255'],
            'codice_fiscale' => ['nullable', 'string', 'max:16'],
            'data_nascita' => ['nullable'],
            'data_di_nascita' => ['nullable'],
            'canale_acquisizione' => ['nullable', 'string', 'max:100'],
            'provenienza_lead' => ['nullable', 'string', 'max:100'],
            'stato_lead' => ['nullable', 'string', 'max:100'],
            'tipologia_cliente' => ['nullable', 'string', 'max:255'],
            'collaboratore_associato' => ['nullable', 'string', 'max:255'],
            'indirizzo' => ['nullable', 'string', 'max:255'],
            'citta' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'cap' => ['nullable', 'string', 'max:20'],
            'note' => ['nullable', 'string', 'max:1000'],

            'prodotto' => ['nullable', 'string', 'max:255'],
            'applicazione' => ['nullable', 'string', 'max:255'],
            'tipo_prodotto' => ['nullable', 'string', 'max:255'],
            'numero_rate' => ['nullable', 'numeric'],
            'rate' => ['nullable', 'numeric'],
            'assicurazione' => ['nullable', 'string', 'max:255'],
            'tabella_provvigionale' => ['nullable'],
            'tabella_finanziaria' => ['nullable'],
            'finanziato' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'importo' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'montante' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'totale_dovuto' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'importo_rata' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'rata_mensile' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'tan' => ['nullable', 'numeric', 'between:0,10000'],
            'teg' => ['nullable', 'numeric', 'between:0,10000'],
            'taeg' => ['nullable', 'numeric', 'between:0,10000'],
            'data_prima_rata' => ['nullable'],
            'data_inizio_finanziamento' => ['nullable'],
            'data_inizio' => ['nullable'],
            'data_ultima_rata' => ['nullable'],
            'data_fine' => ['nullable'],
            'percentuale_rinnovabilita' => ['nullable', 'numeric', 'between:0,100'],
            'percentuale_rinnovabilità' => ['nullable', 'numeric', 'between:0,100'],
            'percentuale_alert' => ['nullable', 'numeric', 'between:0,100'],
            'rinnovo' => ['nullable', 'string', 'max:20'],
            'produzione' => ['nullable', 'string', 'max:100'],
            'ente_erogante' => ['nullable', 'string', 'max:255'],
            'istituto_finanziario' => ['nullable', 'string', 'max:255'],
            'finanziaria_estinta' => ['nullable', 'string', 'max:255'],
            'note_pratica' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Parse date from Excel
     */
    protected function parseDate($value)
    {
        if (!$value) return null;

        try {
            // Se è un numero seriale di Excel
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            // Prova formati italiani espliciti (DD-MM-YYYY, DD/MM/YYYY)
            if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $value, $matches)) {
                $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $year = $matches[3];
                return Carbon::createFromFormat('d-m-Y', "$day-$month-$year")->format('Y-m-d');
            }

            // Fallback a Carbon::parse per altri formati
            return Carbon::parse($value)->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Resolve the user assigned to the imported lead.
     */
    protected function getUser(array $row): User
{
    /*
     * The user explicitly selected in the import modal has priority.
     */
    if ($this->defaultUserId !== null) {
        $defaultUser = User::query()
            ->find($this->defaultUserId);

        if ($defaultUser !== null) {
            return $defaultUser;
        }
    }

    $userFullName = strtolower(
        preg_replace(
            '/\s+/',
            ' ',
            trim(
                (string) (
                    $row['collaboratore_associato']
                    ?? ''
                )
            )
        )
    );

    if ($userFullName !== '') {
        $tokens = array_values(
            array_filter(
                explode(' ', $userFullName)
            )
        );

        $query = User::query();

        /*
         * Each token must be present either in the first name or in
         * the last name. This avoids database-specific CONCAT syntax.
         */
        foreach ($tokens as $token) {
            $query->where(function ($query) use ($token): void {
                $like = '%' . $token . '%';

                $query
                    ->whereRaw(
                        'LOWER(first_name) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        'LOWER(last_name) LIKE ?',
                        [$like]
                    );
            });
        }

        $matchedUser = $query->first();

        if ($matchedUser !== null) {
            return $matchedUser;
        }
    }

    /*
     * Queue workers do not have an authenticated HTTP user.
     * Fall back to the user that started the import.
     */
    $initiatedBy = User::query()
        ->find($this->initiatedByUserId);

    if ($initiatedBy !== null) {
        return $initiatedBy;
    }

    return User::role('superadmin')
        ->firstOrFail();
}

    /**
     * Get customer type from row data
     */
    protected function getCustomerType($row): ?CustomerType
    {
        $customerTypeName = strtolower(preg_replace('/\s+/', ' ', trim($row['tipologia_cliente'] ?? '')));

        if (!$customerTypeName) {
            return null;
        }

        // Prima prova match esatto
        $customerType = CustomerType::whereRaw('LOWER(name) = ?', [$customerTypeName])->first();

        if ($customerType) {
            return $customerType;
        }

        // Poi prova match parziale (ordinato per specificità)
        $customerType = CustomerType::whereRaw('LOWER(name) LIKE ?', ['%' . $customerTypeName . '%'])
            ->orderByRaw('LENGTH(name) DESC') // Prima i più lunghi (più specifici)
            ->first();

        if ($customerType) {
            return $customerType;
        }

        return null;
    }

/**
 * Parse acquisition channel from imported value.
 */
    protected function parseLeadSource(?string $source): LeadSource
{
    if (! filled($source)) {
        return LeadSource::OTHER;
    }

    $normalized = strtolower(
        preg_replace(
            '/\s+/',
            ' ',
            trim(str_replace('_', ' ', $source))
        )
    );

    // Supporta direttamente sia i value dell'enum
    // sia le etichette mostrate nell'interfaccia.
    foreach (LeadSource::cases() as $case) {
        $normalizedValue = strtolower(
            str_replace('_', ' ', $case->value)
        );

        $normalizedLabel = strtolower(
            $case->getLabelText()
        );

        if (
            $normalized === $normalizedValue
            || $normalized === $normalizedLabel
        ) {
            return $case;
        }
    }

    // Alias e valori provenienti da vecchi file Excel.
    return match (true) {
        str_contains($normalized, 'tik tok'),
        str_contains($normalized, 'tiktok')
            => LeadSource::TIK_TOK,

        str_contains($normalized, 'meta')
            => LeadSource::META,

        str_contains($normalized, 'motore di ricerca'),
        str_contains($normalized, 'search engine')
            => LeadSource::SEARCH_ENGINE,

        str_contains($normalized, 'referral'),
        str_contains($normalized, 'passaparola')
            => LeadSource::REFERRAL,

        str_contains($normalized, 'portafoglio interno')
            => LeadSource::INTERN_DOC,

        str_contains($normalized, 'portafoglio esterno')
            => LeadSource::EXTERN_DOC,

        str_contains($normalized, 'altro')
            => LeadSource::OTHER,

        default => LeadSource::OTHER,
    };
}

    /**
     * Parse lead status from string
     */
    protected function parseLeadStatus(?string $status): LeadStatus
    {
        if (!$status) {
            return LeadStatus::NEW;
        }

        $normalized = strtolower(preg_replace('/\s+/', ' ', trim($status)));

        return match (true) {
            str_contains($normalized, 'nuovo') => LeadStatus::NEW,
            str_contains($normalized, 'da ricontattare') => LeadStatus::TO_RECONTACT,
            str_contains($normalized, 'in trattativa') => LeadStatus::IN_NEGOTIATION,
            str_contains($normalized, 'non fattibile') => LeadStatus::NOT_FEASIBLE,
            str_contains($normalized, 'fattibile') => LeadStatus::FEASIBLE,
            default => LeadStatus::NEW,
        };
    }

    /**
     * Clean and validate phone number
     */
    protected function cleanPhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Rimuovi spazi, trattini, parentesi
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Verifica che sia un numero valido (almeno 10 cifre, massimo 24)
        if (preg_match('/^\d{10,24}$/', $cleaned)) {
            return $cleaned;
        }

        return null;
    }

    /**
     * Find existing customer or create new one based on tax_id OR email
     */
    protected function findOrCreateCustomer(array $row, array $customerData): Customer
    {
        $taxId = trim($row['codice_fiscale'] ?? '');
        $email = trim($row['email'] ?? '');

        // Se non c'è né codice fiscale né email, crea sempre nuovo
        if (empty($taxId) && empty($email)) {
            $lead = Customer::create($customerData);
            return $lead;
        }

        // Cerca per tax_id (priorità 1)
        if (!empty($taxId)) {
            $lead = Customer::where('tax_id', $taxId)->first();
            if ($lead) {
                $lead->update($customerData);
                return $lead;
            }
        }

        // Cerca per email (priorità 2)
        if (!empty($email)) {
            $lead = Customer::where('email', $email)->first();
            if ($lead) {
                $lead->update($customerData);
                return $lead;
            }
        }

        // Crea nuovo customer
        $lead = Customer::create($customerData);
        return $lead;
    }

    /**
     * Resolve the report service at execution time.
     *
     * The service is resolved lazily so it is not serialized with the
     * queued import instance.
     */
    private function reportService(): ImportReportService
    {
        return app(ImportReportService::class);
    }

    /**
     * Build a readable label for the report row.
     */
    private function buildRowLabel(array $row, int $rowNumber): string
    {
        $firstName = trim((string) ($row['nome'] ?? ''));
        $lastName = trim((string) ($row['cognome'] ?? ''));
        $fullName = trim("{$firstName} {$lastName}");

        return $fullName !== ''
            ? $fullName
            : "Riga {$rowNumber}";
    }

    /**
     * Create an activity log for an imported row.
     */
    protected function createActivityLog(
    ?Customer $lead,
    string $logName,
    string $message,
    array $row = [],
    ?Throwable $exception = null,
    ?int $rowNumber = null,
    array $validationErrors = [],
): void {
    $properties = [
        'import_type' => 'leads',
        'import_report_id' => $this->importReportId,
        'run_uuid' => $this->runUuid,
        'file_name' => $this->fileName,
        'raw_data' => $row,
    ];

    if ($lead !== null) {
        $properties = array_merge($properties, [
            'customer_name' => $lead->full_name,
            'email' => $lead->email,
            'import_action' => $lead->wasRecentlyCreated
                ? 'created'
                : 'updated',
            'url' => route(
                'customer.show',
                $lead->getKey()
            ),
        ]);
    }

    if ($exception !== null) {
        $properties['error_message'] =
            $exception->getMessage();
    }

    if ($rowNumber !== null) {
        $properties['row_number'] = $rowNumber;
    }

    if ($validationErrors !== []) {
        $properties['validation_errors'] =
            $validationErrors;
    }

    $activity = activity($logName);

    if ($lead !== null) {
        $activity->performedOn($lead);
    }

    $initiatedBy = User::query()
        ->find($this->initiatedByUserId);

    if ($initiatedBy !== null) {
        $activity->causedBy($initiatedBy);
    }

    $activity
        ->withProperties($properties)
        ->log($message);
}
}