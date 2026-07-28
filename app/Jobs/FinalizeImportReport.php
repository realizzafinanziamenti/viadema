<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\ImportExcelCompleted;
use App\Services\Imports\ImportReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

final class FinalizeImportReport implements ShouldQueue
{
    use Queueable;

    /**
     * Maximum number of execution attempts.
     */
    public int $tries = 3;

    /**
     * Maximum execution time in seconds.
     */
    public int $timeout = 120;

    public function __construct(
        public readonly int $importReportId,
        public readonly string $runUuid,
    ) {
    }

    /**
     * Finalize the import report and notify the interested users.
     */
    public function handle(
        ImportReportService $importReportService
    ): void {
        $report = $importReportService->complete(
            reportId: $this->importReportId,
            runUuid: $this->runUuid,
        );

        /*
         * The report may no longer exist or the run UUID may have
         * already been replaced by a newer import.
         */
        if ($report === null) {
            Log::notice(
                'Import report finalization skipped because the run is no longer current.',
                [
                    'import_report_id' => $this->importReportId,
                    'run_uuid' => $this->runUuid,
                ]
            );

            return;
        }

        $users = User::role('superadmin')
            ->get();

        /*
         * The report owner is the user who started the import.
         */
        $initiatedBy = $report->user;

        if ($initiatedBy !== null) {
            $users->push($initiatedBy);
        }

        $users = $users
            ->unique('id')
            ->values();

        if ($users->isNotEmpty()) {
            Notification::send(
                $users,
                new ImportExcelCompleted(
                    $report->type->value
                )
            );
        }

        Log::info(
            'Import report finalized successfully.',
            [
                'import_report_id' => $report->getKey(),
                'run_uuid' => $report->run_uuid,
                'type' => $report->type->value,
                'total_rows' => $report->total_rows,
                'imported_rows' => $report->imported_rows,
                'failed_rows' => $report->failed_rows,
            ]
        );
    }

    /**
     * Handle a permanent failure of the finalization job.
     */
    public function failed(Throwable $exception): void
    {
        try {
            app(ImportReportService::class)->fail(
                reportId: $this->importReportId,
                runUuid: $this->runUuid,
                errorMessage: sprintf(
                    'Errore durante la finalizzazione del report: %s',
                    $exception->getMessage()
                ),
            );
        } catch (Throwable $reportException) {
            Log::error(
                'Unable to mark import report as failed.',
                [
                    'import_report_id' => $this->importReportId,
                    'run_uuid' => $this->runUuid,
                    'exception' => $reportException,
                ]
            );
        }

        Log::error(
            'Import report finalization failed.',
            [
                'import_report_id' => $this->importReportId,
                'run_uuid' => $this->runUuid,
                'exception' => $exception,
            ]
        );
    }

    /**
     * Delay between retry attempts.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }
}