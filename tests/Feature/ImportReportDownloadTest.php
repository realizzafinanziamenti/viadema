<?php

use App\Enums\ImportReportType;
use App\Livewire\Admin\Lead\LeadIndex;
use App\Livewire\Admin\Practice\PracticeIndex;
use App\Models\ImportReport;
use App\Models\User;
use App\Services\Imports\ImportReportDownload;
use App\Services\Imports\ImportReportService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;

beforeEach(function () {
    expect(DB::getDriverName())->toBe('sqlite');
    expect(DB::connection()->getDatabaseName())->toBe(':memory:');
    $this->directory = sys_get_temp_dir().'/crm-report-test-'.Str::uuid();
    File::makeDirectory($this->directory, 0700, true);
    config(['excel.temporary_files.local_path' => $this->directory,
        'excel.temporary_files.remote_disk' => null, 'logging.default' => 'null',
        'view.compiled' => $this->directory]);
    Queue::fake();
    Notification::fake();
    $this->seed(RolesAndPermissionsSeeder::class);
    DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', fn (...$values) => implode('', $values));
    $this->owner = User::factory()->create()->assignRole('superadmin');
    $this->actingAs($this->owner);
});

afterEach(function () {
    File::deleteDirectory($this->directory);
});

function importSyntheticReport($test, ImportReportType $type, array $sheets, bool $retry = false, bool $interrupt = false): ImportReport
{
    $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
    foreach ($sheets as $index => $data) {
        $sheet = $index === 0 ? $book->getActiveSheet() : $book->createSheet();
        $sheet->setTitle($data['name']);
        $sheet->fromArray($data['rows'], null, 'A1', true);
    }
    $file = $test->directory.'/source.xlsx';
    IOFactory::createWriter($book, 'Xlsx')->save($file);
    $book->disconnectWorksheets();
    \App\Models\ProductType::firstOrCreate(['slug' => 'cessione-del-quinto'], ['name' => 'Cessione del quinto']);
    $report = app(ImportReportService::class)->start($test->owner, $type, 'source.xlsx');
    $class = $type === ImportReportType::PRACTICES ? \App\Imports\PracticesImport::class : \App\Imports\LeadsImport::class;
    $pending = \Maatwebsite\Excel\Facades\Excel::queueImport(
        new $class($test->owner, $report->id, $report->run_uuid, $test->owner->id, 'source.xlsx'),
        $file, null, \App\Services\Imports\CrmXlsxReader::TYPE
    )->allOnQueue('excel')->chain([new \App\Jobs\FinalizeImportReport($report->id, $report->run_uuid)]);
    unset($pending);
    $queued = Queue::pushed(\Maatwebsite\Excel\Jobs\QueueImport::class)->last();
    expect($queued->chainQueue)->toBe('excel');
    $chunks = 0;
    foreach ($queued->chained as $serialized) {
        $job = unserialize($serialized);
        expect($job->queue ?? $queued->chainQueue)->toBe('excel');
        if ($interrupt && $job instanceof \Maatwebsite\Excel\Jobs\ReadChunk && $chunks++ === 1) {
            $job->failed(new RuntimeException('Interruzione di test'));
            break;
        }
        app()->call([$job, 'handle']);
        if ($retry && $job instanceof \Maatwebsite\Excel\Jobs\ReadChunk) {
            $before = $report->rows()->orderBy('id')->get()->toArray();
            $count = [\App\Models\Customer::count(), \App\Models\Practice::count()];
            // Rehydrate the same queued payload, as a worker does after a retry.
            app()->call([unserialize($serialized), 'handle']);
            expect($report->rows()->orderBy('id')->get()->toArray())->toBe($before)
                ->and([\App\Models\Customer::count(), \App\Models\Practice::count()])->toBe($count);
        }
    }

    return $report->refresh();
}

function syntheticImportRows(int $count, int $invalid = 0, int $offset = 0): array
{
    $rows = [['ID', 'Nome', 'Cognome', 'Telefono', 'Codice fiscale', 'Prodotto', 'Data prima rata', 'Email', 'Note']];
    for ($i = 1; $i <= $count; $i++) {
        $key = $offset + $i;
        $rows[] = [sprintf('PR-%04d', $key), 'Nome'.$key, 'Cognome'.$key,
            '333'.str_pad((string) $key, 7, '0', STR_PAD_LEFT),
            'CF'.str_pad((string) $key, 14, '0', STR_PAD_LEFT), 'cqs', '2026-01-01',
            $i > $count - $invalid ? 'email-errata' : "record{$key}@example.test", '=1+1'];
    }

    return $rows;
}

