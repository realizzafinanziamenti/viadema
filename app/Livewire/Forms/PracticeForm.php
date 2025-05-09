<?php

namespace App\Livewire\Forms;

use App\Enums\PracticeStatus;
use App\Models\Practice;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Masmerise\Toaster\Toaster;

class PracticeForm extends Form
{
    public ?Practice $practice = null;

    public $productTypeId = null;
    public $productSubtypeId = null;
    public $userId = null;
    public $customerId = null;
    public $financialTableId = null;
    public $insuranceId = null;
    public $installmentId = null;
    public $customerTypeId = null;
    public $amountDisbursed = null;
    public $totalAmount = null;
    public $rateAmount = null;
    public $tan = null;
    public $teg = null;
    public $taeg = null;
    public $insertedAt = null;
    public $startedAt = null;
    public $paidAt = null;
    public $firstDueDate = null;
    public $lastDueDate = null;
    public $extinguishedAt = null;
    public $renewableAt = null;
    public $practiceStatus = null;
    public $daysTransformation = null;
    public $sumDecPlus35 = null;
    public $previousFinance = null;
    public $practiceCode = null;
    public $notes = null;

    protected function rules(): array
    {
        return [
            'productTypeId' => ['required', 'exists:product_types,id'],
            'productSubtypeId' => ['nullable', 'exists:product_subtypes,id'],
            'userId' => ['required', 'exists:users,id'],
            'customerId' => ['required', 'exists:customers,id'],
            'financialTableId' => ['nullable', 'exists:financial_tables,id'],
            'insuranceId' => ['nullable', 'exists:insurances,id'],
            'installmentId' => ['nullable', 'exists:installments,id'],
            'customerTypeId' => ['nullable', 'exists:customer_types,id'],
            'amountDisbursed' => ['nullable', 'numeric'],
            'totalAmount' => ['nullable', 'numeric'],
            'rateAmount' => ['nullable', 'numeric'],
            'tan' => ['nullable', 'numeric'],
            'teg' => ['nullable', 'numeric'],
            'taeg' => ['nullable', 'numeric'],
            'insertedAt' => ['nullable', 'date'],
            'startedAt' => ['nullable', 'date'],
            'paidAt' => ['nullable', 'date'],
            // 'firstDueDate' => ['nullable', 'date'],
            // 'lastDueDate' => ['nullable', 'date'],
            // 'extinguishedAt' => ['nullable', 'date'],
            'renewableAt' => ['nullable', 'date'],
            'practiceStatus' => ['required', 'integer', new Enum(PracticeStatus::class)],
            // 'daysTransformation' => ['nullable', 'integer'],
            // 'sumDecPlus35' => ['nullable', 'numeric'],
            // 'previousFinance' => ['nullable', 'string'],
            'practiceCode' => ['required', 'string', 'unique:practices,practice_code'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'productTypeId' => 'prodotto',
            'productSubtypeId' => 'tipo prodotto',
            'userId' => 'collaboratore',
            'customerId' => 'cliente',
            'financialTableId' => 'tabella provvigione',
            'insuranceId' => 'assicurazione',
            'installmentId' => 'numero rate',
            'customerTypeId' => 'tipologia cliente',
            'amountDisbursed' => 'finanziato',
            'totalAmount' => 'totale dovuto',
            'rateAmount' => 'importo rata',
            'tan' => 'TAN',
            'teg' => 'TEG',
            'taeg' => 'TAEG',
            'insertedAt' => "data inserimento sistema",
            'startedAt' => "data decorrenza",
            'paidAt' => "data liquidazione",
            'firstDueDate' => "data prima rata",
            'lastDueDate' => "data ultima rata",
            'extinguishedAt' => "data estinzione anticipata",
            'renewableAt' => "data rinnovabilità",
            'practiceStatus' => "stato pratica",
            'daysTransformation' => "Trasformazione GG (differenza giorni?)",
            'sumDecPlus35' => "Somma dec + 35% (se utile davvero)",
            'previousFinance' => "finanziaria estinta",
            'practiceCode' => "ID pratica",
            'notes' => "note",
        ];
    }

    /**
     * Set practice for update
     */
    public function setPractice(Practice $practice): void
    {
        $this->practice = $practice;

        $this->fill([
            'productTypeId' => $practice->product_type_id,
            'productSubtypeId' => $practice->product_subtype_id,
            'userId' => $practice->user_id,
            'customerId' => $practice->customer_id,
            'financialTableId' => $practice->financial_table_id,
            'insuranceId' => $practice->insurance_id,
            'installmentId' => $practice->installment_id,
            'customerTypeId' => $practice->customer_type_id,
            'amountDisbursed' => $practice->amount_disbursed,
            'totalAmount' => $practice->total_amount,
            'rateAmount' => $practice->rate_amount,
            'tan' => $practice->tan,
            'teg' => $practice->teg,
            'taeg' => $practice->taeg,
            'insertedAt' => $practice->inserted_at,
            'startedAt' => $practice->started_at,
            'paidAt' => $practice->paid_at,
            'firstDueDate' => $practice->first_due_date,
            'lastDueDate' => $practice->last_due_date,
            'extinguishedAt' => $practice->extinguished_at,
            'renewableAt' => $practice->renewable_at,
            'practiceStatus' => $practice->practice_status,
            'daysTransformation' => $practice->days_transformation,
            'sumDecPlus35' => $practice->sum_dec_plus_35,
            'previousFinance' => $practice->previous_finance,
            'practiceCode' => $practice->practice_code,
            'notes' => $practice->notes
        ]);
    }

    /**
     * Store practice
     */
    public function store()
    {
        $this->validate();

        try {
            $practice = Practice::create([
                'product_type_id' => $this->productTypeId,
                'product_subtype_id' => $this->productSubtypeId,
                'user_id' => $this->userId,
                'customer_id' => $this->customerId,
                'financial_table_id' => $this->financialTableId,
                'insurance_id' => $this->insuranceId,
                'installment_id' => $this->installmentId,
                'customer_type_id' => $this->customerTypeId,
                'amount_disbursed' => $this->amountDisbursed,
                'total_amount' => $this->totalAmount,
                'rate_amount' => $this->rateAmount,
                'tan' => $this->tan,
                'taeg' => $this->taeg,
                'inserted_at' => now(),
                'started_at' => $this->startedAt,
                'paid_at' => $this->paidAt,
                'renewable_at' => $this->renewableAt,
                'practice_status' => $this->practiceStatus,    // default to UNDER_REVIEW
                'practice_code' => $this->practiceCode,
                'notes' => $this->notes
            ]);

            $this->reset();

            Toaster::success('Pratica salvata con successo');
            return $practice;
        } catch (Exception $e) {
            Log::error('Errore durante il salvataggio della pratica: ' . $e->getMessage());
            Toaster::error('Errore durante il salvataggio della pratica: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update practice
     */
    public function update()
    {
        $this->validate();

        try {
            $this->practice->update([
                'product_type_id' => $this->productTypeId,
                'product_subtype_id' => $this->productSubtypeId,
                'user_id' => $this->userId,
                'customer_id' => $this->customerId,
                'financial_table_id' => $this->financialTableId,
                'insurance_id' => $this->insuranceId,
                'installment_id' => $this->installmentId,
                'customer_type_id' => $this->customerTypeId,
                'amount_disbursed' => $this->amountDisbursed,
                'total_amount' => $this->totalAmount,
                'rate_amount' => $this->rateAmount,
                'tan' => $this->tan,
                'taeg' => $this->taeg,
                'inserted_at' => $this->practice->inserted_at,
                'started_at' => $this->startedAt,
                'paid_at' => $this->paidAt,
                'renewable_at' => $this->renewableAt,
                'practice_status' => $this->practiceStatus,
                'practice_code' => $this->practiceCode,
                'notes' => $this->notes
            ]);

            Toaster::success('Pratica aggiornata con successo');
            return true;
        } catch (Exception $e) {
            Log::error('Errore durante l\'aggiornamento della pratica: ' . $e->getMessage());
            Toaster::error('Errore durante l\'aggiornamento della pratica: ' . $e->getMessage());
            return false;
        }
    }
}
