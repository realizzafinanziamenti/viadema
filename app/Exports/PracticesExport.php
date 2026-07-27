<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PracticesExport extends StringValueBinder implements
    FromQuery,
    ShouldAutoSize,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithCustomValueBinder
{
    public function __construct(protected $query) {}

    public function query()
    {
        return $this->query->with([
            'customer',
            'user',
            'opportunity.productType',
            'opportunity.productSubtype',
            'opportunity.installment',
            'opportunity.customerType',
            'opportunity.insurance',
            'opportunity.financialTable',
        ]);
    }

    public function headings(): array
    {
        return [
            'ID pratica',
            'Canale di acquisizione',
            'Prodotto',
            'Tipo prodotto',
            'Ente erogante',
            'Istituto finanziario',
            'Finanziaria estinta',
            'Stato',
            'Produzione',
            'Operatore',
            'Data inserimento',
            'Data di inizio',
            'Data di fine',
            'Data liquidazione',
            'Data estinzione anticipata',
            'Data rinnovabilità',
            'Data alert',
            'Importo finanziato',
            'Rate',
            'Rata mensile',
            'Taeg',
            'Tan',
            'Teg',
            'Totale dovuto',
            'Rinnovo',
            'Percentuale rinnovabilità',
            'Percentuale alert',
            'Tipologia cliente',
            'Assicurazione',
            'Tabella provvigionale',
            'Giorni trasformazione',
            'Somma DEC + 35',
            'Note pratica',
            'Cliente',
            'Email',
            'Telefono',
            'Indirizzo',
            'CAP',
            'Città',
            'Provincia',
            'Codice Fiscale',
        ];
    }

    public function map($practice): array
    {
        $opportunity = $practice->opportunity;

        return [
            $practice->practice_code ?? '',

            $opportunity?->acquisition_channel?->getLabelText() ?? '',
            $opportunity?->productType?->name ?? '',
            $opportunity?->productSubtype?->name ?? '',
            $opportunity?->disbursing_institution ?? '',
            $opportunity?->financial_institution ?? '',
            $opportunity?->previous_finance ?? '',

            $practice->practice_status?->getLabelText() ?? '',
            $opportunity?->production_type?->getLabelText() ?? '',

            $this->fullName(
                $practice->user?->first_name,
                $practice->user?->last_name
            ),

            $this->formatDate($practice->inserted_at),
            $this->formatDate($opportunity?->first_installment_date),
            $this->formatDate($opportunity?->last_installment_date),
            $this->formatDate($practice->disbursement_date),
            $this->formatDate($practice->early_settlement_date),
            $this->formatDate($practice->renewability_date),
            $this->formatDate($practice->alert_date),

            $this->formatMoney($opportunity?->amount_disbursed),
            $opportunity?->installment?->value ?? '',
            $this->formatMoney($opportunity?->rate_amount),

            $this->formatPercent($opportunity?->taeg),
            $this->formatPercent($opportunity?->tan),
            $this->formatPercent($opportunity?->teg),

            $this->formatMoney($opportunity?->total_amount),

            $opportunity
                ? ($opportunity->is_renewal ? 'Sì' : 'No')
                : '',

            $this->formatPercent(
                $opportunity?->renewability_percentage
            ),

            $this->formatPercent(
                $opportunity?->percentage_alert
            ),

            $opportunity?->customerType?->name ?? '',
            $opportunity?->insurance?->name ?? '',

            $this->formatPercent(
                $opportunity?->financialTable?->percentage
            ),

            $practice->days_transformation ?? '',
            $this->formatMoney($practice->sum_dec_plus_35),

            $opportunity?->notes ?? '',

            $this->fullName(
                $practice->customer?->first_name,
                $practice->customer?->last_name
            ),

            $practice->customer?->email ?? '',
            $practice->customer?->phone ?? '',
            $practice->customer?->address ?? '',
            $practice->customer?->postal_code ?? '',
            $practice->customer?->city ?? '',
            $practice->customer?->state ?? '',
            $practice->customer?->tax_id ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
            ],
        ];
    }

    private function fullName(
        ?string $firstName,
        ?string $lastName
    ): string {
        return trim(
            ($firstName ?? '') . ' ' . ($lastName ?? '')
        );
    }

    private function formatDate(mixed $value): string
    {
        if (!$value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable) {
            return '';
        }
    }

    private function formatMoney(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format(
            (float) $value,
            2,
            ',',
            '.'
        ) . '€';
    }

    private function formatPercent(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format(
            (float) $value,
            2,
            ',',
            '.'
        ) . '%';
    }
}