test('mixed real XLSX imports produce ten original records with complete row colors', function ($type) {
    $report = importSyntheticReport($this, $type, [['name' => 'Origine', 'rows' => syntheticImportRows(10, 4)]]);
    expect([$report->total_rows, $report->imported_rows, $report->failed_rows])->toBe([10, 6, 4]);
    expect($report->detail_version)->toBe(2);
    $book = readDownloadWorkbook($this, app(ImportReportDownload::class)->download($type));
    expect($book->getSheetNames())->toBe(['Tutti i record', 'Record falliti']);
    $all = $book->getSheet(0);
    expect($all->getHighestRow())->toBe(11)
        ->and($book->getSheet(1)->getHighestRow())->toBe(5)
        ->and($all->getFreezePane())->toBe('A2')
        ->and($all->getAutoFilter()->getRange())->not->toBeEmpty()
        ->and($all->getStyle('A1')->getFont()->getBold())->toBeTrue();
    for ($i = 2; $i <= 11; $i++) {
        $color = $i <= 7 ? 'DCFCE7' : 'FEE2E2';
        expect($all->getCell('A'.$i)->getValue())->toBe('Origine')
            ->and($all->getCell('B'.$i)->getValue())->toBe($i)
            ->and($all->getCell('C'.$i)->getValue())->toBe(sprintf('PR-%04d', $i - 1))
            ->and($all->getCell('F'.$i)->getValue())->toBe($i <= 7 ? 'Importato' : 'Fallito');
        foreach ($all->getColumnIterator() as $column) {
            expect($all->getStyle($column->getColumnIndex().$i)->getFill()->getStartColor()->getRGB())->toBe($color);
        }
        expect($all->getCell('S'.$i)->getValue())->toBe('=1+1')
            ->and($all->getCell('S'.$i)->getDataType())->toBe('s');
    }
    for ($i = 2; $i <= 5; $i++) {
        $failed = $book->getSheet(1);
        expect($failed->getCell('B'.$i)->getValue())->toBe($i + 6)
            ->and($failed->getCell('H'.$i)->getValue())->toBe('email')
            ->and($failed->getCell('I'.$i)->getValue())->toBe('email: email-errata')
            ->and($failed->getCell('J'.$i)->getValue())->toContain('email')
            ->and($failed->getStyle('S'.$i)->getFill()->getStartColor()->getRGB())->toBe('FEE2E2');
    }
    $book->disconnectWorksheets();
})->with([ImportReportType::PRACTICES, ImportReportType::LEADS]);

test('multiple chunks sheets and retries preserve original order without duplicates', function ($type) {
    $report = importSyntheticReport($this, $type, [
        ['name' => 'Z primo', 'rows' => syntheticImportRows(105)],
        ['name' => 'A secondo', 'rows' => syntheticImportRows(3, 0, 105)],
    ], retry: true);
    expect([$report->total_rows, $report->imported_rows, $report->failed_rows])->toBe([108, 108, 0]);
    $book = readDownloadWorkbook($this, app(ImportReportDownload::class)->download($type));
    $all = $book->getSheet(0);
    expect($all->getHighestRow())->toBe(109);
    for ($i = 2; $i <= 106; $i++) {
        expect($all->getCell('B'.$i)->getValue())->toBe($i)
            ->and($all->getCell('A'.$i)->getValue())->toBe('Z primo');
    }
    expect($all->getCell('A107')->getValue())->toBe('A secondo')
        ->and($all->getCell('B107')->getValue())->toBe(2)
        ->and($all->getCell('C107')->getValue())->toBe('PR-0106')
        ->and($book->getSheet(1)->getCell('A2')->getValue())->toContain('Nessun record fallito');
    $book->disconnectWorksheets();
})->with([ImportReportType::PRACTICES, ImportReportType::LEADS]);

test('real multi error row preserves original whitespace values and all errors', function ($type) {
    $rows = syntheticImportRows(1, 1);
    $rows[1][1] = str_repeat('N', 256);
    $rows[1][7] = '  email-errata  ';
    $report = importSyntheticReport($this, $type, [['name' => 'Origine', 'rows' => $rows]]);
    $row = $report->rows()->first();
    expect($row->errors)->toHaveKeys(['nome', 'email'])
        ->and($row->source_data['values'][7])->toBe('  email-errata  ');
    $book = readDownloadWorkbook($this, app(ImportReportDownload::class)->download($type));
    expect($book->getSheet(1)->getCell('J2')->getValue())->toContain('nome:', 'email:')
        ->and($book->getSheet(1)->getCell('I2')->getValue())->toContain('email:   email-errata  ');
    $book->disconnectWorksheets();
})->with([ImportReportType::PRACTICES, ImportReportType::LEADS]);

