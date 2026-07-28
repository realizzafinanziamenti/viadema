<?php

namespace App\Imports;

use App\Enums\CustomerStatus;
use App\Enums\LeadSource;
use App\Enums\PracticeStatus;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Installment;
use App\Models\InstallmentProductDefault;
use App\Models\Insurance;
use App\Models\Practice;
use App\Models\PracticeOpportunity;
use App\Models\ProductSubtype;
use App\Models\ProductType;
use App\Models\User;
use App\Services\Imports\ImportReportService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Throwable;

class PracticesImport implements
    ToModel,
    WithHeadingRow,
    SkipsOnFailure,
    ShouldQueue,
    WithChunkReading,
    WithValidation,
    WithEvents
{
    use RemembersRowNumber;

    protected ?int $defaultUserId;

    public function __construct(
        ?User $defaultUser,
        protected int $importReportId,
        protected string $runUuid,
        protected int $initiatedByUserId,
        protected string $fileName,
    ) {
        /*
         * Queued imports are serialized.
         * Keep only scalar values instead of serializing Eloquent models.
         */
        $this->defaultUserId = $defaultUser?->getKey();
    }

    /**
     * Import a single Excel row.
     */
    public function model(array $row): ?Practice
    {
        $rowNumber = $this->getRowNumber();
        $label = $this->buildRowLabel($row, $rowNumber);

        try {
            /*
             * Customer, opportunity, practice and success report row are
             * persisted atomically. If reporting fails, domain data is
             * rolled back as well, preventing duplicates on queue retries.
             */
            $practice = DB::transaction(function () use (
                $row,
                $rowNumber
            ): Practice {
                $user = $this->getUser($row);
                $customer = $this->setCustomer($row, $user);
                $product = $this->setProduct($row);
                $productSubtype = $this->getProductSubtype($row);

                $installment = $this->value($row, 'numero_rate')
                    ? Installment::query()
                        ->where(
                            'value',
                            $this->value($row, 'numero_rate')
                        )
                        ->first()
                    : null;

                $insurance = $this->getInsurance($row);
                $customerType = $this->getCustomerType($row);

                $installmentProductDefault =
                    $this->getRenewabilityAndAlertDefaultPercentage(
                        $product,
                        $installment
                    );

                $practiceStatus = $this->getPracticeStatus($row);

                $disbursementDate = $this->parseDate(
                    $this->value($row, 'data_liquidazione')
                );

                if (
                    $practiceStatus === PracticeStatus::DISBURSED->value
                    && $disbursementDate === null
                ) {
                    $disbursementDate = $this->parseDate(
                        $this->value($row, 'data_inserimento')
                    ) ?? now()->format('Y-m-d');
                }

                $isRenewal = $this->parseRenewalValue(
                    $this->value($row, 'rinnovo')
                );

                $acquisitionChannel =
                    $this->getAcquisitionChannel($row);

                $opportunity = PracticeOpportunity::create([
                    'customer_id' => $customer->getKey(),
                    'acquisition_channel' =>
                        $acquisitionChannel?->value,

                    'product_type_id' => $product?->getKey(),
                    'product_subtype_id' =>
                        $productSubtype?->getKey(),
                    'financial_table_id' => null,
                    'insurance_id' => $insurance?->getKey(),
                    'installment_id' => $installment?->getKey(),
                    'customer_type_id' =>
                        $customerType?->getKey(),

                    'amount_disbursed' => $this->nullableNumber(
                        $this->value($row, 'finanziato')
                    ),
                    'total_amount' => $this->nullableNumber(
                        $this->value($row, 'montante')
                    ),
                    'rate_amount' => $this->nullableNumber(
                        $this->value($row, 'importo_rata')
                    ),
                    'tan' => $this->nullableNumber(
                        $this->value($row, 'tan')
                    ),
                    'teg' => $this->nullableNumber(
                        $this->value($row, 'teg')
                    ),
                    'taeg' => $this->nullableNumber(
                        $this->value($row, 'taeg')
                    ),

                    'first_installment_date' => $this->parseDate(
                        $this->getFirstInstallmentDate($row)
                    ),
                    'last_installment_date' => $this->parseDate(
                        $this->getLastInstallmentDate($row)
                    ),

                    'renewability_percentage' =>
                        $installmentProductDefault
                            ?->renewability_percentage
                        ?? 40.00,

                    'percentage_alert' =>
                        $installmentProductDefault
                            ?->percentage_alert
                        ?? 35.00,

                    'is_renewal' => $isRenewal,
                    'notes' => null,
                ]);

                $practice = Practice::create([
                    'user_id' => $user->getKey(),
                    'customer_id' => $customer->getKey(),
                    'practice_opportunity_id' =>
                        $opportunity->getKey(),

                    'inserted_at' => $this->parseDate(
                        $this->value($row, 'data_inserimento')
                    ) ?? now(),

                    'early_settlement_date' => $this->parseDate(
                        $this->value(
                            $row,
                            'data_estinzione_anticipata'
                        )
                    ),

                    'disbursement_date' => $disbursementDate,
                    'practice_status' => $practiceStatus,

                    'days_transformation' =>
                        $this->value($row, 'trasformazione_gg'),

                    'sum_dec_plus_35' => $this->nullableNumber(
                        $this->value($row, 'somma_dec_35')
                    ),
                ]);

                $message = filled($practice->practice_code)
                    ? "Pratica {$practice->practice_code} importata correttamente."
                    : 'Pratica importata correttamente.';

                $this->reportService()->recordImportedRow(
                    reportId: $this->importReportId,
                    runUuid: $this->runUuid,
                    rowNumber: $rowNumber,
                    label: $customer->full_name
                        ?: $this->buildRowLabel($row, $rowNumber),
                    rawData: $row,
                    entityType: Practice::class,
                    entityId: $practice->getKey(),
                    message: $message,
                );

                return $practice;
            }, 3);
        } catch (Throwable $exception) {
            $errors = [$exception->getMessage()];

            $this->reportService()->recordFailedRow(
                reportId: $this->importReportId,
                runUuid: $this->runUuid,
                rowNumber: $rowNumber,
                label: $label,
                message:
                    'Errore durante l\'importazione della pratica.',
                rawData: $row,
                errors: $errors,
            );

            $this->createActivityLog(
                practice: null,
                logName: 'import_failure',
                message:
                    "Errore durante l'importazione della pratica alla riga {$rowNumber}",
                row: $row,
                exception: $exception,
                rowNumber: $rowNumber,
                validationErrors: $errors,
            );

            Log::warning(
                "Errore import pratica alla riga {$rowNumber}: {$exception->getMessage()}",
                [
                    'import_report_id' => $this->importReportId,
                    'run_uuid' => $this->runUuid,
                    'row_number' => $rowNumber,
                    'label' => $label,
                    'exception' => $exception,
                ]
            );

            return null;
        }

        $this->createActivityLog(
            practice: $practice,
            logName: 'import_success',
            message: 'Pratica importata con successo',
            row: $row,
            rowNumber: $rowNumber,
        );

        return $practice;
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
                static fn (Failure $failure): int =>
                    $failure->row()
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
                "Import pratica fallito alla riga {$rowNumber}: {$message}",
                [
                    'import_report_id' => $this->importReportId,
                    'run_uuid' => $this->runUuid,
                    'row_number' => $rowNumber,
                    'validation_errors' => $errors,
                ]
            );

            $this->createActivityLog(
                practice: null,
                logName: 'import_validation_failure',
                message:
                    "Import pratica fallito alla riga {$rowNumber}",
                row: $rowValues,
                rowNumber: $rowNumber,
                validationErrors: $errors,
            );
        }
    }

    public function registerEvents(): array
    {
        return [
            ImportFailed::class => [
                $this,
                'handleImportFailed',
            ],
        ];
    }

    public function handleImportFailed(
        ImportFailed $event
    ): void {
        $exception = $event->getException();

        try {
            $this->reportService()->fail(
                reportId: $this->importReportId,
                runUuid: $this->runUuid,
                errorMessage: $exception->getMessage(),
            );
        } catch (Throwable $reportException) {
            Log::error(
                'Impossibile marcare come fallito il report import pratiche.',
                [
                    'import_report_id' => $this->importReportId,
                    'run_uuid' => $this->runUuid,
                    'exception' => $reportException,
                ]
            );
        }

        Log::error(
            'Import pratiche in coda terminato con errore.',
            [
                'import_report_id' => $this->importReportId,
                'run_uuid' => $this->runUuid,
                'file_name' => $this->fileName,
                'exception' => $exception,
            ]
        );
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function prepareForValidation(array $row): array
    {
        foreach (
            ['recapito_cell', 'numero_di_tel', 'cf_cl']
            as $key
        ) {
            if (
                isset($row[$key])
                && $row[$key] !== null
            ) {
                $row[$key] = trim((string) $row[$key]);
            }
        }

        foreach (
            ['nome', 'cognome', 'cognome_nome_cliente']
            as $key
        ) {
            if (
                isset($row[$key])
                && $row[$key] !== null
            ) {
                $row[$key] = trim((string) $row[$key]);
            }
        }

        return $row;
    }

    public function rules(): array
    {
        return [
            'canale_acquisizione' => ['nullable', 'string', 'max:100'],
            'provenienza_lead' => ['nullable', 'string', 'max:100'],
            'cognome_nome_cliente' => [
                'nullable',
                'string',
                'max:255',
            ],

            'nome' => [
                'nullable',
                'required_without:cognome_nome_cliente',
                'string',
                'max:255',
            ],

            'cognome' => [
                'nullable',
                'required_without:cognome_nome_cliente',
                'string',
                'max:255',
            ],
            'cf_cl' => ['nullable', 'string', 'max:16'],
            'recapito_cell' => ['nullable', 'string', 'min:10', 'max:20'],
            'numero_di_tel' => ['required_without:recapito_cell', 'string', 'min:10', 'max:20'],
            'data_nascita_cliente' => ['nullable'],
            'applicazione' => ['nullable', 'string'],
            'tipo_prodotto' => ['nullable', 'string', 'max:255'],
            'assicurazione' => ['nullable', 'string', 'max:255'],
            'numero_rate' => ['nullable', 'numeric'],
            'tipo_cliente' => ['nullable', 'string', 'max:255'],
            'finanziato' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'montante' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'importo_rata' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'tan' => ['nullable', 'numeric', 'between:0,10000'],
            'teg' => ['nullable', 'numeric', 'between:0,10000'],
            'taeg' => ['nullable', 'numeric', 'between:0,10000'],
            'data_inserimento' => ['nullable'],
            'data_prima_rata' => ['nullable', 'required_without:data_inizio_finanziamento'],
            'data_inizio_finanziamento' => ['nullable', 'required_without:data_prima_rata'],
            'data_ultima_rata' => ['nullable'],
            'data_fine' => ['nullable'],
            'data_estinzione_anticipata' => ['nullable'],
            'data_liquidazione' => ['nullable'],
            'stato_pratica' => ['nullable', 'string'],
            'rinnovo' => [
                'nullable',
                'string',
                Rule::in([
                    'S', 's', 'N', 'n', 'SI', 'si', 'NO', 'no', 'Y', 'y', '1', '0',
                ]),
            ],
            'trasformazione_gg' => ['nullable', 'integer'],
            'somma_dec_35' => ['nullable', 'numeric'],
        ];
    }

    protected function value(
        array $row,
        string $key,
        mixed $default = null
    ): mixed {
        return array_key_exists($key, $row)
            && $row[$key] !== ''
                ? $row[$key]
                : $default;
    }

    protected function nullableNumber(
        mixed $value
    ): ?float {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) str_replace(',', '.', (string) $value);
    }

    protected function getCustomerLogName(array $row): string
    {
        $name = trim((string) ($this->value($row, 'cognome_nome_cliente') ?? ''));

        if ($name !== '') {
            return $name;
        }

        $composed = trim(
            (string) $this->value($row, 'cognome', '')
            . ' '
            . (string) $this->value($row, 'nome', '')
        );

        return $composed !== '' ? $composed : 'N/D';
    }

    protected function buildRowLabel(
        array $row,
        int $rowNumber
    ): string {
        $name = $this->getCustomerLogName($row);

        return $name !== 'N/D'
            ? $name
            : "Riga {$rowNumber}";
    }

    protected function getCustomerNameParts(array $row): array
    {
        $fullName = trim((string) $this->value($row, 'cognome_nome_cliente', ''));

        if ($fullName !== '') {
            $parts = preg_split('/\s+/', $fullName) ?: [];

            return [
                'last_name' => array_shift($parts) ?: '',
                'first_name' => implode(' ', $parts),
            ];
        }

        return [
            'first_name' => trim((string) $this->value($row, 'nome', '')),
            'last_name' => trim((string) $this->value($row, 'cognome', '')),
        ];
    }

    protected function getPhone(array $row): ?string
    {
        return $this->value($row, 'recapito_cell')
            ?? $this->value($row, 'numero_di_tel');
    }

    protected function getFirstInstallmentDate(array $row): mixed
    {
        return $this->value($row, 'data_prima_rata')
            ?? $this->value($row, 'data_inizio_finanziamento');
    }

    protected function getLastInstallmentDate(array $row): mixed
    {
        return $this->value($row, 'data_ultima_rata')
            ?? $this->value($row, 'data_fine');
    }

    protected function getPracticeStatus(array $row): string
    {
        $status = $this->value($row, 'stato_pratica');

        if ($status) {
            return (string) $status;
        }

        return $this->getLastInstallmentDate($row)
            ? PracticeStatus::DISBURSED->value
            : PracticeStatus::UNDER_REVIEW->value;
    }

    protected function parseDate(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    protected function getUser(array $row): User
    {
        if ($this->defaultUserId !== null) {
            $defaultUser = User::query()->find($this->defaultUserId);

            if ($defaultUser !== null) {
                return $defaultUser;
            }
        }

        $userFullName = mb_strtolower(
            preg_replace(
                '/\s+/',
                ' ',
                trim((string) $this->value($row, 'nome_agenzia', ''))
            )
        );

        if ($userFullName !== '') {
            $tokens = array_values(array_filter(explode(' ', $userFullName)));
            $query = User::query();

            foreach ($tokens as $token) {
                $query->where(function ($query) use ($token): void {
                    $like = '%' . $token . '%';

                    $query
                        ->whereRaw('LOWER(first_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', [$like]);
                });
            }

            $matchedUser = $query->first();

            if ($matchedUser !== null) {
                return $matchedUser;
            }
        }

        $initiatedBy = User::query()->find($this->initiatedByUserId);

        if ($initiatedBy !== null) {
            return $initiatedBy;
        }

        return User::role('superadmin')->firstOrFail();
    }

    protected function setCustomer(array $row, User $user): Customer
    {
        $nameParts = $this->getCustomerNameParts($row);
        $taxId = $this->value($row, 'cf_cl');

        if ($taxId) {
            $customer = Customer::query()->where('tax_id', $taxId)->first();

            if ($customer !== null) {
                return $customer;
            }
        }

        return Customer::create([
            'user_id' => $user->getKey(),
            'first_name' => $nameParts['first_name'],
            'last_name' => $nameParts['last_name'],
            'phone' => $this->getPhone($row),
            'date_of_birth' => $this->parseDate(
                $this->value($row, 'data_nascita_cliente')
            ),
            'tax_id' => $taxId,
            'customer_status' => CustomerStatus::CUSTOMER->value,
        ]);
    }

    protected function setProduct(array $row): ?ProductType
    {
        $value = mb_strtolower(
            trim((string) $this->value($row, 'applicazione', ''))
        );

        return match ($value) {
            'cqs', 'cqp' => ProductType::query()
                ->where('slug', 'cessione-del-quinto')
                ->first(),
            'del' => ProductType::query()
                ->where('slug', 'delegazione-di-pagamento')
                ->first(),
            'mutuo' => ProductType::query()
                ->where('slug', 'mutui')
                ->first(),
            'prestito personale' => ProductType::query()
                ->where('slug', 'prestiti')
                ->first(),
            default => null,
        };
    }

    protected function getAcquisitionChannel(array $row): ?LeadSource
    {
        $value = $this->value($row, 'canale_acquisizione')
            ?? $this->value($row, 'provenienza_lead');

        if ($value === null) {
            return null;
        }

        return $this->parseLeadSource((string) $value);
    }

    protected function parseLeadSource(?string $source): ?LeadSource
    {
        if ($source === null || trim($source) === '') {
            return null;
        }

        $rawValue = trim($source);
        $enumValue = LeadSource::tryFrom($rawValue);

        if ($enumValue !== null) {
            return $enumValue;
        }

        $normalized = mb_strtolower(
            preg_replace('/\s+/', ' ', str_replace('_', ' ', $rawValue))
        );

        foreach (LeadSource::cases() as $case) {
            $caseValue = mb_strtolower(str_replace('_', ' ', $case->value));
            $caseLabel = mb_strtolower($case->getLabelText());

            if ($normalized === $caseValue || $normalized === $caseLabel) {
                return $case;
            }
        }

        return match (true) {
            str_contains($normalized, 'tik tok'),
            str_contains($normalized, 'tiktok') => LeadSource::TIK_TOK,
            str_contains($normalized, 'meta') => LeadSource::META,
            str_contains($normalized, 'motore di ricerca'),
            str_contains($normalized, 'search engine') => LeadSource::SEARCH_ENGINE,
            str_contains($normalized, 'referral'),
            str_contains($normalized, 'passaparola') => LeadSource::REFERRAL,
            str_contains($normalized, 'portafoglio interno') => LeadSource::INTERN_DOC,
            str_contains($normalized, 'portafoglio esterno') => LeadSource::EXTERN_DOC,
            default => LeadSource::OTHER,
        };
    }

    protected function getInsurance(array $row): ?Insurance
    {
        $insuranceName = mb_strtolower(
            trim((string) $this->value($row, 'assicurazione', ''))
        );

        if ($insuranceName === '') {
            return null;
        }

        return Insurance::query()
            ->whereRaw('LOWER(name) = ?', [$insuranceName])
            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $insuranceName . '%'])
            ->first();
    }

    protected function getCustomerType(array $row): ?CustomerType
    {
        $customerTypeName = mb_strtolower(
            trim((string) $this->value($row, 'tipo_cliente', ''))
        );

        if ($customerTypeName === '') {
            return null;
        }

        return CustomerType::query()
            ->whereRaw('LOWER(name) = ?', [$customerTypeName])
            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $customerTypeName . '%'])
            ->first();
    }

    protected function getProductSubtype(array $row): ?ProductSubtype
    {
        $productSubtypeName = mb_strtolower(
            trim((string) $this->value($row, 'tipo_prodotto', ''))
        );

        if ($productSubtypeName === '') {
            return null;
        }

        return ProductSubtype::query()
            ->whereRaw('LOWER(name) = ?', [$productSubtypeName])
            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $productSubtypeName . '%'])
            ->first();
    }

    protected function getRenewabilityAndAlertDefaultPercentage(
        ?ProductType $product,
        ?Installment $installment
    ): ?InstallmentProductDefault {
        if ($product === null || $installment === null) {
            return null;
        }

        return InstallmentProductDefault::query()
            ->where('product_type_id', $product->getKey())
            ->where('installment_id', $installment->getKey())
            ->first();
    }

    protected function parseRenewalValue(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        return match (mb_strtoupper(trim($value))) {
            'S', 'SI', 'SÌ', 'YES', 'Y', '1' => true,
            default => false,
        };
    }

    private function reportService(): ImportReportService
    {
        return app(ImportReportService::class);
    }

    protected function createActivityLog(
        ?Practice $practice,
        string $logName,
        string $message,
        array $row = [],
        ?Throwable $exception = null,
        ?int $rowNumber = null,
        array $validationErrors = [],
    ): void {
        try {
            $properties = [
                'import_type' => 'practices',
                'import_report_id' => $this->importReportId,
                'run_uuid' => $this->runUuid,
                'raw_data' => $row,
                'file_name' => $this->fileName,
            ];

            if ($practice !== null) {
                $properties = array_merge($properties, [
                    'practice_code' => $practice->practice_code,
                    'customer_name' => $practice->customer?->full_name,
                    'import_action' => $practice->wasRecentlyCreated
                        ? 'created'
                        : 'updated',
                    'url' => route('practice.show', $practice->getKey()),
                ]);
            }

            if ($exception !== null) {
                $properties['error_message'] = $exception->getMessage();
            }

            if ($rowNumber !== null) {
                $properties['row_number'] = $rowNumber;
            }

            if ($validationErrors !== []) {
                $properties['validation_errors'] = $validationErrors;
            }

            $activity = activity($logName);

            if ($practice !== null) {
                $activity->performedOn($practice);
            }

            $initiatedBy = User::query()->find($this->initiatedByUserId);

            if ($initiatedBy !== null) {
                $activity->causedBy($initiatedBy);
            }

            $activity
                ->withProperties($properties)
                ->log($message);
        } catch (Throwable $loggingException) {
            Log::error(
                'Impossibile registrare l\'activity log dell\'import pratica.',
                [
                    'import_report_id' => $this->importReportId,
                    'run_uuid' => $this->runUuid,
                    'row_number' => $rowNumber,
                    'log_name' => $logName,
                    'exception' => $loggingException,
                ]
            );
        }
    }
}