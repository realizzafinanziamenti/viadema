<?php

namespace App\Imports;

use App\Enums\CustomerStatus;
use App\Enums\PracticeStatus;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Installment;
use App\Models\InstallmentProductDefault;
use App\Models\Insurance;
use App\Models\Practice;
use App\Models\ProductSubtype;
use App\Models\ProductType;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\PracticeOpportunity;
use Illuminate\Support\Facades\DB;
class PracticesImport implements ToModel, WithHeadingRow, SkipsOnFailure, ShouldQueue, WithChunkReading, WithValidation
{
    use SkipsFailures;

    protected ?User $defaultUser;

    public function __construct(?User $defaultUser = null)
    {
        $this->defaultUser = $defaultUser;
    }

    public function model(array $row)
    {
        try {
            $user = $this->getUser($row);
            $customer = $this->setCustomer($row, $user);
            $product = $this->setProduct($row);
            $productSubtype = $this->getProductSubtype($row);

            $installment = $this->value($row, 'numero_rate')
                ? Installment::where('value', $this->value($row, 'numero_rate'))->first()
                : null;

            $insurance = $this->getInsurance($row);
            $customerType = $this->getCustomerType($row);
            $installmentProductDefault = $this->getRenewabilityAndAlertDefaultPercentage($product, $installment);
            $practiceStatus = $this->getPracticeStatus($row);
            $disbursementDate = $this->parseDate($this->value($row, 'data_liquidazione'));
            if (
                $practiceStatus === PracticeStatus::DISBURSED->value &&
                !$disbursementDate
            ) {
                $disbursementDate =
                    $this->parseDate($this->value($row, 'data_inserimento'))
                    ?? now()->format('Y-m-d');
            }
            $isRenewal = $this->parseRenewalValue($this->value($row, 'rinnovo'));
            $practice = DB::transaction(function () use (
                $customer,
                $user,
                $product,
                $productSubtype,
                $installment,
                $insurance,
                $customerType,
                $installmentProductDefault,
                $practiceStatus,
                $disbursementDate,
                $isRenewal,
                $row
            ) {
                $opportunity = PracticeOpportunity::create([
                    'customer_id' => $customer->id,

                    'product_type_id' => $product?->id,
                    'product_subtype_id' => $productSubtype?->id,
                    'financial_table_id' => null,
                    'insurance_id' => $insurance?->id,
                    'installment_id' => $installment?->id,
                    'customer_type_id' => $customerType?->id,

                    'amount_disbursed' => $this->nullableNumber($this->value($row, 'finanziato')),
                    'total_amount' => $this->nullableNumber($this->value($row, 'montante')),
                    'rate_amount' => $this->nullableNumber($this->value($row, 'importo_rata')),
                    'tan' => $this->nullableNumber($this->value($row, 'tan')),
                    'teg' => $this->nullableNumber($this->value($row, 'teg')),
                    'taeg' => $this->nullableNumber($this->value($row, 'taeg')),

                    'first_installment_date' => $this->parseDate($this->getFirstInstallmentDate($row)),
                    'last_installment_date' => $this->parseDate($this->getLastInstallmentDate($row)),

                    'renewability_percentage' => $installmentProductDefault?->renewability_percentage ?? 40.00,
                    'percentage_alert' => $installmentProductDefault?->percentage_alert ?? 35.00,

                    'is_renewal' => $isRenewal,

                    'notes' => null,
                ]);

                $practice = Practice::create([
                    'user_id' => $user?->id,
                    'customer_id' => $customer->id,
                    'practice_opportunity_id' => $opportunity->id,

                    'inserted_at' => $this->parseDate($this->value($row, 'data_inserimento')) ?? now(),
                    'early_settlement_date' => $this->parseDate($this->value($row, 'data_estinzione_anticipata')),
                    'disbursement_date' => $disbursementDate,

                    'practice_status' => $practiceStatus,

                    'days_transformation' => $this->value($row, 'trasformazione_gg'),
                    'sum_dec_plus_35' => $this->nullableNumber($this->value($row, 'somma_dec_35')),
                ]);

                $this->createActivityLog($practice, 'import_success', 'Pratica importata con successo', $row);

                return $practice;
            });

            return $practice;
        } catch (Exception $e) {
            $this->createActivityLog(null, 'import_failure', 'Errore durante l\'importazione della pratica', $row, $e);

            Log::warning('Errore import pratica per cliente ' . $this->getCustomerLogName($row) . ': ' . $e->getMessage());

            return null;
        }
    }

    public function onFailure(Failure ...$failures): void
    {
        $rows = collect($failures)->groupBy(fn ($failure) => $failure->row());

        foreach ($rows as $rowNumber => $failureGroup) {
            $errors = $failureGroup
                ->flatMap(fn ($failure) => $failure->errors())
                ->unique()
                ->values()
                ->toArray();

            $rowValues = $failureGroup->first()->values();

            $fake = new class($rowNumber, $errors, $rowValues) {
                public function __construct(
                    public int $row,
                    public array $errors,
                    public array $values
                ) {}

                public function row()
                {
                    return $this->row;
                }

                public function errors()
                {
                    return $this->errors;
                }

                public function values()
                {
                    return $this->values;
                }
            };

            Log::warning("Import fallito alla riga {$rowNumber}: " . implode(' | ', $errors));

            $this->createActivityLog(
                practice: null,
                logName: 'import_validation_failure',
                message: "Import pratica fallito alla riga {$rowNumber} per cliente " . $this->getCustomerLogName($rowValues),
                row: $rowValues,
                e: null,
                failures: $fake
            );
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function prepareForValidation(array $row): array
    {
        foreach (['recapito_cell', 'numero_di_tel', 'cf_cl'] as $key) {
            if (isset($row[$key]) && $row[$key] !== null) {
                $row[$key] = trim((string) $row[$key]);
            }
        }

        foreach (['nome', 'cognome', 'cognome_nome_cliente'] as $key) {
            if (isset($row[$key]) && $row[$key] !== null) {
                $row[$key] = trim((string) $row[$key]);
            }
        }

        return $row;
    }

    public function rules(): array
    {
        return [
            'cognome_nome_cliente' => ['nullable', 'string', 'max:255'],
            'nome' => ['required_without:cognome_nome_cliente', 'string', 'max:255'],
            'cognome' => ['required_without:cognome_nome_cliente', 'string', 'max:255'],

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
                Rule::in(['S', 's', 'N', 'n', 'SI', 'si', 'NO', 'no', 'Y', 'y', '1', '0']),
            ],

            'trasformazione_gg' => ['nullable', 'integer'],
            'somma_dec_35' => ['nullable', 'numeric'],
        ];
    }

    protected function value(array $row, string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $row) && $row[$key] !== '' ? $row[$key] : $default;
    }

