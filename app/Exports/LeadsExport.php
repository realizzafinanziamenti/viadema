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

class LeadsExport extends StringValueBinder implements
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
            'customerType',
            'latestPracticeOpportunity.productType',
            'latestPracticeOpportunity.productSubtype',
            'latestPracticeOpportunity.installment',
            'latestPracticeOpportunity.customerType',
            'latestPracticeOpportunity.insurance',
            'latestPracticeOpportunity.financialTable',
        ]);
    }

    public function headings(): array
    {
        return [
            // Lead
            'Nome',
            'Tipologia cliente',
            'Stato lead',
            'Data di ricontatto',
            'Canale di acquisizione',
            'Telefono',
            'Email',
            'Data di nascita',
            'Indirizzo',
            'CAP',
            'Città',
            'Provincia',
            'Codice Fiscale',
            'Note lead',

            // Opportunity
            'Prodotto',
            'Tipo prodotto',
            'Ente erogante',
            'Istituto finanziario',
            'Produzione',
            'Data di inizio',
            'Data di fine',
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
            'Tipologia cliente pratica',
            'Assicurazione',
            'Tabella provvigionale',
            'Finanziaria estinta',
            'Note pratica',
        ];
    }

    public function map($lead): array
    {
        $opportunity = $lead->latestPracticeOpportunity;

        return [
            // Lead
            $this->fullName($lead->first_name, $lead->last_name),
            $lead->customerType?->name ?? '',
            $lead->lead_status?->getLabelText() ?? '',
            $this->formatDate($lead->recontact_date),
            $opportunity?->acquisition_channel?->getLabelText() ?? '',
            $lead->phone ?? '',
            $lead->email ?? '',
            $this->formatDate($lead->date_of_birth),
            $lead->address ?? '',
            $lead->postal_code ?? '',
            $lead->city ?? '',
            $lead->state ?? '',
            $lead->tax_id ?? '',
            $lead->notes ?? '',

            // Opportunity
            $opportunity?->productType?->name ?? '',
            $opportunity?->productSubtype?->name ?? '',
            $opportunity?->disbursing_institution ?? '',
            $opportunity?->financial_institution ?? '',
            $opportunity?->production_type?->getLabelText() ?? '',
            $this->formatDate($opportunity?->first_installment_date),
            $this->formatDate($opportunity?->last_installment_date),
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
            $this->formatPercent($opportunity?->renewability_percentage),
            $this->formatPercent($opportunity?->percentage_alert),
            $opportunity?->customerType?->name ?? '',
            $opportunity?->insurance?->name ?? '',
            $this->formatPercent(
                $opportunity?->financialTable?->percentage
            ),
            $opportunity?->previous_finance ?? '',
            $opportunity?->notes ?? '',
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