test('duplicates are explicit unsuccessful outcomes and retries preserve successful originals', function ($type) {
    $rows = syntheticImportRows(1);
    $rows[] = $rows[1];
    $rows[2][0] = 'DUP-0001';
    $report = importSyntheticReport($this, $type, [['name' => 'Origine', 'rows' => $rows]], retry: true);
    expect([$report->total_rows, $report->imported_rows, $report->failed_rows])->toBe([2, 1, 1])
        ->and($report->rows()->where('row_number', 3)->first()->status->value)->toBe('duplicate');
    $book = readDownloadWorkbook($this, app(ImportReportDownload::class)->download($type));
    expect($book->getSheet(0)->getCell('F3')->getValue())->toContain('Duplicato')
        ->and($book->getSheet(0)->getStyle('A3')->getFill()->getStartColor()->getRGB())->toBe('FEF3C7')
        ->and($book->getSheet(1)->getCell('C2')->getValue())->toBe('DUP-0001')
        ->and($book->getSheet(1)->getStyle('A2')->getFill()->getStartColor()->getRGB())->toBe('FEE2E2');
    $book->disconnectWorksheets();
})->with([ImportReportType::PRACTICES, ImportReportType::LEADS]);

test('failed persistence never leaves a successful record or CRM mutations', function ($type) {
    \Illuminate\Support\Facades\Event::listen('eloquent.creating: '.\App\Models\ImportReportRow::class, function ($row) {
        if ($row->status->value === 'imported') {
            throw new RuntimeException('SQL internal detail');
        }
    });
    $report = importSyntheticReport($this, $type, [['name' => 'Origine', 'rows' => syntheticImportRows(1)]]);
    expect([$report->total_rows, $report->imported_rows, $report->failed_rows])->toBe([1, 0, 1])
        ->and(\App\Models\Customer::count())->toBe(0)
        ->and(\App\Models\Practice::count())->toBe(0)
        ->and(\App\Models\PracticeOpportunity::count())->toBe(0);
    $book = readDownloadWorkbook($this, app(ImportReportDownload::class)->download($type));
    expect($book->getSheet(1)->getCell('J2')->getValue())->toContain('Salvataggio non riuscito')
        ->not->toContain('SQL internal detail');
    $book->disconnectWorksheets();
})->with([ImportReportType::PRACTICES, ImportReportType::LEADS]);

test('interrupted import exports only persisted outcomes with an explicit partial warning', function ($type) {
    $report = importSyntheticReport($this, $type, [['name' => 'Origine', 'rows' => syntheticImportRows(105)]], interrupt: true);
    expect($report->status->value)->toBe('failed')
        ->and($report->total_rows)->toBe(100);
    // A late/retried finalizer must not turn a failed import into a complete one.
    (new \App\Jobs\FinalizeImportReport($report->id, $report->run_uuid))->handle(app(ImportReportService::class));
    expect($report->fresh()->status->value)->toBe('failed');
    $book = readDownloadWorkbook($this, app(ImportReportDownload::class)->download($type));
    expect($book->getSheet(0)->getCell('A2')->getValue())->toContain('elenco parziale')
        ->and($book->getSheet(0)->getHighestRow())->toBe(102);
    $book->disconnectWorksheets();
})->with([ImportReportType::PRACTICES, ImportReportType::LEADS]);

test('blank rows are represented according to existing validation without inventing source ids', function ($type) {
    $rows = syntheticImportRows(3);
    $rows[2] = array_fill(0, count($rows[0]), null);
    $rows[3][0] = null;
    $report = importSyntheticReport($this, $type, [['name' => 'Origine', 'rows' => $rows]]);
    expect([$report->total_rows, $report->imported_rows, $report->failed_rows])->toBe([3, 2, 1]);
    $book = readDownloadWorkbook($this, app(ImportReportDownload::class)->download($type));
    expect($book->getSheet(0)->getCell('B3')->getValue())->toBe(3)
        ->and($book->getSheet(0)->getCell('C3')->getValue())->toBeNull()
        ->and($book->getSheet(0)->getCell('C4')->getValue())->toBeNull()
        ->and($book->getSheet(0)->getCell('G4')->getValue())->not->toBeNull();
    $book->disconnectWorksheets();
})->with([ImportReportType::PRACTICES, ImportReportType::LEADS]);

