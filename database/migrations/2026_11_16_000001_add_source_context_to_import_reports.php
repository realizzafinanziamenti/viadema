<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_reports', function (Blueprint $table) {
            $table->unsignedTinyInteger('detail_version')->nullable();
        });
        Schema::table('import_report_rows', function (Blueprint $table) {
            $table->string('source_sheet', 31)->default('');
            $table->unsignedInteger('sheet_index')->nullable();
            $table->json('source_data')->nullable();
            $table->dropUnique('import_report_rows_run_row_unique');
            $table->unique(['import_report_id', 'run_uuid', 'source_sheet', 'row_number'], 'import_report_rows_source_unique');
        });
    }

    public function down(): void
    {
        // Multiple worksheets can now share row numbers: do not silently destroy
        // those results merely to recreate the old, narrower unique constraint.
        if (\Illuminate\Support\Facades\DB::table('import_report_rows')
            ->select('import_report_id', 'run_uuid', 'row_number')
            ->groupBy('import_report_id', 'run_uuid', 'row_number')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Rollback non possibile: report con righe di più fogli.');
        }
        Schema::table('import_report_rows', function (Blueprint $table) {
            $table->dropUnique('import_report_rows_source_unique');
            $table->unique(['import_report_id', 'run_uuid', 'row_number'], 'import_report_rows_run_row_unique');
            $table->dropColumn(['source_sheet', 'sheet_index', 'source_data']);
        });
        Schema::table('import_reports', fn (Blueprint $table) => $table->dropColumn('detail_version'));
    }
};
