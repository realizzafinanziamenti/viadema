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
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
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

            return Customer::updateOrCreate(
                [
                    // Trova per codice fiscale o email
                    'tax_id' => $row['codice_fiscale'] ?? null,
                    'email' => $row['email'] ?? null,
                ],
                [
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
                ]
            );
        } catch (Exception $e) {
            Log::warning("Errore nell'import lead alla riga con nome '{$row['nome']} {$row['cognome']}': {$e->getMessage()}");
            return null;
        }
    }

    public function failed(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::channel('import')->warning("Import lead fallito alla riga {$failure->row()}: " . implode(', ', $failure->errors()));
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
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')
            ],
            'codice_fiscale' => [
                'nullable',
                'string',
                'max:16',
                Rule::unique('customers', 'tax_id')
            ],
            'data_nascita' => ['nullable', 'date'],
            'provenienza_lead' => ['nullable', 'string', new Enum(LeadSource::class)],
            'stato_lead' => ['nullable', 'string', new Enum(LeadStatus::class)],
            'tipologia_cliente' => ['nullable', 'exists:customer_types,id'],
            'collaboratore_associato' => ['nullable', 'exists:users,id'],
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
        $userFullName = strtolower(trim($row['collaboratore_associato'] ?? ''));

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
        $customerTypeName = strtolower(trim($row['tipologia_cliente'] ?? ''));

        if (!$customerTypeName) {
            return null;
        }

        return CustomerType::whereRaw('LOWER(name) = ?', [$customerTypeName])
            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $customerTypeName . '%'])
            ->first();
    }

    /**
     * Parse lead source from string
     */
    protected function parseLeadSource(?string $source): LeadSource
    {
        if (!$source) {
            return LeadSource::OTHER;
        }

        $normalized = strtolower(trim($source));

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

        $normalized = strtolower(trim($status));

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
}