test('historical aggregate report is never presented as a complete list', function () {
    $report = completedDownloadReport($this->owner, ImportReportType::LEADS);
    $report->update(['detail_version' => null]);
    $report->rows()->delete();
    $book = readDownloadWorkbook($this, app(ImportReportDownload::class)->download(ImportReportType::LEADS));
    expect($book->getSheetNames())->toBe(['Tutti i record', 'Record falliti'])
        ->and($book->getSheet(0)->getCell('A2')->getValue())->toContain('Report storico', 'non verificabile')
        ->and($book->getSheet(0)->getCell('A3')->getValue())->toBe('Nessun dettaglio di riga disponibile.');
    $book->disconnectWorksheets();
});

test('original ids and duplicate original headings survive independently of current CRM data', function ($type) {
    $rows = syntheticImportRows(1);
    $rows[0][0] = 'ID origine';
    $rows[1][0] = '000015';
    $rows[0][] = 'Note';
    $rows[1][] = '  seconda nota  ';
    $report = importSyntheticReport($this, $type, [['name' => 'Origine', 'rows' => $rows]]);
    \App\Models\Customer::query()->update(['first_name' => 'Modificato dopo import']);
    $book = readDownloadWorkbook($this, app(ImportReportDownload::class)->download($type));
    $sheet = $book->getSheet(0);
    expect($sheet->getCell('C2')->getValue())->toBe('000015')
        ->and($sheet->getCell('C2')->getDataType())->toBe('s')
        ->and($sheet->getCell('D2')->getValue())->toBe('Nome1 Cognome1')
        ->and($sheet->getCell('S2')->getValue())->toBe('=1+1')
        ->and($sheet->getCell('T2')->getValue())->toBe('  seconda nota  ');
    $book->disconnectWorksheets();
})->with([ImportReportType::PRACTICES, ImportReportType::LEADS]);

function completedDownloadReport(User $user, ImportReportType $type): ImportReport
{
    $service = app(ImportReportService::class);
    $report = $service->start($user, $type, '=source.xlsx');
    $service->recordImportedRow($report->id, $report->run_uuid, 2, '=original', ['telefono' => '00123'], 'customer', 42);
    $service->recordFailedRow($report->id, $report->run_uuid, 8, 'Errore originale', 'Dato errato',
        ['telefono' => '=1+1'], ['telefono' => ['Telefono non valido', 'Secondo errore']]);
    $report = $service->complete($report->id, $report->run_uuid);
    $report->update(['completed_at' => '2026-11-15 10:30:00', 'detail_version' => 2]);

    return $report->refresh();
}

function readDownloadWorkbook($test, $response)
{
    ob_start();
    $response->sendContent();
    $bytes = ob_get_clean();
    $path = $test->directory.'/download.xlsx';
    file_put_contents($path, $bytes);
    // A separate Excel client does not inherit the export's process-global binder.
    \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(new \PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder);

    return IOFactory::load($path);
}

