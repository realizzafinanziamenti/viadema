<?php

namespace App\Livewire\Forms;

use App\Enums\LeadSource;
use App\Enums\PracticeStatus;
use App\Enums\ProductionType;
use App\Models\Customer;
use App\Models\Practice;
use App\Models\PracticeOpportunity;
use App\Models\User;
use App\Notifications\UserAddedToPractice;
use App\Traits\AcceptedFileTypes;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Livewire\Form;
use Masmerise\Toaster\Toaster;

class PracticeForm extends Form
{
    use AcceptedFileTypes;

    public ?string $acquisitionChannel = null;

    public ?Practice $practice = null;

    public ?PracticeOpportunity $opportunity = null;

    public $productTypeId = null;

    public $productSubtypeId = null;

    public $userId = null;

    public $customerId = null;

    public $financialTableId = null;

    public $insuranceId = null;

    public $installmentId = null;

    public $customerTypeId = null;

    public ?string $employmentStartDate = null;

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

    /**
     * Ensure that the customer does not already have
     * a practice for the selected product.
     */

     public const DUPLICATE_PRODUCT_MESSAGE =
    'Il cliente possiede già una pratica per questo prodotto.';

public function hasDuplicatePractice(): bool
{
    if (
        ! $this->customerId
        || ! $this->productTypeId
    ) {
        return false;
    }

    $query = Practice::query()
        ->forCustomerAndProduct(
            (int) $this->customerId,
            (int) $this->productTypeId
        );

    if ($this->practice !== null) {
        $query->where(
            'id',
            '!=',
            $this->practice->getKey()
        );
    }

    return $query->exists();
}
    private function ensurePracticeIsNotDuplicate(): void
    {
        $customer = Customer::query()
            ->whereKey($this->customerId)
            ->lockForUpdate()
            ->firstOrFail();

        if (
            ! $customer->isCustomer()
            || blank($customer->tax_id)
        ) {
            throw ValidationException::withMessages([
                'practiceForm.customerId' =>
                    'Il cliente deve avere un codice fiscale prima di creare una pratica.',
            ]);
        }

        $query = Practice::query()
            ->forCustomerAndProduct(
                $customer->getKey(),
                (int) $this->productTypeId
            );

        /*
         * During update, the current practice must not
         * be considered a duplicate of itself.
         */
        if ($this->practice !== null) {
            $query->where(
                'id',
                '!=',
                $this->practice->getKey()
            );
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'practiceForm.productTypeId' =>
                    self::DUPLICATE_PRODUCT_MESSAGE,
            ]);
        }
    }

    /**
     * Load an existing opportunity into the form.
     */
    public function setOpportunity(
        PracticeOpportunity $opportunity
    ): void {
        $this->opportunity = $opportunity;

        $this->fill([
            'acquisitionChannel' =>
                $opportunity->acquisition_channel?->value,

            'customerId' =>
                $opportunity->customer_id,

            'productTypeId' =>
                $opportunity->product_type_id,

            'productSubtypeId' =>
                $opportunity->product_subtype_id,

            'financialTableId' =>
                $opportunity->financial_table_id,

            'insuranceId' =>
                $opportunity->insurance_id,

            'installmentId' =>
                $opportunity->installment_id,

            'customerTypeId' =>
                $opportunity->customer_type_id,

            'employmentStartDate' =>
                $opportunity->employment_start_date?->format('Y-m-d'),

            'amountDisbursed' =>
                $opportunity->amount_disbursed,

            'totalAmount' =>
                $opportunity->total_amount,

            'rateAmount' =>
                $opportunity->rate_amount,

            'tan' =>
                $opportunity->tan,

            'teg' =>
                $opportunity->teg,

            'taeg' =>
                $opportunity->taeg,

            'firstInstallmentDate' =>
                $opportunity->first_installment_date?->format('Y-m-d'),

            'lastInstallmentDate' =>
                $opportunity->last_installment_date?->format('Y-m-d'),

            'renewabilityPercentage' =>
                $opportunity->renewability_percentage,

            'percentageAlert' =>
                $opportunity->percentage_alert,

            'isRenewal' =>
                (bool) $opportunity->is_renewal,

            'productionType' =>
                $opportunity->production_type?->value,

            'disbursingInstitution' =>
                $opportunity->disbursing_institution,

            'financialInstitution' =>
                $opportunity->financial_institution,

            'previousFinance' =>
                $opportunity->previous_finance,

            'notes' =>
                $opportunity->notes,
        ]);
    }

    protected function rules(): array
    {
        return array_merge(
            [
                'acquisitionChannel' => [
                    'nullable',
                    'string',
                    new Enum(LeadSource::class),
                ],

                'productTypeId' => [
                    'required',
                    'exists:product_types,id',
                ],

                'productSubtypeId' => [
                    'nullable',
                    'exists:product_subtypes,id',
                ],

                'customerId' => [
                    'required',
                    'exists:customers,id',
                ],

                'financialTableId' => [
                    'nullable',
                    'exists:financial_tables,id',
                ],

                'insuranceId' => [
                    'nullable',
                    'exists:insurances,id',
                ],

                'installmentId' => [
                    'nullable',
                    'exists:installments,id',
                ],

                'customerTypeId' => [
                    'nullable',
                    'exists:customer_types,id',
                ],

                'employmentStartDate' => [
                    'nullable',
                    'date',
                ],

                'amountDisbursed' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                ],

                'totalAmount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                ],

                'rateAmount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                ],

                'tan' => [
                    'nullable',
                    'numeric',
                    'between:0,10000',
                ],

                'teg' => [
                    'nullable',
                    'numeric',
                    'between:0,10000',
                ],

                'taeg' => [
                    'nullable',
                    'numeric',
                    'between:0,10000',
                ],

                'insertedAt' => [
                    'nullable',
                    'date',
                ],

                'firstInstallmentDate' => [
                    'required',
                    'date',
                ],

                'lastInstallmentDate' => [
                    'nullable',
                    'date',
                ],

                'renewabilityDate' => [
                    'nullable',
                    'date',
                ],

                'renewabilityPercentage' => [
                    'nullable',
                    'numeric',
                    'between:0,100',
                ],

                'percentageAlert' => [
                    'nullable',
                    'numeric',
                    'between:0,100',
                ],

                'practiceStatus' => [
                    'required',
                    'string',
                    new Enum(PracticeStatus::class),
                ],

                'previousFinance' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'isRenewal' => [
                    'nullable',
                    'boolean',
                ],

                'productionType' => [
                    'nullable',
                    'string',
                    new Enum(ProductionType::class),
                ],

                'disbursingInstitution' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'financialInstitution' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:65535',
                ],

                'attachments' => [
                    'nullable',
                    'array',
                    'max:10',
                ],

                'attachments.*' => [
                    'nullable',
                    'file',
                    'mimetypes:' . implode(
                        ',',
                        $this->acceptedFileTypesArray()
                    ),
                    'max:10240',
                ],
            ],
            $this->userIdRules()
        );
    }

    /**
     * User assignment rules.
     */
    protected function userIdRules(): array
    {
        return [
            'userId' => [
                'nullable',
                'exists:users,id',
            ],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'acquisitionChannel' =>
                'canale di acquisizione',

            'productTypeId' =>
                'prodotto',

            'productSubtypeId' =>
                'tipo prodotto',

            'userId' =>
                'collaboratore',

            'customerId' =>
                'cliente',

            'financialTableId' =>
                'tabella provvigione',

            'insuranceId' =>
                'assicurazione',

            'installmentId' =>
                'numero rate',

            'customerTypeId' =>
                'tipologia cliente',

            'employmentStartDate' =>
                'data di assunzione',

            'amountDisbursed' =>
                'finanziato',

            'totalAmount' =>
                'totale dovuto',

            'rateAmount' =>
                'importo rata',

            'tan' =>
                'TAN',

            'teg' =>
                'TEG',

            'taeg' =>
                'TAEG',

            'insertedAt' =>
                'data inserimento sistema',

            'firstInstallmentDate' =>
                'data di inizio',

            'lastInstallmentDate' =>
                'data di fine',

            'renewabilityDate' =>
                'data rinnovabilità',

            'renewabilityPercentage' =>
                'percentuale di ammortamento per il rinnovo',

            'percentageAlert' =>
                'percentuale di ammortamento per alert',

            'practiceStatus' =>
                'stato pratica',

            'previousFinance' =>
                'finanziaria estinta',

            'isRenewal' =>
                'rinnovo',

            'productionType' =>
                'produzione',

            'disbursingInstitution' =>
                'ente erogante',

            'financialInstitution' =>
                'istituto finanziario',

            'notes' =>
                'note',

            'attachments' =>
                'allegati',

            'attachments.*' =>
                'file allegato',
        ];
    }

    /**
     * Load an existing practice into the form.
     */
    public function setPractice(Practice $practice): void
    {
        $this->practice = $practice;

        $practice->loadMissing('opportunity');

        $opportunity = $practice->opportunity;

        $this->opportunity = $opportunity;

        $this->fill([
            'acquisitionChannel' =>
                $opportunity?->acquisition_channel?->value,

            'productTypeId' =>
                $opportunity?->product_type_id,

            'productSubtypeId' =>
                $opportunity?->product_subtype_id,

            'financialTableId' =>
                $opportunity?->financial_table_id,

            'insuranceId' =>
                $opportunity?->insurance_id,

            'installmentId' =>
                $opportunity?->installment_id,

            'customerTypeId' =>
                $opportunity?->customer_type_id,

            'employmentStartDate' =>
                $opportunity?->employment_start_date?->format('Y-m-d'),

            'amountDisbursed' =>
                $opportunity?->amount_disbursed,

            'totalAmount' =>
                $opportunity?->total_amount,

            'rateAmount' =>
                $opportunity?->rate_amount,

            'tan' =>
                $opportunity?->tan,

            'teg' =>
                $opportunity?->teg,

            'taeg' =>
                $opportunity?->taeg,

            'firstInstallmentDate' =>
                $opportunity?->first_installment_date?->format('Y-m-d'),

            'lastInstallmentDate' =>
                $opportunity?->last_installment_date?->format('Y-m-d'),

            'renewabilityPercentage' =>
                $opportunity?->renewability_percentage,

            'percentageAlert' =>
                $opportunity?->percentage_alert,

            'isRenewal' =>
                $opportunity?->is_renewal ?? false,

            'productionType' =>
                $opportunity?->production_type?->value,

            'disbursingInstitution' =>
                $opportunity?->disbursing_institution,

            'financialInstitution' =>
                $opportunity?->financial_institution,

            'previousFinance' =>
                $opportunity?->previous_finance,

            'notes' =>
                $opportunity?->notes,

            // Fields belonging directly to Practice.
            'userId' =>
                $practice->user_id,

            'customerId' =>
                $practice->customer_id,

            'insertedAt' =>
                $practice->inserted_at?->format('Y-m-d'),

            'renewabilityDate' =>
                $practice->renewability_date?->format('Y-m-d'),

            'practiceStatus' =>
                $practice->practice_status?->value,
        ]);
    }

    /**
     * Store practice.
     */
    public function store(): ?Practice
    {
        $this->validate();

        try {
            $practice = DB::transaction(function (): Practice {
                $this->ensurePracticeIsNotDuplicate();

                $opportunity = $this->opportunity;

                if ($opportunity) {
                    $opportunity->update(
                        $this->opportunityData()
                    );
                } else {
                    $opportunity = PracticeOpportunity::create(
                        $this->opportunityData()
                    );
                }

                $practice = Practice::create(
                    array_merge(
                        $this->practiceData(),
                        [
                            'practice_opportunity_id' =>
                                $opportunity->getKey(),
                        ]
                    )
                );

                foreach ($this->attachments as $attachment) {
                    $practice->attachments()->create([
                        'file_name' =>
                            $attachment->getClientOriginalName(),

                        'file_path' =>
                            $attachment->store(
                                'practice-attachments',
                                'public'
                            ),

                        'mime_type' =>
                            $attachment->getClientMimeType(),

                        'file_size' =>
                            $attachment->getSize(),
                    ]);
                }

                if ($practice->user_id) {
                    $assignedUser = User::find(
                        $practice->user_id
                    );

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

            Toaster::success(
                'Pratica salvata con successo'
            );

            return $practice;
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error(
                'Errore durante il salvataggio della pratica: '
                . $e->getMessage()
            );

            Toaster::error(
                'Errore durante il salvataggio della pratica: '
                . $e->getMessage()
            );

            return null;
        }
    }

    /**
     * Update practice.
     */
    public function update(): Practice|false
    {
        $this->validate();

        try {
            DB::transaction(function (): void {
                $this->ensurePracticeIsNotDuplicate();

                $oldUserId = $this->practice->user_id;

                $this->practice->loadMissing(
                    'opportunity'
                );

                $opportunity =
                    $this->practice->opportunity;

                if ($opportunity) {
                    $opportunity->update(
                        $this->opportunityData()
                    );
                } else {
                    $opportunity = PracticeOpportunity::create(
                        $this->opportunityData()
                    );
                }

                $this->practice->update(
                    array_merge(
                        $this->practiceData(),
                        [
                            'practice_opportunity_id' =>
                                $opportunity->getKey(),
                        ]
                    )
                );

                foreach ($this->attachments as $attachment) {
                    $this->practice
                        ->attachments()
                        ->create([
                            'file_name' =>
                                $attachment->getClientOriginalName(),

                            'file_path' =>
                                $attachment->store(
                                    'practice-attachments',
                                    'public'
                                ),

                            'mime_type' =>
                                $attachment->getClientMimeType(),

                            'file_size' =>
                                $attachment->getSize(),
                        ]);
                }

                if (
                    $oldUserId !== $this->practice->user_id
                    && $this->practice->user_id
                ) {
                    $assignedUser = User::find(
                        $this->practice->user_id
                    );

                    if ($assignedUser) {
                        Notification::send(
                            $assignedUser,
                            new UserAddedToPractice(
                                $this->practice
                            )
                        );
                    }
                }
            });

            Toaster::success(
                'Pratica aggiornata con successo'
            );

            return $this->practice;
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error(
                'Errore durante l\'aggiornamento della pratica: '
                . $e->getMessage()
            );

            Toaster::error(
                'Errore durante l\'aggiornamento della pratica: '
                . $e->getMessage()
            );

            return false;
        }
    }

    /**
     * Normalize a nullable numeric value.
     */
    private function nullableNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) str_replace(
            ',',
            '.',
            (string) $value
        );
    }

    /**
     * Practice opportunity data.
     */
    private function opportunityData(): array
    {
        return [
            'acquisition_channel' =>
                $this->acquisitionChannel ?: null,

            'customer_id' =>
                $this->customerId,

            'product_type_id' =>
                $this->productTypeId,

            'product_subtype_id' =>
                $this->productSubtypeId ?: null,

            'financial_table_id' =>
                $this->financialTableId ?: null,

            'insurance_id' =>
                $this->insuranceId ?: null,

            'installment_id' =>
                $this->installmentId ?: null,

            'customer_type_id' =>
                $this->customerTypeId ?: null,

            'employment_start_date' =>
                $this->employmentStartDate ?: null,

            'amount_disbursed' =>
                $this->nullableNumber(
                    $this->amountDisbursed
                ),

            'total_amount' =>
                $this->nullableNumber(
                    $this->totalAmount
                ),

            'rate_amount' =>
                $this->nullableNumber(
                    $this->rateAmount
                ),

            'tan' =>
                $this->nullableNumber(
                    $this->tan
                ),

            'teg' =>
                $this->nullableNumber(
                    $this->teg
                ),

            'taeg' =>
                $this->nullableNumber(
                    $this->taeg
                ),

            'first_installment_date' =>
                $this->firstInstallmentDate ?: null,

            'last_installment_date' =>
                $this->lastInstallmentDate ?: null,

            'renewability_percentage' =>
                $this->nullableNumber(
                    $this->renewabilityPercentage
                ) ?? 40.00,

            'percentage_alert' =>
                $this->nullableNumber(
                    $this->percentageAlert
                ) ?? 35.00,

            'is_renewal' =>
                $this->isRenewal,

            'production_type' =>
                $this->productionType ?: null,

            'disbursing_institution' =>
                $this->disbursingInstitution ?: null,

            'financial_institution' =>
                $this->financialInstitution ?: null,

            'previous_finance' =>
                $this->previousFinance ?: null,

            'notes' =>
                $this->notes ?: null,
        ];
    }

    /**
     * Practice-specific data.
     */
    private function practiceData(): array
    {
        return [
            'user_id' =>
                $this->userId ?: Auth::id(),

            'customer_id' =>
                $this->customerId,

            'inserted_at' =>
                $this->insertedAt ?? now(),

            'renewability_date' =>
                $this->renewabilityDate,

            'practice_status' =>
                $this->practiceStatus,
        ];
    }
}