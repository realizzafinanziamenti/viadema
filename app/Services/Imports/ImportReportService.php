<?php

namespace App\Services\Imports;

use App\Enums\ImportReportRowStatus;
use App\Enums\ImportReportStatus;
use App\Enums\ImportReportType;
use App\Models\ImportReport;
use App\Models\ImportReportRow;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ImportReportService
{
    /**
     * Start a new import for the given user and type.
     *
     * The previous completed report is reused and its rows are deleted.
     * A running import cannot be overwritten.
     */
    public function start(
        User $user,
        ImportReportType $type,
        string $fileName
    ): ImportReport {
        return DB::transaction(function () use (
            $user,
            $type,
            $fileName
        ): ImportReport {
            $report = ImportReport::query()
                ->forUserAndType($user->getKey(), $type)
                ->lockForUpdate()
                ->first();

            if ($report?->isRunning()) {
                throw new DomainException(
                    'Esiste già un import dello stesso tipo in corso.'
                );
            }

            if ($report === null) {
                $report = new ImportReport([
                    'user_id' => $user->getKey(),
                    'type' => $type,
                ]);
            } else {
                $report->rows()->delete();
            }

            $report->fill([
                'run_uuid' => Str::uuid()->toString(),
                'file_name' => Str::limit(
                    basename($fileName),
                    255,
                    ''
                ),
                'status' => ImportReportStatus::PENDING,
                'total_rows' => 0,
                'imported_rows' => 0,
                'failed_rows' => 0,
                'started_at' => now(),
                'completed_at' => null,
                'viewed_at' => null,
                'error_message' => null,
            ]);

            $report->save();

            return $report->refresh();
        }, 3);
    }

    /**
     * Find the report belonging to a user and import type.
     */
    public function findForUserAndType(
        User $user,
        ImportReportType $type
    ): ?ImportReport {
        return ImportReport::query()
            ->forUserAndType($user->getKey(), $type)
            ->first();
    }

    /**
     * Determine whether the supplied execution is still current.
     */
    public function isCurrentRun(
        int $reportId,
        string $runUuid
    ): bool {
        return ImportReport::query()
            ->whereKey($reportId)
            ->where('run_uuid', $runUuid)
            ->exists();
    }

    /**
     * Register a successfully imported row.
     */
    public function recordImportedRow(
        int $reportId,
        string $runUuid,
        int $rowNumber,
        string $label,
        array $rawData,
        ?string $entityType = null,
        ?int $entityId = null,
        string $message = 'Importato correttamente'
    ): bool {
        return $this->recordRow(
            reportId: $reportId,
            runUuid: $runUuid,
            rowNumber: $rowNumber,
            status: ImportReportRowStatus::IMPORTED,
            label: $label,
            message: $message,
            rawData: $rawData,
            errors: null,
            entityType: $entityType,
            entityId: $entityId,
        );
    }

    /**
     * Register a row that could not be imported.
     */
    public function recordFailedRow(
        int $reportId,
        string $runUuid,
        int $rowNumber,
        string $label,
        string $message,
        array $rawData,
        array $errors,
    ): bool {
        return $this->recordRow(
            reportId: $reportId,
            runUuid: $runUuid,
            rowNumber: $rowNumber,
            status: ImportReportRowStatus::FAILED,
            label: $label,
            message: $message,
            rawData: $rawData,
            errors: $errors,
        );
    }

    /**
     * Finalize a successfully processed import.
     */
    public function complete(
        int $reportId,
        string $runUuid
    ): ?ImportReport {
        return DB::transaction(function () use (
            $reportId,
            $runUuid
        ): ?ImportReport {
            $report = $this->lockCurrentReport(
                $reportId,
                $runUuid
            );

            if ($report === null) {
                return null;
            }

            $counts = $this->calculateCounts(
                $report,
                $runUuid
            );

            $report->update([
                'status' => ImportReportStatus::COMPLETED,
                'total_rows' => $counts['total'],
                'imported_rows' => $counts['imported'],
                'failed_rows' => $counts['failed'],
                'completed_at' => now(),
                'error_message' => null,
            ]);

            return $report->refresh();
        }, 3);
    }

    /**
     * Mark the entire import as failed.
     *
     * Successfully processed rows are preserved in the report.
     */
    public function fail(
        int $reportId,
        string $runUuid,
        string $errorMessage
    ): ?ImportReport {
        return DB::transaction(function () use (
            $reportId,
            $runUuid,
            $errorMessage
        ): ?ImportReport {
            $report = $this->lockCurrentReport(
                $reportId,
                $runUuid
            );

            if ($report === null) {
                return null;
            }

            if (
                $report->status ===
                ImportReportStatus::COMPLETED
            ) {
                return $report;
            }

            $counts = $this->calculateCounts(
                $report,
                $runUuid
            );

            $report->update([
                'status' => ImportReportStatus::FAILED,
                'total_rows' => $counts['total'],
                'imported_rows' => $counts['imported'],
                'failed_rows' => $counts['failed'],
                'completed_at' => now(),
                'error_message' => Str::limit(
                    $errorMessage,
                    65000,
                    ''
                ),
            ]);

            return $report->refresh();
        }, 3);
    }

    /**
     * Mark the report as viewed by the user.
     */
    public function markAsViewed(
        int $reportId,
        string $runUuid
    ): bool {
        return ImportReport::query()
            ->whereKey($reportId)
            ->where('run_uuid', $runUuid)
            ->update([
                'viewed_at' => now(),
            ]) > 0;
    }

    private function recordRow(
        int $reportId,
        string $runUuid,
        int $rowNumber,
        ImportReportRowStatus $status,
        string $label,
        string $message,
        array $rawData,
        ?array $errors = null,
        ?string $entityType = null,
        ?int $entityId = null,
    ): bool {
        return DB::transaction(function () use (
            $reportId,
            $runUuid,
            $rowNumber,
            $status,
            $label,
            $message,
            $rawData,
            $errors,
            $entityType,
            $entityId
        ): bool {
            $report = $this->lockCurrentReport(
                $reportId,
                $runUuid
            );

            if ($report === null || !$report->isRunning()) {
                return false;
            }

            if (
                $report->status ===
                ImportReportStatus::PENDING
            ) {
                $report->update([
                    'status' => ImportReportStatus::PROCESSING,
                ]);
            }

            ImportReportRow::query()->updateOrCreate(
                [
                    'import_report_id' => $report->getKey(),
                    'run_uuid' => $runUuid,
                    'row_number' => $rowNumber,
                ],
                [
                    'status' => $status,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'label' => Str::limit($label, 255, ''),
                    'message' => $message,
                    'errors' => $errors,
                    'raw_data' => $rawData,
                ]
            );

            return true;
        }, 3);
    }

    private function lockCurrentReport(
        int $reportId,
        string $runUuid
    ): ?ImportReport {
        return ImportReport::query()
            ->whereKey($reportId)
            ->where('run_uuid', $runUuid)
            ->lockForUpdate()
            ->first();
    }

    /**
     * Calculate counters from persisted rows.
     *
     * Counters are not incremented while processing because queued jobs
     * can be retried. Recalculating them avoids duplicate increments.
     *
     * @return array{total: int, imported: int, failed: int}
     */
    private function calculateCounts(
        ImportReport $report,
        string $runUuid
    ): array {
        $rows = $report->rows()
            ->where('run_uuid', $runUuid);

        $imported = (clone $rows)
            ->where(
                'status',
                ImportReportRowStatus::IMPORTED->value
            )
            ->count();

        $failed = (clone $rows)
            ->where(
                'status',
                ImportReportRowStatus::FAILED->value
            )
            ->count();

        return [
            'total' => $imported + $failed,
            'imported' => $imported,
            'failed' => $failed,
        ];
    }
}