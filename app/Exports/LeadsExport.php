<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeadsExport extends StringValueBinder implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithCustomValueBinder
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
            'Nome',
            'Tipo',
            'Stato',
            'Canale di acquisizione',
            'Telefono',
            'Email',
            'Data di nascita',
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
    public function map($lead): array
    {
        return [
            $lead->first_name . ' ' . $lead->last_name,
            $lead->customerType?->name ?? '',
            $lead->lead_status?->getLabelText() ?? '',
            $lead->lead_source?->getLabelText() ?? '',
            $lead->phone,
            $lead->email ?? '',
            $lead->date_of_birth ? $lead->date_of_birth->format('d-m-Y') : '',
            $lead->address ?? '',
            $lead->postal_code ?? '',
            $lead->city ?? '',
            $lead->state ?? '',
            $lead->tax_id ?? '',
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
