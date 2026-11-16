<?php

namespace App\Services\Imports;

use App\Enums\ImportReportType;
use App\Exports\ImportReportExport;
use App\Models\Customer;
use App\Models\ImportReport;
use App\Models\Practice;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportReportDownload
{
    public function download(ImportReportType $type): ?StreamedResponse
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        Gate::authorize(...($type === ImportReportType::PRACTICES
            ? ['importPractice', Practice::class] : ['importLead', Customer::class]));

        return DB::transaction(function () use ($user, $type) {
            // start() locks this same record before replacing the run and its rows.
            // Hold the lock until the workbook is materialized to avoid mixed runs.
            $report = ImportReport::query()->forUserAndType($user->getKey(), $type)
                ->whereIn('status', ['completed', 'failed'])->whereNotNull('completed_at')
                ->orderByDesc('completed_at')->orderByDesc('id')->lockForUpdate()->first();
            if ($report === null) {
                return null;
            }
            $binder = \PhpOffice\PhpSpreadsheet\Cell\Cell::getValueBinder();
            try {
                $content = Excel::raw(new ImportReportExport($report), \Maatwebsite\Excel\Excel::XLSX);
            } finally {
                \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder($binder);
            }
            $name = 'report-import-'.($type === ImportReportType::PRACTICES ? 'pratiche' : 'lead')
                .'-'.$report->completed_at->format('Y-m-d-Hi').'.xlsx';

            return response()->streamDownload(static function () use ($content) {
                echo $content;
            }, $name, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'private, no-store']);
        });
    }
}
