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
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LeadsImport implements ToModel, WithHeadingRow, SkipsOnFailure, ShouldQueue, WithChunkReading, WithValidation
{
    use SkipsFailures;

    protected ?User $defaultUser;

    public function __construct(?User $defaultUser = null)
    {
        $this->defaultUser = $defaultUser;
    }

    /**
     * @param array $row
     * @return Customer|null
     */
    public function model(array $row)
    {
        try {
            // Recupera l'utente associato
            $user = $this->getUser($row);

            // Recupera il tipo di cliente, se specificato
            $customerType = $this->getCustomerType($row);

            // Parse lead source e status
            $leadSource = $this->parseLeadSource($row['provenienza_lead'] ?? 'sconosciuto');
            $leadStatus = $this->parseLeadStatus($row['stato_lead'] ?? 'nuovo');

            // Parsing semplificato nome, cognome, codice fiscale
            $firstName = trim($row['nome'] ?? '');
            $lastName = trim($row['cognome'] ?? '');
            $cf = trim($row['codice_fiscale'] ?? '');
            $cf = !empty($cf) ? $cf : null; // Se vuoto, setta NULL per evitare problemi di univocità

            $customerData = [
                'user_id' => $user->id,
                'customer_type_id' => $customerType?->id,

                // Dati anagrafici semplificati
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $this->cleanPhone($row['telefono']),
                'email' => $row['email'] ?? null,
                'date_of_birth' => isset($row['data_nascita']) ? $this->parseDate($row['data_nascita']) : (isset($row['data_di_nascita']) ? $this->parseDate($row['data_di_nascita']) : null),
                'tax_id' => $cf,

                // Indirizzo
                'address' => $row['indirizzo'] ?? null,
                'city' => $row['citta'] ?? $row['città'] ?? null,
                'state' => $row['provincia'] ?? null,
                'postal_code' => $row['cap'] ?? null,

                // Status e classificazione
                'customer_status' => CustomerStatus::LEAD,
                'lead_source' => $leadSource,
                'lead_status' => $leadStatus,

                // Note
                'notes' => $row['note'] ?? null,
            ];

            $lead = $this->findOrCreateCustomer($row, $customerData);
            $this->createActivityLog($lead, 'import_success', 'Lead importato con successo', $row);
            return $lead;
        } catch (Exception $e) {
            $this->createActivityLog(null, 'import_failure', 'Errore durante l\'importazione del lead', $row, $e);
            Log::warning("Errore nell'import lead alla riga con nome '{$row['nome']} {$row['cognome']}': {$e->getMessage()}");
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
                lead: null,
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
            'provenienza_lead' => ['nullable', 'string', 'max:100'],
            'stato_lead' => ['nullable', 'string', 'max:100'],
            'tipologia_cliente' => ['nullable', 'string', 'max:255'],
            'collaboratore_associato' => ['nullable', 'string', 'max:255'],
            'indirizzo' => ['nullable', 'string', 'max:255'],
            'citta' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'cap' => ['nullable', 'string', 'max:20'],
            'note' => ['nullable', 'string', 'max:1000'],
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
     * Get user from row data
     */
    protected function getUser($row): User
    {
        // Se viene passato un utente di default per l'importazione, usalo
        if ($this->defaultUser) {
            return $this->defaultUser;
        }

        $userFullName = strtolower(preg_replace('/\s+/', ' ', trim($row['collaboratore_associato'] ?? '')));

        if ($userFullName) {
            $user = User::whereRaw("CONCAT(LOWER(first_name), ' ', LOWER(last_name)) LIKE ?", ['%' . $userFullName . '%'])
                ->first();

            if ($user) {
                return $user;
            }
        }

        // Fallback to superadmin
        return User::role('superadmin')->first();
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
     * Parse lead source from string
     */
    protected function parseLeadSource(?string $source): LeadSource
    {
        if (!$source) {
            return LeadSource::OTHER;
        }

        $normalized = strtolower(preg_replace('/\s+/', ' ', trim($source)));

        return match (true) {
            str_contains($normalized, 'tik tok') => LeadSource::TIK_TOK,
            str_contains($normalized, 'meta') => LeadSource::META,
            str_contains($normalized, 'motore di ricerca') => LeadSource::SEARCH_ENGINE,
            str_contains($normalized, 'referral') => LeadSource::REFERRAL,
            str_contains($normalized, 'altro') => LeadSource::OTHER,
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
     * Crea un log di attività per l'importazione.
     */
    protected function createActivityLog($lead, $logName, $message, $row = [], $e = null, $failures = null)
    {
        $properties = [
            'import_type' => 'leads',
            'raw_data' => $row,
            'file_name' => request()->file('file')?->getClientOriginalName(),
        ];

        if ($lead) {
            $properties = array_merge($properties, [
                'customer_name' => $lead->full_name,
                'email' => $lead->email,
                'import_action' => $lead->wasRecentlyCreated ? 'created' : 'updated',
                'url' => route('customer.show', $lead->id),
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
            ->when($lead, fn($activity) => $activity->performedOn($lead))
            ->causedBy(auth()->user())
            ->withProperties($properties)
            ->log($message);
    }
}