    protected function nullableNumber(mixed $value): ?float
    {
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
            ((string) $this->value($row, 'cognome', '')) . ' ' .
            ((string) $this->value($row, 'nome', ''))
        );

        return $composed !== '' ? $composed : 'N/D';
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

    protected function parseDate($value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (Exception) {
            return null;
        }
    }

    protected function getUser(array $row): ?User
    {
        if ($this->defaultUser) {
            return $this->defaultUser;
        }

        $userFullName = strtolower(trim((string) $this->value($row, 'nome_agenzia', '')));

        if ($userFullName !== '') {
            $user = User::whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $userFullName . '%'])
                ->first();

            if ($user) {
                return $user;
            }
        }

        return auth()->user() ?? User::role('superadmin')->first();
    }

    protected function setCustomer(array $row, ?User $user): Customer
    {
        $nameParts = $this->getCustomerNameParts($row);
        $taxId = $this->value($row, 'cf_cl');

        if ($taxId) {
            $customer = Customer::where('tax_id', $taxId)->first();

            if ($customer) {
                return $customer;
            }
        }

        return Customer::create([
            'user_id' => $user?->id,
            'first_name' => $nameParts['first_name'],
            'last_name' => $nameParts['last_name'],
            'phone' => $this->getPhone($row),
            'date_of_birth' => $this->parseDate($this->value($row, 'data_nascita_cliente')),
            'tax_id' => $taxId,
            'customer_status' => CustomerStatus::CUSTOMER->value,
        ]);
    }

    protected function setProduct(array $row): ?ProductType
    {
        $value = strtolower(trim((string) $this->value($row, 'applicazione', '')));

        return match ($value) {
            'cqs', 'cqp' => ProductType::where('slug', 'cessione-del-quinto')->first(),
            'del' => ProductType::where('slug', 'delegazione-di-pagamento')->first(),
            'mutuo' => ProductType::where('slug', 'mutui')->first(),
            'prestito personale' => ProductType::where('slug', 'prestiti')->first(),
            default => null,
        };
    }

    protected function getInsurance(array $row): ?Insurance
    {
        $insuranceName = strtolower(trim((string) $this->value($row, 'assicurazione', '')));

        if ($insuranceName === '') {
            return null;
        }

        return Insurance::whereRaw('LOWER(name) = ?', [$insuranceName])
            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $insuranceName . '%'])
            ->first();
    }

    protected function getCustomerType(array $row): ?CustomerType
    {
        $customerTypeName = strtolower(trim((string) $this->value($row, 'tipo_cliente', '')));

        if ($customerTypeName === '') {
            return null;
        }

        return CustomerType::whereRaw('LOWER(name) = ?', [$customerTypeName])
            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $customerTypeName . '%'])
            ->first();
    }

    protected function getProductSubtype(array $row): ?ProductSubtype
    {
        $productSubtypeName = strtolower(trim((string) $this->value($row, 'tipo_prodotto', '')));

        if ($productSubtypeName === '') {
            return null;
        }

        return ProductSubtype::whereRaw('LOWER(name) = ?', [$productSubtypeName])
            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $productSubtypeName . '%'])
            ->first();
    }

    protected function getRenewabilityAndAlertDefaultPercentage(?ProductType $product, ?Installment $installment): ?InstallmentProductDefault
    {
        if (!$product || !$installment) {
            return null;
        }

        return InstallmentProductDefault::where('product_type_id', $product->id)
            ->where('installment_id', $installment->id)
            ->first();
    }

    protected function parseRenewalValue(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        return match (strtoupper(trim($value))) {
            'S', 'SI', 'SÌ', 'YES', 'Y', '1' => true,
            default => false,
        };
    }

    protected function createActivityLog($practice, $logName, $message, $row = [], $e = null, $failures = null): void
    {
        $properties = [
            'import_type' => 'practices',
            'raw_data' => $row,
            'file_name' => request()->file('file')?->getClientOriginalName(),
        ];

        if ($practice) {
            $properties = array_merge($properties, [
                'practice_code' => $practice->practice_code,
                'customer_name' => $practice->customer?->full_name,
                'import_action' => $practice->wasRecentlyCreated ? 'created' : 'updated',
                'url' => route('practice.show', $practice->id),
            ]);
        }

        if ($e) {
            $properties['error_message'] = $e->getMessage();
        }

        if ($failures) {
            $properties = array_merge($properties, [
                'row_number' => $failures->row(),
                'validation_errors' => $failures->errors(),
                'failed_data' => $failures->values(),
            ]);
        }

        activity($logName)
            ->when($practice, fn ($activity) => $activity->performedOn($practice))
            ->causedBy(auth()->user())
            ->withProperties($properties)
            ->log($message);
    }
}
