<?php

namespace App\Livewire\Forms;
use App\Models\PracticeOpportunity;
use App\Enums\PracticeStatus;
use App\Enums\ProductionType;
use App\Models\Practice;
use App\Models\User;
use App\Notifications\UserAddedToPractice;
use App\Traits\AcceptedFileTypes;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\Auth;
class PracticeForm extends Form
{
    use AcceptedFileTypes;

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
    public $firstInstallmentDate = null;
    public $lastInstallmentDate = null;
    public $renewabilityPercentage = null;
    public $percentageAlert = null;
    public $renewabilityDate = null;
    public $practiceStatus = null;
    public $previousFinance = null;
    public bool $isRenewal = false;
    public $productionType = null;
    public $disbursingInstitution = null;
    public $financialInstitution = null;
    public $notes = null;
    public array $attachments = [];

    protected function rules(): array
    {
        return array_merge(
            [
                'productTypeId' => ['nullable', 'exists:product_types,id'],
                'productSubtypeId' => ['nullable', 'exists:product_subtypes,id'],
                'customerId' => ['required', 'exists:customers,id'],
                'financialTableId' => ['nullable', 'exists:financial_tables,id'],
                'insuranceId' => ['nullable', 'exists:insurances,id'],
                'installmentId' => ['nullable', 'exists:installments,id'],
                'customerTypeId' => ['nullable', 'exists:customer_types,id'],
                'amountDisbursed' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
                'totalAmount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
                'rateAmount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
                'tan' => ['nullable', 'numeric', 'between:0,10000'],
                'teg' => ['nullable', 'numeric', 'between:0,10000'],
                'taeg' => ['nullable', 'numeric', 'between:0,10000'],
                'insertedAt' => ['nullable', 'date'],
                'firstInstallmentDate' => ['required', 'date'],
                'lastInstallmentDate' => ['nullable', 'date'],
                'renewabilityDate' => ['nullable', 'date'],
                'renewabilityPercentage' => ['nullable', 'numeric', 'between:0,100'],
                'percentageAlert' => ['nullable', 'numeric', 'between:0,100'],
                'practiceStatus' => ['required', 'string', new Enum(PracticeStatus::class)],
                'previousFinance' => ['nullable', 'string', 'max:255'],
                'isRenewal' => ['nullable', 'boolean'],
                'productionType' => ['nullable', 'string', new Enum(ProductionType::class)],
                'disbursingInstitution' => ['nullable', 'string', 'max:255'],
                'financialInstitution' => ['nullable', 'string', 'max:255'],
                'notes' => ['nullable', 'string', 'max:65535'],
                'attachments' => ['nullable', 'array', 'max:10'],
                'attachments.*' => ['nullable', 'file', 'mimetypes:' . implode(',', $this->acceptedFileTypesArray()), 'max:10240']
            ],
            $this->userIdRules()
        );
    }

