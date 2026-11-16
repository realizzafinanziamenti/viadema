<?php

namespace App\Exports;

use App\Models\ImportReport;
use App\Models\ImportReportRow;
use App\Services\Imports\ImportHeadings;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ImportReportExport implements WithMultipleSheets
{
    public const HEADINGS = ['Foglio origine', 'Riga Excel', 'ID originale', 'Nominativo / denominazione',
        'Codice fiscale / identificativo', 'Stato', 'ID CRM', 'Campo', 'Valore originale problematico', 'Errori'];

    public function __construct(private ImportReport $report) {}

    public function sheets(): array
    {
        // Retain only column metadata. Extra columns preserve long texts without
        // duplicating records; the source records are read in bounded chunks.
        $columns = array_fill_keys(self::HEADINGS, 1);
        foreach ($this->rows()->lazy(500) as $row) {
            foreach ($this->record($row) as $heading => $value) {
                $columns[$heading] = max($columns[$heading] ?? 1, is_string($value) ? (int) ceil(mb_strlen($value) / 16000) : 1);
            }
        }
        $notice = null;
        if ($this->report->detail_version !== 2) {
            $notice = 'Report storico: dettaglio completo non verificabile. Sono mostrati solo gli esiti conservati; foglio e valori originali potrebbero non essere disponibili.';
        } elseif ($this->report->status->value === 'failed') {
            $notice = 'Importazione interrotta: elenco parziale degli esiti persistiti. Le righe non elaborate o annullate non sono ricostruibili.';
        }

        return [new ImportReportSheet($this, $columns, false, $notice), new ImportReportSheet($this, $columns, true, $notice)];
    }

    public function rows(bool $failedOnly = false)
    {
        return $this->report->rows()->forRun($this->report->run_uuid)
            ->when($failedOnly, fn ($query) => $query->whereIn('status', ['failed', 'duplicate']))
            ->orderBy('sheet_index')->orderBy('source_sheet')->orderBy('row_number')->orderBy('id');
    }

    public function record(ImportReportRow $row): array
    {
        $raw = $row->raw_data ?? [];
        $original = [];
        $fieldValues = $raw;
        if ($row->source_data !== null) {
            $headings = $row->source_data['headings'];
            $values = $row->source_data['values'];
            $keys = HeadingRowFormatter::format($headings);
            $mapping = [];
            foreach ($values as $index => $value) {
                $coordinate = Coordinate::stringFromColumnIndex($index + 1);
                $heading = $headings[$index] ?? '';
                $original['Orig. '.$coordinate.' — '.$heading] = self::cellValue($value);
                $mapping[$keys[$index] ?? $index] = $coordinate;
            }
            // Resolve aliases through column references without changing values.
            $mapping = ImportHeadings::normalize($mapping, $this->report->type->value === 'practices');
            foreach ($mapping as $field => $coordinate) {
                $fieldValues[$field] = $values[Coordinate::columnIndexFromString($coordinate) - 1] ?? null;
            }
        } else {
            foreach ($raw as $key => $value) {
                $original['Dato conservato — '.$key] = self::cellValue($value);
            }
        }
        $pick = static function (array $keys) use ($fieldValues) {
            foreach ($keys as $key) {
                if (isset($fieldValues[$key]) && $fieldValues[$key] !== '') {
                    return self::text($fieldValues[$key]);
                }
            }

            return null;
        };
        $fields = $problemValues = $errors = [];
        foreach ($row->errors ?? [] as $field => $messages) {
            if (is_string($field)) {
                $fields[] = $field;
                if (array_key_exists($field, $fieldValues)) {
                    $problemValues[] = $field.': '.self::text($fieldValues[$field]);
                }
            }
            foreach (Arr::flatten((array) $messages) as $message) {
                $errors[] = (is_string($field) ? $field.': ' : '').self::text($message);
            }
        }
        if ($row->status->value !== 'imported' && $errors === [] && $row->message !== null) {
            $errors[] = $row->message;
        }
        $name = $pick(['denominazione', 'ragione_sociale', 'cognome_nome_cliente', 'nominativo']);
        if ($name === null) {
            $name = trim(($pick(['nome']) ?? '').' '.($pick(['cognome']) ?? ''))
                ?: ($row->source_data === null ? $row->label : null);
        }

        return array_combine(self::HEADINGS, [
            $row->source_sheet === '' ? null : $row->source_sheet, $row->row_number,
            $pick(['id', 'id_originale', 'id_origine', 'id_record', 'record_id', 'id_pratica', 'id_lead']), $name,
            $pick(['codice_fiscale', 'cf_cl', 'partita_iva', 'telefono', 'recapito_cell', 'numero_di_tel', 'email']),
            match ($row->status->value) {
                'imported' => 'Importato', 'duplicate' => 'Duplicato — non importato', default => 'Fallito',
            },
            $row->entity_id, implode("\n", $fields), implode("\n", $problemValues), implode("\n", $errors),
        ]) + $original;
    }

    private static function cellValue(mixed $value): mixed
    {
        return is_array($value) ? self::text($value) : $value;
    }

    private static function text(mixed $value): ?string
    {
        return $value === null ? null : (is_array($value)
            ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
            : (string) $value);
    }
}