test('downloads valid original workbook with numeric counts and safe strings without writes', function ($type, $slug) {
    $report = completedDownloadReport($this->owner, $type);
    $before = $report->fresh()->toArray();
    $beforeRows = $report->rows()->get()->toArray();
    $writes = [];
    DB::listen(function ($query) use (&$writes) {
        if (preg_match('/^\s*(insert|update|delete|replace)/i', $query->sql)) {
            $writes[] = $query->sql;
        }
    });
    $response = app(ImportReportDownload::class)->download($type);
    expect($response->headers->get('Content-Type'))->toBe('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->and($response->headers->get('Content-Disposition'))->toContain("report-import-{$slug}-2026-11-15-1030.xlsx");
    $book = readDownloadWorkbook($this, $response);
    expect($book->getSheetNames())->toBe(['Tutti i record', 'Record falliti']);
    $all = $book->getSheet(0);
    expect($all->getHighestRow() - 1)->toBe($report->total_rows)
        ->and($all->getCell('B2')->getValue())->toBe(2)
        ->and($all->getCell('G2')->getValue())->toBe(42)
        ->and($all->getCell('G2')->getDataType())->toBe('n')
        ->and($all->getCell('D2')->getValue())->toBe('=original')
        ->and($all->getCell('D2')->getDataType())->toBe('s');
    $errors = $book->getSheet(1);
    expect($errors->getHighestRow() - 1)->toBe($report->failed_rows)
        ->and($errors->getCell('B2')->getValue())->toBe(8)
        ->and($errors->getCell('H2')->getValue())->toBe('telefono')
        ->and($errors->getCell('I2')->getValue())->toBe('telefono: =1+1')
        ->and($errors->getCell('J2')->getValue())->toContain('Telefono non valido', 'Secondo errore')
        ->and($errors->getCell('K2')->getValue())->toBe('=1+1')
        ->and($errors->getCell('K2')->getDataType())->toBe('s');
    expect($report->fresh()->toArray())->toBe($before)
        ->and($report->rows()->get()->toArray())->toBe($beforeRows)
        ->and($writes)->toBe([]);
    Queue::assertNothingPushed();
    Notification::assertNothingSent();
    expect(glob($this->directory.'/laravel-excel-*'))->toBe([]);
    $book->disconnectWorksheets();
})->with([[ImportReportType::PRACTICES, 'pratiche'], [ImportReportType::LEADS, 'lead']]);

test('no download for missing or unfinished report', function ($status) {
    if ($status !== null) {
        $report = app(ImportReportService::class)->start($this->owner, ImportReportType::PRACTICES, 'running.xlsx');
        $report->update(['status' => $status]);
    }
    expect(app(ImportReportDownload::class)->download(ImportReportType::PRACTICES))->toBeNull();
})->with([null, 'pending', 'processing']);

test('selects only current user and type and does not resurrect an overwritten report', function () {
    $service = app(ImportReportService::class);
    $old = completedDownloadReport($this->owner, ImportReportType::PRACTICES);
    completedDownloadReport($this->owner, ImportReportType::LEADS);
    completedDownloadReport(User::factory()->create(), ImportReportType::PRACTICES);
    $new = $service->start($this->owner, ImportReportType::PRACTICES, 'new.xlsx');
    expect($new->id)->toBe($old->id)->and($new->run_uuid)->not->toBe($old->run_uuid);
    expect(app(ImportReportDownload::class)->download(ImportReportType::PRACTICES))->toBeNull();
    $service->recordImportedRow($new->id, $new->run_uuid, 9, 'Nuovo', []);
    $service->complete($new->id, $new->run_uuid);
    $book = readDownloadWorkbook($this, app(ImportReportDownload::class)->download(ImportReportType::PRACTICES));
    expect($book->getSheet(0)->getHighestRow())->toBe(3)
        ->and($book->getSheet(0)->getCell('B3')->getValue())->toBe(9)
        ->and($book->getSheet(0)->getCell('D3')->getValue())->toBe('Nuovo');
    $book->disconnectWorksheets();
});

test('denies unauthenticated and unauthorized users', function ($guest, $type) {
    completedDownloadReport($this->owner, $type);
    if ($guest) {
        auth()->forgetGuards();
    } else {
        $this->actingAs(User::factory()->create());
    }
    expect(fn () => app(ImportReportDownload::class)->download($type))->toThrow($guest
        ? \Symfony\Component\HttpKernel\Exception\HttpException::class
        : \Illuminate\Auth\Access\AuthorizationException::class);
})->with([true, false])->with([ImportReportType::PRACTICES, ImportReportType::LEADS]);

test('exports all errors beyond pagination and cell length limits', function () {
    $report = completedDownloadReport($this->owner, ImportReportType::LEADS);
    $long = str_repeat('Errore lungo è. ', 3000);
    for ($i = 10; $i < 1015; $i++) {
        $report->rows()->create(['run_uuid' => $report->run_uuid, 'row_number' => $i,
            'status' => 'failed', 'errors' => [$i === 1014 ? $long : "Errore {$i}"]]);
    }
    $book = readDownloadWorkbook($this, app(ImportReportDownload::class)->download(ImportReportType::LEADS));
    $all = $book->getSheet(1)->toArray(null, false, false);
    $last = array_values(array_filter($all, fn ($row) => $row[1] === 1014));
    expect(count($last))->toBe(1)
        ->and(implode('', array_slice($last[0], 9, 3)))->toBe($long)
        ->and(count(array_unique(array_column(array_slice($all, 1), 1))))->toBe(1006);
    $book->disconnectWorksheets();
});

test('terminal failed report explicitly marks incomplete detail', function () {
    $service = app(ImportReportService::class);
    $report = $service->start($this->owner, ImportReportType::LEADS, 'failed.xlsx');
    $report->update(['detail_version' => 2]);
    $service->fail($report->id, $report->run_uuid, 'Errore definitivo');
    $book = readDownloadWorkbook($this, app(ImportReportDownload::class)->download(ImportReportType::LEADS));
    expect($book->getSheetNames())->toBe(['Tutti i record', 'Record falliti'])
        ->and($book->getSheet(0)->getCell('A2')->getValue())->toContain('Importazione interrotta')
        ->and($book->getSheet(1)->getCell('A3')->getValue())->toContain('Nessun record fallito');
    $book->disconnectWorksheets();
});

test('livewire download ignores manipulated report ids and handles unavailable reports', function ($component, $type, $slug) {
    $other = completedDownloadReport(User::factory()->create(), $type);
    $test = Livewire::actingAs($this->owner)->test($component)
        ->set('selectedImportReportId', $other->id)
        ->call('downloadLatestImportReport')->assertNoFileDownloaded();
    completedDownloadReport($this->owner, $type);
    $test->call('downloadLatestImportReport')
        ->assertFileDownloaded("report-import-{$slug}-2026-11-15-1030.xlsx");
})->with([[PracticeIndex::class, ImportReportType::PRACTICES, 'pratiche'],
    [LeadIndex::class, ImportReportType::LEADS, 'lead']]);

test('report service preserves counters and rejects stale runs', function () {
    $service = app(ImportReportService::class);
    $report = completedDownloadReport($this->owner, ImportReportType::PRACTICES);
    expect([$report->total_rows, $report->imported_rows, $report->failed_rows])->toBe([2, 1, 1]);
    $new = $service->start($this->owner, ImportReportType::PRACTICES, 'new.xlsx');
    expect($service->recordImportedRow($report->id, $report->run_uuid, 3, 'stale', []))->toBeFalse()
        ->and($service->complete($report->id, $report->run_uuid))->toBeNull()
        ->and($new->rows()->count())->toBe(0);
});

test('real import failure callbacks and finalization remain compatible with export', function ($importClass, $type) {
    $service = app(ImportReportService::class);
    $report = $service->start($this->owner, $type, 'source.xlsx');
    $import = new $importClass($this->owner, $report->id, $report->run_uuid, $this->owner->id, 'source.xlsx');
    expect($import->chunkSize())->toBe(100);
    $import->onFailure(new \Maatwebsite\Excel\Validators\Failure(17, 'telefono', ['Telefono errato'], ['telefono' => '001']));
    $job = new \App\Jobs\FinalizeImportReport($report->id, $report->run_uuid);
    expect($job->queue)->toBe('excel');
    $job->handle($service);
    $report->refresh();
    expect([$report->total_rows, $report->failed_rows, $report->imported_rows])->toBe([1, 1, 0]);
    $book = readDownloadWorkbook($this, app(ImportReportDownload::class)->download($type));
    expect($book->getSheet(1)->getCell('B3')->getValue())->toBe(17)
        ->and($book->getSheet(1)->getCell('H3')->getValue())->toBe('telefono')
        ->and($book->getSheet(1)->getCell('J3')->getValue())->toBe('telefono: Telefono errato');
    $book->disconnectWorksheets();
})->with([[\App\Imports\PracticesImport::class, ImportReportType::PRACTICES],
    [\App\Imports\LeadsImport::class, ImportReportType::LEADS]]);

test('preflight still accepts valid xlsx and rejects malformed input', function () {
    $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
    $book->getActiveSheet()->fromArray([['nome', 'telefono'], ['Mario', '00123']]);
    $file = $this->directory.'/input.xlsx';
    IOFactory::createWriter($book, 'Xlsx')->save($file);
    expect(app(\App\Services\Imports\XlsxImportPreflight::class)->inspect($file))->not->toBeEmpty();
    $book->disconnectWorksheets();
    file_put_contents($file, 'invalid');
    expect(fn () => app(\App\Services\Imports\XlsxImportPreflight::class)->inspect($file))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('livewire denies users without import permission', function ($component) {
    \Spatie\Permission\Models\Role::findByName('consultant')->revokePermissionTo('import leads', 'import practices');
    Livewire::actingAs(User::factory()->create()->assignRole('consultant'))->test($component)
        ->call('downloadLatestImportReport')->assertForbidden();
})->with([[PracticeIndex::class], [LeadIndex::class]]);
