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
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LeadsImport implements ToModel, WithHeadingRow, SkipsOnFailure, ShouldQueue, WithChunkReading
{
    use SkipsFailures;

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

            // Parsing semplificato nome e cognome
            $firstName = trim($row['nome'] ?? '');
            $lastName = trim($row['cognome'] ?? '');

            // Validazione base: nome e cognome devono essere presenti
            if (empty($firstName) || empty($lastName)) {
                Log::warning("Saltata riga senza nome e cognome");
                return null;
            }

            $customerData = [
                'user_id' => $user->id,
                'customer_type_id' => $customerType?->id,

                // Dati anagrafici semplificati
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $this->cleanPhone($row['telefono']),
                'email' => $row['email'] ?? null,
                'date_of_birth' => $this->parseDate($row['data_nascita']) ?? null,
                'tax_id' => $row['codice_fiscale'] ?? null,

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

    public function failed(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->createActivityLog(null, 'import_validation_failure', 'Errore di validazione durante l\'importazione del lead', $failure->values(), null, $failure);
            Log::warning("Import lead fallito alla riga {$failure->row()}: " . implode(', ', $failure->errors()));
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return [
            'nome' => ['nullable', 'string', 'max:255'],
            'cognome' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'min:10', 'max:24'],
            'email' => ['nullable', 'email', 'max:255'],
            'codice_fiscale' => ['nullable', 'string', 'max:16'],
            'data_nascita' => ['nullable', 'date'],
            'provenienza_lead' => ['nullable', 'string', 'max:100'],
            'stato_lead' => ['nullable', 'string', 'max:100'],
            'tipologia_cliente' => ['nullable', 'string', 'max:255'],
            'collaboratore_associato' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Parse date from Excel
     */
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
     * Get user from row data
     */
    protected function getUser($row): User
    {
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
    protected function createActivityLog($lead, $logName, $message, $row = [], $e = null, $failure = null)
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
            ]);
        }

        if ($e) {
            $properties = array_merge($properties, [
                'error_message' => $e->getMessage(),
            ]);
        }

        if ($failure) {
            $properties = array_merge($properties, [
                'row_number' => $failure->row(),
                'validation_errors' => $failure->errors(),
                'failed_data' => $failure->values(),
            ]);
        }

        activity($logName)
            ->when($lead, fn($activity) => $activity->performedOn($lead))
            ->causedBy(auth()->user())
            ->withProperties($properties)
            ->log($message);
    }
}
