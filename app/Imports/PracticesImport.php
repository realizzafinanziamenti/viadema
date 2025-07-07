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
use Demo\Product;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;

class PracticesImport implements ToModel, WithHeadingRow, SkipsOnFailure, ShouldQueue, WithChunkReading
{
    use SkipsFailures;

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
            $customer = $this->setCustomer($row);
            // Imposta il tipo di prodotto
            $product = $this->setProduct($row);
            // cerca il tipo di prodotto corrispondente
            $productSubtype = getProductSubtype($row);
            // Se la data di estinzione è presente, la pratica è considerata liquidata
            // Altrimenti, è in fase di revisione
            $practiceStatus = $row['data_liquidazione'] ? PracticeStatus::DISBURSED->value : PracticeStatus::UNDER_REVIEW->value;
            // cerca l'installment corrispondente
            $installment = Installment::where('value', $row['numero_rate'])->first();
            // Recupera l'assicurazione corrispondente, se esiste
            $insurance = getInsurance($row);
            // cerca il tipo di cliente corrispondente
            $customerType = getCustomerType($row);
            // Recupera i valori di rinnovabilità e percentuale di avviso predefiniti
            $installmentProductDefault = $this->getRenewabilityAndAlertDefaultPercentage($product, $installment);

            return new Practice([
                'product_type_id'      => $product->id,
                'product_subtype_id'      => $$productSubtype->id ?? null,
                'user_id'              => $user->id,
                'customer_id'          => $customer->id ?? null,
                'financial_table_id'   => null,
                'insurance_id'         => $insurance->id ?? null,
                'installment_id'       => $installment->id ?? null,
                'customer_type_id'     => $customerType->id ?? null,

                'product_subtype_label' => $productSubtype->name ?? $row['tipo_prodotto'] ?? null,
                'financial_table_percentage' => $row['tabella_finanziaria'] ?? null,
                'insurance_label'      => $row['assicurazione'] ?? null,
                'installment_value_label' => $row['numero_rate'] ?? null,
                'customer_type_label'  => $customerType->name ?? $row['tipo_cliente'] ?? null,

                'amount_disbursed'   => $row['finanziato'] ?? null,
                'total_amount'       => $row['montante'] ?? null,
                'rate_amount'        => $row['importo_rata'] ?? null,
                'tan'                => $row['tan'] ?? null,
                'teg'                => $row['teg'] ?? null,
                'taeg'               => $row['taeg'] ?? null,

                'inserted_at' => $this->parseDate($row['data_inserimento']) ?? now(),
                'first_installment_date' => $this->parseDate($row['data_prima_rata']) ?? null,
                'last_installment_date' => $this->parseDate($row['data_ultima_rata']) ?? null,

                'renewability_percentage' => $installmentProductDefault->renewability_percentage ?? 40.00,
                'percentage_alert'        => $installmentProductDefault->percentage_alert ?? 35.00,

                'practice_status' => $practiceStatus,
                'practice_code' => $row['pratica'],
                'notes' => null,

                'days_transformation' => $row['trasformazione_gg'] ?? null,
                'sum_dec_plus_35'     => $row['somma_dec_35'] ?? null,
            ]);
        } catch (Exception $e) {
            Log::channel('import')->warning("Errore alla riga con pratica {$row['pratica']}: {$e->getMessage()}");
            return null;
        }
    }

    public function failed(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::channel('import')->warning("Import fallito alla riga {$failure->row()}: " . implode(', ', $failure->errors()));
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return [
            'cognome_nome_cliente' => ['required', 'string', 'max:255'],
            'codice_fiscale' => ['required', 'string', 'max:16'],
            'tipo_prodotto' => ['required', 'string', 'max:255'],
            'numero_rate' => ['required', 'integer'],
            'tipo_cliente' => ['nullable', 'string', 'max:255'],

            'finanziato' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'montante' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'importo_rata' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'tan' => ['required', 'numeric', 'between:0,10000'],
            'teg' => ['nullable', 'numeric', 'between:0,10000'],
            'taeg' => ['required', 'numeric', 'between:0,10000'],

            'data_inserimento' => ['nullable', 'date'],
            'data_prima_rata' => ['required', 'date'],
            'data_ultima_rata' => ['required', 'date'],

            'trasformazione_gg' => ['nullable', 'integer'],
            'somma_dec_35' => ['nullable', 'numeric'],

            'pratica' => ['required', 'string', Rule::unique('practices', 'practice_code')],
        ];
    }

    protected function parseDate($value)
    {
        if (!$value) return null;
        try {
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
        $userFullName = strtolower(trim($row['nome_agenzia']));

        $user = User::whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $userFullName . '%'])
            ->first();

        return $user ?? User::where('role', 'superadmin')->first(); // Fallback to superadmin if no user found
    }

    /**
     * Crea o aggiorna il cliente in base ai dati della riga.
     *
     * @param array $row
     * @return Customer
     */
    protected function setCustomer($row): Customer
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
                    'user_id' => 1, // Assumendo che l'utente sia sempre 1 per l'importazione
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
                'user_id' => 1,
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
     * @return Product
     */
    protected function setProduct($row): Product
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
    protected function getRenewabilityAndAlertDefaultPercentage(Product $product, ?Installment $installment): ?InstallmentProductDefault
    {
        if (!$installment) {
            return null;
        }

        $installmentProductDefault = InstallmentProductDefault::where('product_type_id', $product->id)
            ->where('installment_id', $installment->id ?? null)
            ->first();

        return $installmentProductDefault;
    }
}