    /**
     * userId rules
     * if user is not allowed to assign practice to user, assign practice to current user
     */
    protected function userIdRules(): array
{
    return [
        'userId' => ['nullable', 'exists:users,id'],
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
            'firstInstallmentDate' => "data di inizio",
            'lastInstallmentDate' => "data di fine",
            'renewabilityDate' => "data rinnovabilità",
            'renewabilityPercentage' => "percentuale di ammortamento per il rinnovo",
            'percentageAlert' => "percentuale di ammortamento per alert",
            'practiceStatus' => "stato pratica",
            'previousFinance' => "finanziaria estinta",
            'isRenewal' => "rinnovo",
            'productionType' => "produzione",
            'disbursingInstitution' => "ente erogante",
            'financialInstitution' => "istituto finanziario",
            'notes' => "note",
            'attachments' => 'allegati',
            'attachments.*' => 'file allegato'
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
            'insertedAt' => $practice->inserted_at?->format('Y-m-d'),
            'firstInstallmentDate' => $practice->first_installment_date?->format('Y-m-d'),
            'lastInstallmentDate' => $practice->last_installment_date?->format('Y-m-d'),
            'renewabilityDate' => $practice->renewability_date?->format('Y-m-d'),
            'renewabilityPercentage' => $practice->renewability_percentage,
            'percentageAlert' => $practice->percentage_alert,
            'practiceStatus' => $practice->practice_status?->value,
            'previousFinance' => $practice->previous_finance,
            'isRenewal' => $practice->is_renewal,
            'productionType' => $practice->production_type?->value,
            'disbursingInstitution' => $practice->disbursing_institution,
            'financialInstitution' => $practice->financial_institution,
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
            $practice = DB::transaction(function () {
                $opportunity = PracticeOpportunity::create($this->opportunityData());

                $practice = Practice::create(array_merge(
                    $this->practiceData(),
                    ['practice_opportunity_id' => $opportunity->id]
                ));

                foreach ($this->attachments as $attachment) {
                    $practice->attachments()->create([
                        'file_name' => $attachment->getClientOriginalName(),
                        'file_path' => $attachment->store('practice-attachments', 'public'),
                        'mime_type' => $attachment->getClientMimeType(),
                        'file_size' => $attachment->getSize()
                    ]);
                }

               // notify assigned user only if the practice has an assigned user
if ($practice->user_id) {
    $assignedUser = User::find($practice->user_id);

    if ($assignedUser) {
        Notification::send(
            $assignedUser,
            new UserAddedToPractice($practice)
        );
    }
}

return $practice;
            });


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
            DB::transaction(function () {
                // get old user id before update
                $oldUserId = $this->practice->user_id;

                $this->practice->loadMissing('opportunity');
                $this->practice->update($this->practiceData());
                $this->practice->opportunity->update($this->opportunityData());

                foreach ($this->attachments as $attachment) {
                    $this->practice->attachments()->create([
                        'file_name' => $attachment->getClientOriginalName(),
                        'file_path' => $attachment->store('practice-attachments', 'public'),
                        'mime_type' => $attachment->getClientMimeType(),
                        'file_size' => $attachment->getSize()
                    ]);
                }

                // if user changed, notify new user only if the practice has an assigned user
if ($oldUserId !== $this->practice->user_id && $this->practice->user_id) {
    $assignedUser = User::find($this->practice->user_id);

    if ($assignedUser) {
        Notification::send(
            $assignedUser,
            new UserAddedToPractice($this->practice)
        );
    }
}
            });

            Toaster::success('Pratica aggiornata con successo');
            return $this->practice;
        } catch (Exception $e) {
            Log::error('Errore durante l\'aggiornamento della pratica: ' . $e->getMessage());
            Toaster::error('Errore durante l\'aggiornamento della pratica: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * practice data
     */
    private function nullableNumber($value): ?float
{
    if ($value === null || $value === '') {
        return null;
    }

    return (float) str_replace(',', '.', (string) $value);
}
private function opportunityData(): array
{
    return [
        'customer_id' => $this->customerId,
        'product_type_id' => $this->productTypeId,
        'product_subtype_id' => $this->productSubtypeId ?: null,
        'financial_table_id' => $this->financialTableId ?: null,
        'insurance_id' => $this->insuranceId ?: null,
        'installment_id' => $this->installmentId ?: null,
        'customer_type_id' => $this->customerTypeId ?: null,

        'amount_disbursed' => $this->nullableNumber($this->amountDisbursed),
        'total_amount' => $this->nullableNumber($this->totalAmount),
        'rate_amount' => $this->nullableNumber($this->rateAmount),

        'tan' => $this->nullableNumber($this->tan),
        'teg' => $this->nullableNumber($this->teg),
        'taeg' => $this->nullableNumber($this->taeg),

        'first_installment_date' => $this->firstInstallmentDate ?: null,
        'last_installment_date' => $this->lastInstallmentDate ?: null,

        'renewability_percentage' => $this->nullableNumber($this->renewabilityPercentage) ?? 40.00,
        'percentage_alert' => $this->nullableNumber($this->percentageAlert) ?? 35.00,

        'is_renewal' => $this->isRenewal,
        'production_type' => $this->productionType ?: null,

        'disbursing_institution' => $this->disbursingInstitution ?: null,
        'financial_institution' => $this->financialInstitution ?: null,
        'previous_finance' => $this->previousFinance ?: null,

        'notes' => $this->notes ?: null,
    ];
}
    private function practiceData(): array
    {
        return [
            // if user is not allowed to assign practice to user, assign practice to current user
            'user_id' => $this->userId ?: Auth::id(),            'product_type_id' => $this->productTypeId,
            'product_subtype_id' => $this->productSubtypeId ?? null,
            'customer_id' => $this->customerId,
            'financial_table_id' => $this->financialTableId ?? null,
            'insurance_id' => $this->insuranceId ?? null,
            'installment_id' => $this->installmentId,
            'customer_type_id' => $this->customerTypeId ?? null,
            'amount_disbursed' => $this->nullableNumber($this->amountDisbursed),
            'total_amount' => $this->nullableNumber($this->totalAmount),
            'rate_amount' => $this->nullableNumber($this->rateAmount),
            'tan' => $this->nullableNumber($this->tan),
            'teg' => $this->nullableNumber($this->teg),
            'taeg' => $this->nullableNumber($this->taeg),
            'inserted_at' => $this->insertedAt ?? now(),  // in upload it use old date, instead in create it uses now()
            'first_installment_date' => $this->firstInstallmentDate,
            'last_installment_date' => $this->lastInstallmentDate,
            'renewability_date' => $this->renewabilityDate,
            'renewability_percentage' => $this->nullableNumber($this->renewabilityPercentage) ?? 40.00,
            'percentage_alert' => $this->nullableNumber($this->percentageAlert) ?? 35.00,
            'practice_status' => $this->practiceStatus,
            'previous_finance' => $this->previousFinance ?? null,
            'is_renewal' => $this->isRenewal,
            'production_type' => $this->productionType ?? null,
            'disbursing_institution' => $this->disbursingInstitution ?? null,
            'financial_institution' => $this->financialInstitution ?? null,
            'notes' => $this->notes
        ];
    }
}