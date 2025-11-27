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

class CustomersExport extends StringValueBinder implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithCustomValueBinder
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
    public function map($customer): array
    {
        return [
            $customer->first_name . ' ' . $customer->last_name,
            $customer->customerType?->name ?? '',
            $customer->phone,
            $customer->email ?? '',
            $customer->date_of_birth ? $customer->date_of_birth->format('d-m-Y') : '',
            $customer->address ?? '',
            $customer->postal_code ?? '',
            $customer->city ?? '',
            $customer->state ?? '',
            $customer->tax_id ?? '',
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
