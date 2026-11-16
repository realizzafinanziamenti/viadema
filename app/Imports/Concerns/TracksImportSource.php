<?php

namespace App\Imports\Concerns;

use App\Models\ImportReport;
use Maatwebsite\Excel\Concerns\RemembersChunkOffset;
use Maatwebsite\Excel\Events\BeforeSheet;

trait TracksImportSource
{
    use RemembersChunkOffset;

    protected string $sourceSheet = '';

    protected ?int $sourceSheetIndex = null;

    protected array $sourceHeadings = [];

    protected array $sourceValues = [];

    protected array $originalRows = [];

    protected array $recordedRows = [];

    protected bool $acceptImportRows = true;

    public function trackSourceSheet(BeforeSheet $event): void
    {
        $sheet = $event->sheet->getDelegate();
        $this->sourceSheet = $sheet->getTitle();
        $this->sourceSheetIndex = $sheet->getParent()->getIndex($sheet);
        $this->originalRows = $this->sourceValues = $this->recordedRows = [];
        // ReadChunk already wraps this callback and its rows in a transaction.
        // The same report lock serializes retries and start() replacing a run.
        $report = ImportReport::query()->whereKey($this->importReportId)
            ->where('run_uuid', $this->runUuid)->lockForUpdate()->first();
        $this->acceptImportRows = $report !== null && $report->isRunning();
        if (! $this->acceptImportRows) {
            return;
        }
        if ($report->detail_version === null && ! $report->rows()->forRun($this->runUuid)->whereNull('source_data')->exists()) {
            $report->update(['detail_version' => 2]);
        }
        $start = $this->getChunkOffset() ?? 2;
        $end = min($sheet->getHighestRow(), $start + $this->chunkSize() - 1);
        $column = $sheet->getHighestColumn();
        $this->sourceHeadings = $sheet->rangeToArray("A1:{$column}1", null, false, false)[0];
        foreach ($sheet->rangeToArray("A{$start}:{$column}{$end}", null, false, false) as $offset => $values) {
            $this->sourceValues[$start + $offset] = $values;
        }
        $this->recordedRows = array_fill_keys($report->rows()->forRun($this->runUuid)
            ->where('source_sheet', $this->sourceSheet)->whereBetween('row_number', [$start, $end])
            ->pluck('row_number')->all(), true);
    }

    protected function shouldProcessSourceRow(int $rowNumber): bool
    {
        return $this->acceptImportRows && ! isset($this->recordedRows[$rowNumber]);
    }

    protected function originalRow(int $rowNumber, array $fallback): array
    {
        return $this->originalRows[$rowNumber] ?? $fallback;
    }

    protected function sourceData(int $rowNumber): ?array
    {
        return isset($this->sourceValues[$rowNumber]) ? [
            'headings' => $this->sourceHeadings,
            'values' => $this->sourceValues[$rowNumber],
        ] : null;
    }
}
