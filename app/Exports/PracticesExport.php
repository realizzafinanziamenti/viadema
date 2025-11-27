<?php

namespace App\Exports;

use App\Models\Practice;
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
        return $this->query;
    }

    /**
     * Intestazioni delle colonne
     */
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

    /**
     * Mappa i dati per ogni riga
     */
    public function map($practice): array
    {
        return [
            $practice->practice_code ?? '',
            $practice->productType?->name ?? '',
            $practice->disbursing_institution ?? '',
            $practice->financial_institution ?? '',
            $practice->practice_status?->getLabelText() ?? '',
            $practice->production_type?->getLabelText() ?? '',
            $practice->user?->first_name . ' ' . $practice->user?->last_name ?? '',
            $practice->formatted_first_installment_date ?? '',
            $practice->formatted_last_installment_date ?? '',
            $practice->formatted_early_settlement_date ?? '',
            $practice->formatted_amount_disbursed ?? '',
            $practice->installment?->value ?? ($practice->installment_value_label ?? ''),
            $practice->formatted_rate_amount ?? '',
            $practice->formatted_taeg ?? '',
            $practice->formatted_tan ?? '',
            $practice->formatted_total_amount ?? '',
            $practice->is_renewal ? 'Sì' : 'No',
            $practice->formatted_renewability_date ?? '',
            $practice->customerType?->name ?? '',
            $practice->insurance?->name ?? '',
            $practice->customer?->first_name . ' ' . $practice->customer?->last_name,
            $practice->customer?->email ?? '',
            $practice->customer?->phone ?? '',
            $practice->customer?->address ?? '',
            $practice->customer?->postal_code ?? '',
            $practice->customer?->city ?? '',
            $practice->customer?->state ?? '',
            $practice->customer?->tax_id ?? '',
        ];
    }

    /**
     * Stili Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
