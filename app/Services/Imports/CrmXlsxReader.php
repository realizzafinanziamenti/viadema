<?php

namespace App\Services\Imports;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

final class CrmXlsxReader extends Xlsx
{
    public const TYPE = 'CrmXlsx';

    public function listWorksheetInfo($filename)
    {
        // Called once by the planner on Laravel Excel's own temporary copy.
        // ReadChunk inherits load(): no preflight is performed on each chunk.
        return app(XlsxImportPreflight::class)->inspect($filename);
    }
}
