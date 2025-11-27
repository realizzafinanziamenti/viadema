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

class PracticesImport implements ToModel, WithHeadingRow, SkipsOnFailure, ShouldQueue, WithChunkReading, WithValidation
{
    use SkipsFailures;

    protected ?User $defaultUser;

    public function __construct(?User $defaultUser = null)
    {
        $this->defaultUser = $defaultUser;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Recupera l'utente associato
            $user = $this->getUser($row);
            // Recupera o crea customer
            $customer = $this->setCustomer($row, $user);
            // Imposta il tipo di prodotto
            $product = $this->setProduct($row);
            // cerca il tipo di prodotto corrispondente
            $productSubtype = $this->getProductSubtype($row);
            // Se la data di estinzione è presente, la pratica è considerata liquidata
            // Altrimenti, è in fase di revisione
            $practiceStatus = $row['data_liquidazione'] ? PracticeStatus::DISBURSED->value : PracticeStatus::UNDER_REVIEW->value;
            // cerca l'installment corrispondente
            $installment = Installment::where('value', $row['numero_rate'])->first();
            // Recupera l'assicurazione corrispondente, se esiste
            $insurance = $this->getInsurance($row);
            // cerca il tipo di cliente corrispondente
            $customerType = $this->getCustomerType($row);
            // Recupera i valori di rinnovabilità e percentuale di avviso predefiniti
            $installmentProductDefault = $this->getRenewabilityAndAlertDefaultPercentage($product, $installment);
            // Determina se la pratica è un rinnovo
            $isRenewal = $this->parseRenewalValue($row['rinnovo'] ?? 'N');

            $practice = Practice::updateOrCreate(
                ['practice_code' => $row['pratica']],
                [
                    'product_type_id' => $product->id,
                    'product_subtype_id' => $productSubtype->id ?? null,
                    'user_id' => $user->id,
                    'customer_id' => $customer->id ?? null,
                    'financial_table_id' => null,
                    'insurance_id' => $insurance->id ?? null,
                    'installment_id' => $installment->id ?? null,
                    'customer_type_id' => $customerType->id ?? null,

                    'product_subtype_label' => $productSubtype?->name ?? $row['tipo_prodotto'] ?? null,
                    'financial_table_percentage' => null,
                    'insurance_label' => $insurance?->name ?? $row['assicurazione'] ?? null,
                    'installment_value_label' => $installment?->value ?? $row['numero_rate'] ?? null,
                    'customer_type_label' => $customerType?->name ?? $row['tipo_cliente'] ?? null,

                    'amount_disbursed' => $row['finanziato'] ?? null,
                    'total_amount' => $row['montante'] ?? null,
                    'rate_amount' => $row['importo_rata'] ?? null,
                    'tan' => $row['tan'] ?? null,
                    'teg' => $row['teg'] ?? null,
                    'taeg' => $row['taeg'] ?? null,

                    'inserted_at' => $this->parseDate($row['data_inserimento']) ?? now(),
                    'first_installment_date' => $this->parseDate($row['data_prima_rata']) ?? null,
                    'last_installment_date' => $this->parseDate($row['data_ultima_rata']) ?? null,
                    'early_settlement_date' => $this->parseDate($row['data_estinzione_anticipata']) ?? null,
                    'disbursement_date' => $this->parseDate($row['data_liquidazione']) ?? null,

                    'renewability_percentage' => $installmentProductDefault->renewability_percentage ?? 40.00,
                    'percentage_alert' => $installmentProductDefault->percentage_alert ?? 35.00,

                    'practice_status' => $practiceStatus,
                    'is_renewal' => $isRenewal,

                    'notes' => null,

                    'days_transformation' => $row['trasformazione_gg'] ?? null,
                    'sum_dec_plus_35' => $row['somma_dec_35'] ?? null,
                ]
            );

            $this->createActivityLog($practice, 'import_success', 'Pratica importata con successo', $row);

            return $practice;
        } catch (Exception $e) {
            $this->createActivityLog(null, 'import_failure', 'Errore durante l\'importazione della pratica', $row, $e);
            Log::warning("Errore alla riga con pratica {$row['pratica']}: {$e->getMessage()}");
            return null;
        }
    }

    public function onFailure(Failure ...$failures)
    {
        // Raggruppa per riga
        $rows = collect($failures)->groupBy(fn($f) => $f->row());

        foreach ($rows as $rowNumber => $failureGroup) {

            // Unisci gli errori della riga
            $errors = $failureGroup
                ->flatMap(fn($f) => $f->errors())
                ->unique()
                ->values()
                ->toArray();

            // Prendi i valori della riga
            $rowValues = $failureGroup->first()->values();

            // Crea un FakeFailure che contiene TUTTI gli errori della riga
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

            // Log unico
            Log::warning("Import fallito alla riga {$rowNumber}: " . implode(' | ', $errors));

            $this->createActivityLog(
                practice: null,
                logName: 'import_validation_failure',
                message: "Import lead fallito alla riga {$rowNumber}",
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

    /* Prepara i dati prima della validazione */
    public function prepareForValidation(array $row)
    {
        $values = ['recapito_cell', 'cf_cl'];

        foreach ($values as $key) {
            if (isset($row[$key]) && !is_null($row[$key])) {
                $row[$key] = (string) $row[$key];
            }
        }

        return $row;
    }

    /* Regole di validazione */
    public function rules(): array
    {
        return [
            'pratica' => ['required', Rule::unique('practices', 'practice_code')],

            'cognome_nome_cliente' => ['required', 'string', 'max:255'],
            'cf_cl' => ['nullable', 'string', 'max:16'],
            'recapito_cell' => ['required', 'string', 'min:10', 'max:20'],
            'data_nascita_cliente' => ['nullable'],

            'applicazione' => ['required', 'string'],
            'tipo_prodotto' => ['nullable', 'string', 'max:255'],
            'assicurazione' => ['nullable', 'string', 'max:255'],
            'numero_rate' => ['required', 'numeric'],
            'tipo_cliente' => ['nullable', 'string', 'max:255'],

            'finanziato' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'montante' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'importo_rata' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'tan' => ['required', 'numeric', 'between:0,10000'],
            'teg' => ['nullable', 'numeric', 'between:0,10000'],
            'taeg' => ['required', 'numeric', 'between:0,10000'],

            'data_inserimento' => ['nullable'],
            'data_prima_rata' => ['required'],
            'data_ultima_rata' => ['required'],
            'data_estinzione_anticipata' => ['nullable'],
            'data_liquidazione' => ['nullable'],

            'rinnovo' => ['nullable', 'string', Rule::in(['S', 's', 'N', 'n', 'SI', 'si', 'NO', 'no', 'Y', 'y', '1', '0'])],
            'trasformazione_gg' => ['nullable', 'integer'],
            'somma_dec_35' => ['nullable', 'numeric'],
        ];
    }

    protected function parseDate($value)
    {
        if (!$value) return null;

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Recupera l'utente associato alla riga.
     *
     * @param array $row
     * @return User|null
     */
    protected function getUser($row)
    {
        // Se viene passato un utente di default per l'importazione, usalo
        if ($this->defaultUser) {
            return $this->defaultUser;
        }

        $userFullName = strtolower(trim($row['nome_agenzia']));

        $user = User::whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $userFullName . '%'])
            ->first();

        return $user ?? User::role('superadmin')->first(); // Fallback to superadmin if no user found
    }

    /**
     * Crea o aggiorna il cliente in base ai dati della riga.
     *
     * @param array $row
     * @return Customer
     */
    protected function setCustomer($row, $user): Customer
    {
        // Creazione o aggiornamento del cliente
        $fullName = $row['cognome_nome_cliente'] ?? '';
        $parts = explode(' ', $fullName);
        $lastName = array_shift($parts);
        $firstName = implode(' ', $parts);
        $taxId = $row['cf_cl'] ?? null; // 'cf_cl' dovrebbe corrispondere al codice fiscale del cliente

        // cerca il cliente per codice fiscale
        if ($taxId) {
            $customer = Customer::where('tax_id', $taxId)->first();

            // se il cliente non esiste, lo crea
            if (!$customer) {
                $customer = Customer::create([
                    'user_id' => $user->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $row['recapito_cell'] ?? null,
                    'date_of_birth' => $this->parseDate($row['data_nascita_cliente']) ?? null,
                    'tax_id' => $taxId,
                    'customer_status' => CustomerStatus::CUSTOMER->value, // Imposta lo stato del cliente come "Cliente"
                ]);
            }
        } else {
            // Se il codice fiscale non è presente, crea un cliente senza di esso
            $customer = Customer::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $row['recapito_cell'] ?? null,
                'date_of_birth' => $this->parseDate($row['data_nascita_cliente']) ?? null,
                'tax_id' => null,
                'customer_status' => CustomerStatus::CUSTOMER->value,
            ]);
        }

        return $customer;
    }

    /**
     * Imposta il tipo di prodotto in base ai dati della riga.
     *
     * @param array $row
     * @return ProductType|null
     */
    protected function setProduct($row): ?ProductType
    {
        $value = strtolower(trim($row['applicazione'] ?? ''));

        $product = match ($value) {
            'cqs' => ProductType::where('slug', 'cessione-del-quinto')->first(),
            'cqp' => ProductType::where('slug', 'cessione-del-quinto')->first(),
            'del' => ProductType::where('slug', 'delegazione-di-pagamento')->first(),
            'mutuo' => ProductType::where('slug', 'mutui')->first(),
            'prestito personale' => ProductType::where('slug', 'prestiti')->first(),
            default => ProductType::where('slug', 'prestiti')->first(),
        };

        return $product;
    }

    /**
     * Recupera l'assicurazione in base al nome.
     *
     * @param array $row
     * @return Insurance|null
     */
    protected function getInsurance($row): ?Insurance
    {
        $insuranceName = strtolower(trim($row['assicurazione'] ?? ''));

        $insurance = Insurance::whereRaw('LOWER(name) = ?', [$insuranceName])
            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $insuranceName . '%'])
            ->first();  // garantisce precedenza al match esatto

        return $insurance;
    }

    /**
     * Recupera il tipo di cliente in base al nome.
     *
     * @param array $row
     * @return CustomerType|null
     */
    protected function getCustomerType($row): ?CustomerType
    {
        $customerTypeName = strtolower(trim($row['tipo_cliente'] ?? ''));

        $customerType = CustomerType::whereRaw('LOWER(name) = ?', [$customerTypeName])
            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $customerTypeName . '%'])
            ->first();  // garantisce precedenza al match esatto

        return $customerType;
    }

    /**
     * Recupera il sottotipo di prodotto in base al nome.
     *
     * @param array $row
     * @return ProductSubtype|null
     */
    protected function getProductSubtype($row): ?ProductSubtype
    {
        $productSubtypeName = strtolower(trim($row['tipo_prodotto'] ?? ''));

        $productSubtype = ProductSubtype::whereRaw('LOWER(name) = ?', [$productSubtypeName])
            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $productSubtypeName . '%'])
            ->first();  // garantisce precedenza al match esatto

        return $productSubtype;
    }

    /**
     * Recupera i valori di rinnovabilità e percentuale di avviso predefiniti per il prodotto e l'installment specificati.
     *
     * @param Product $product
     * @param Installment|null $installment
     * @return InstallmentProductDefault|null
     */
    protected function getRenewabilityAndAlertDefaultPercentage(?ProductType $product, ?Installment $installment): ?InstallmentProductDefault
    {
        if (!$installment) {
            return null;
        }

        $installmentProductDefault = InstallmentProductDefault::where('product_type_id', $product->id)
            ->where('installment_id', $installment->id ?? null)
            ->first();

        return $installmentProductDefault;
    }

    /**
     * Parse renewal value from Excel (S/N to true/false)
     *
     * @param string|null $value
     * @return bool
     */
    protected function parseRenewalValue(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        $normalizedValue = strtoupper(trim($value));

        return match ($normalizedValue) {
            'S', 'SI', 'SÌ', 'YES', 'Y', '1' => true,
            'N', 'NO', '0' => false,
            default => false, // Default a false per valori non riconosciuti
        };
    }

    /**
     * Crea un log di attività per l'importazione.
     */
    protected function createActivityLog($practice, $logName, $message, $row = [], $e = null, $failures = null)
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
            $properties = array_merge($properties, [
                'error_message' => $e->getMessage(),
            ]);
        }

        if ($failures) {
            $properties = array_merge($properties, [
                'row_number' => $failures->row(),
                'validation_errors' => $failures->errors(),
                'failed_data' => $failures->values(),
            ]);
        }

        activity($logName)
            ->when($practice, fn($activity) => $activity->performedOn($practice))
            ->causedBy(auth()->user())
            ->withProperties($properties)
            ->log($message);
    }
}
