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

class PracticesExport extends StringValueBinder implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithCustomValueBinder
{
    public function __construct(protected $query) {}

    public function query()
    {
        return $this->query->with([
            'customer',
            'user',
            'opportunity.productType',
            'opportunity.installment',
            'opportunity.customerType',
            'opportunity.insurance',
        ]);
    }

    public function headings(): array
    {
        return [
            'ID pratica',
            'Prodotto',
            'Ente erogante',
            'Istituto finanziario',
            'Stato',
            'Produzione',
            'Operatore',
            'Data di inizio',
            'Data di fine',
            'Data liquidazione',
            'Importo',
            'Rate',
            'Rata mensile',
            'Taeg',
            'Tan',
            'Totale dovuto',
            'Rinnovo',
            'Data rinnovabilità',
            'Tipologia cliente',
            'Assicurazione',
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
            $opportunity?->productType?->name ?? '',
            $opportunity?->disbursing_institution ?? '',
            $opportunity?->financial_institution ?? '',
            $practice->practice_status?->getLabelText() ?? '',
            $opportunity?->production_type?->getLabelText() ?? '',
            $this->fullName($practice->user?->first_name, $practice->user?->last_name),
            $this->formatDate($opportunity?->first_installment_date),
            $this->formatDate($opportunity?->last_installment_date),
            $this->formatDate($practice->disbursement_date),
            $this->formatMoney($opportunity?->amount_disbursed),
            $opportunity?->installment?->value ?? '',
            $this->formatMoney($opportunity?->rate_amount),
            $this->formatPercent($opportunity?->taeg),
            $this->formatPercent($opportunity?->tan),
            $this->formatMoney($opportunity?->total_amount),
            $opportunity ? ($opportunity->is_renewal ? 'Sì' : 'No') : '',
            $this->formatDate($practice->renewability_date),
            $opportunity?->customerType?->name ?? '',
            $opportunity?->insurance?->name ?? '',
            $this->fullName($practice->customer?->first_name, $practice->customer?->last_name),
            $practice->customer?->email ?? '',
            $practice->customer?->phone ?? '',
            $practice->customer?->address ?? '',
            $practice->customer?->postal_code ?? '',
            $practice->customer?->city ?? '',
            $practice->customer?->state ?? '',
            $practice->customer?->tax_id ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    private function fullName(?string $firstName, ?string $lastName): string
    {
        return trim(($firstName ?? '') . ' ' . ($lastName ?? ''));
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

        return number_format((float) $value, 2, ',', '.') . '€';
    }

    private function formatPercent(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format((float) $value, 2, ',', '.') . '%';
    }
}