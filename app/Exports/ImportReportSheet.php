<?php

namespace App\Exports;

use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportReportSheet extends DefaultValueBinder implements FromGenerator, WithCustomValueBinder, WithHeadings, WithStrictNullComparison, WithStyles, WithTitle
{
    private array $rowColors = [];

    public function __construct(private ImportReportExport $export, private array $columns, private bool $failedOnly, private ?string $notice) {}

    public function title(): string
    {
        return $this->failedOnly ? 'Record falliti' : 'Tutti i record';
    }

    public function headings(): array
    {
        $headings = [];
        foreach ($this->columns as $heading => $parts) {
            for ($i = 1; $i <= $parts; $i++) {
                $headings[] = $heading.($i > 1 ? " (parte {$i})" : '');
            }
        }

        return $headings;
    }

    public function generator(): Generator
    {
        $excelRow = 2;
        if ($this->notice !== null) {
            yield [$this->notice];
            $excelRow++;
        }
        $found = false;
        foreach ($this->export->rows($this->failedOnly)->lazy(500) as $row) {
            $found = true;
            $record = $this->export->record($row);
            $values = [];
            foreach ($this->columns as $heading => $parts) {
                $value = $record[$heading] ?? null;
                for ($i = 0; $i < $parts; $i++) {
                    $values[] = is_string($value) ? mb_substr($value, $i * 16000, 16000) : ($i === 0 ? $value : null);
                }
            }
            $this->rowColors[$excelRow++] = $this->failedOnly || $row->status->value === 'failed'
                ? 'FEE2E2' : ($row->status->value === 'imported' ? 'DCFCE7' : 'FEF3C7');
            yield $values;
        }
        if (! $found) {
            yield [$this->failedOnly ? 'Nessun record fallito negli esiti disponibili.' : 'Nessun dettaglio di riga disponibile.'];
        }
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet): array
    {
        $last = $sheet->getHighestColumn();
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:'.$last.$sheet->getHighestRow());
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true)->setVertical('top');
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getFont()->getColor()->setRGB('18181B');
        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setWidth(26);
        }
        foreach (['B' => 12, 'D' => 32, 'F' => 28, 'G' => 14, 'I' => 40, 'J' => 65] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        // Apply one style range per contiguous outcome instead of per cell.
        $start = $previousRow = null;
        $previousColor = null;
        foreach ($this->rowColors + [($sheet->getHighestRow() + 1) => null] as $row => $color) {
            if ($previousColor !== null && $color !== $previousColor) {
                $sheet->getStyle("A{$start}:{$last}{$previousRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($previousColor);
            }
            if ($color !== $previousColor) {
                $start = $row;
            }
            $previousRow = $row;
            $previousColor = $color;
        }
        if ($this->notice !== null) {
            $sheet->mergeCells('A2:'.$last.'2');
            $sheet->getRowDimension(2)->setRowHeight(42);
        }
        $sheet->getComment('A1')->getText()->createTextRun('Riga Excel, ID originale e ID CRM sono identificativi distinti. Le colonne Orig. conservano i valori letti, comprese le date seriali Excel. I testi lunghi proseguono nelle colonne Parte.');

        return [1 => ['font' => ['bold' => true]]];
    }
}
