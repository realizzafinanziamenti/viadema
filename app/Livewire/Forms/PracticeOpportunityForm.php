<?php

namespace App\Livewire\Forms;

use App\Enums\LeadSource;
use App\Enums\ProductionType;
use App\Models\Customer;
use App\Models\PracticeOpportunity;
use Livewire\Form;
use Illuminate\Validation\Rules\Enum;

class PracticeOpportunityForm extends Form
{
    public ?PracticeOpportunity $opportunity = null;
    public ?string $acquisitionChannel = null;

    public $productTypeId = null;
    public $productSubtypeId = null;
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

    public $firstInstallmentDate = null;
    public $lastInstallmentDate = null;

    public $renewabilityPercentage = null;
    public $percentageAlert = null;

    public bool $isRenewal = false;
    public $productionType = null;

    public $disbursingInstitution = null;
    public $financialInstitution = null;
    public $previousFinance = null;

    public $notes = null;

    protected function rules(): array
    {
        return [
            'acquisitionChannel' => [
                'nullable',
                'string',
                new Enum(LeadSource::class),
            ],
            'productTypeId' => ['nullable', 'exists:product_types,id'],
            'productSubtypeId' => ['nullable', 'exists:product_subtypes,id'],
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

            'firstInstallmentDate' => ['nullable', 'date'],
            'lastInstallmentDate' => ['nullable', 'date'],

            'renewabilityPercentage' => ['nullable', 'numeric', 'between:0,100'],
            'percentageAlert' => ['nullable', 'numeric', 'between:0,100'],

            'isRenewal' => ['nullable', 'boolean'],
            'productionType' => ['nullable', 'string', new Enum(ProductionType::class)],

            'disbursingInstitution' => ['nullable', 'string', 'max:255'],
            'financialInstitution' => ['nullable', 'string', 'max:255'],
            'previousFinance' => ['nullable', 'string', 'max:255'],

            'notes' => ['nullable', 'string', 'max:65535'],
        ];
    }

    public function setOpportunity(?PracticeOpportunity $opportunity): void
    {
        $this->opportunity = $opportunity;

        if (! $opportunity) {
            return;
        }

        $this->acquisitionChannel = $opportunity->acquisition_channel?->value;
        $this->productTypeId = $opportunity->product_type_id;
        $this->productSubtypeId = $opportunity->product_subtype_id;
        $this->financialTableId = $opportunity->financial_table_id;
        $this->insuranceId = $opportunity->insurance_id;
        $this->installmentId = $opportunity->installment_id;
        $this->customerTypeId = $opportunity->customer_type_id;

        $this->amountDisbursed = $opportunity->amount_disbursed;
        $this->totalAmount = $opportunity->total_amount;
        $this->rateAmount = $opportunity->rate_amount;

        $this->tan = $opportunity->tan;
        $this->teg = $opportunity->teg;
        $this->taeg = $opportunity->taeg;

        $this->firstInstallmentDate = $opportunity->first_installment_date?->format('Y-m-d');
        $this->lastInstallmentDate = $opportunity->last_installment_date?->format('Y-m-d');

        $this->renewabilityPercentage = $opportunity->renewability_percentage;
        $this->percentageAlert = $opportunity->percentage_alert;

        $this->isRenewal = (bool) $opportunity->is_renewal;
        $this->productionType = $opportunity->production_type?->value;

        $this->disbursingInstitution = $opportunity->disbursing_institution;
        $this->financialInstitution = $opportunity->financial_institution;
        $this->previousFinance = $opportunity->previous_finance;

        $this->notes = $opportunity->notes;
    }

    public function store(Customer $customer): PracticeOpportunity
    {
        $this->validate();

        return PracticeOpportunity::create(array_merge(
            $this->data(),
            ['customer_id' => $customer->id]
        ));
    }

    public function updateOrCreate(Customer $customer): PracticeOpportunity
    {
        $this->validate();

        if ($this->opportunity) {
            $this->opportunity->update($this->data());
            return $this->opportunity;
        }

        return $this->store($customer);
    }

    private function data(): array
    {
        return [
            'acquisition_channel' => $this->acquisitionChannel ?: null,
            'product_type_id' => $this->productTypeId ?: null,
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

            'renewability_percentage' => $this->nullableNumber($this->renewabilityPercentage) ?? 40,
            'percentage_alert' => $this->nullableNumber($this->percentageAlert) ?? 35,

            'is_renewal' => $this->isRenewal,
            'production_type' => $this->productionType ?: null,

            'disbursing_institution' => $this->disbursingInstitution ?: null,
            'financial_institution' => $this->financialInstitution ?: null,
            'previous_finance' => $this->previousFinance ?: null,

            'notes' => $this->notes ?: null,
        ];
    }

    private function nullableNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) str_replace(',', '.', (string) $value);
    }
}