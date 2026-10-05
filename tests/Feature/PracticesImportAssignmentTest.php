<?php

use App\Enums\CustomerStatus;
use App\Enums\ImportReportRowStatus;
use App\Enums\ImportReportType;
use App\Imports\PracticesImport;
use App\Models\Customer;
use App\Models\Practice;
use App\Models\ProductType;
use App\Models\User;
use App\Services\Imports\ImportReportService;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Notification::fake();
    ProductType::create(['name' => 'Prestiti', 'slug' => 'prestiti']);
    $this->initiator = User::factory()->create([
        'first_name' => 'Autore', 'last_name' => 'Import',
    ]);
    $this->report = app(ImportReportService::class)->start(
        $this->initiator, ImportReportType::PRACTICES, 'assignment.xlsx'
    );
    $this->makeImport = fn (?User $defaultUser = null) => new PracticesImport(
        $defaultUser, $this->report->id, $this->report->run_uuid,
        $this->initiator->id, 'assignment.xlsx'
    );
    $this->row = [
        'nome' => 'Cliente', 'cognome' => 'Test',
        'cf_cl' => 'RSSMRA80A01H501U', 'recapito_cell' => '3331234567',
        'applicazione' => 'prestito personale', 'data_prima_rata' => '2026-01-01',
    ];
    $this->importRow = function (array $values = [], ?User $defaultUser = null): ?Practice {
        $import = ($this->makeImport)($defaultUser);
        $import->rememberRowNumber(2);

        return $import->model(array_merge($this->row, $values))?->fresh();
    };
});

test('explicit modal assignment takes precedence over Excel and initiator', function () {
    $modal = User::factory()->create();
    $excel = User::factory()->create(['first_name' => 'Mario', 'last_name' => 'Rossi']);
    $practice = ($this->importRow)(['nome_agenzia' => $excel->full_name], $modal);

    expect($practice)->not->toBeNull()
        ->and($practice->user_id)->toBe($modal->id)
        ->and($practice->customer->user_id)->toBe($modal->id);
});

test('Excel matches an exact normalized full name in either order', function (string $name) {
    $excel = User::factory()->create(['first_name' => '  Mario ', 'last_name' => ' De   Rossi  ']);
    $practice = ($this->importRow)(['nome_agenzia' => $name]);

    expect($practice)->not->toBeNull()
        ->and($practice->user_id)->toBe($excel->id)
        ->and($practice->customer->user_id)->toBe($excel->id);
})->with(['Mario De Rossi', "  mARIO\t DE   rOSSI  ", 'De Rossi Mario']);

test('missing invalid partial and wildcard names fall back to the initiator', function (array $values) {
    User::factory()->create(['first_name' => 'Mario', 'last_name' => 'Rossini']);
    $practice = ($this->importRow)($values);

    expect($practice)->not->toBeNull()
        ->and($practice->user_id)->toBe($this->initiator->id)
        ->and($practice->customer->user_id)->toBe($this->initiator->id);
})->with([
    'missing column' => [[]],
    'null' => [['nome_agenzia' => null]],
    'empty' => [['nome_agenzia' => '']],
    'whitespace' => [['nome_agenzia' => '   ']],
    'unknown' => [['nome_agenzia' => 'Utente Inesistente']],
    'not Rossini' => [['nome_agenzia' => 'Mario Rossi']],
    'percent' => [['nome_agenzia' => '%']],
    'underscore' => [['nome_agenzia' => '_']],
    'embedded wildcard' => [['nome_agenzia' => 'Mario Ross%']],
]);

test('ambiguous names including reversed collisions fall back to the initiator', function (string $first, string $last) {
    User::factory()->create(['first_name' => 'Mario', 'last_name' => 'Rossi']);
    User::factory()->create(['first_name' => $first, 'last_name' => $last]);
    $practice = ($this->importRow)(['nome_agenzia' => 'Mario Rossi']);

    expect($practice)->not->toBeNull()
        ->and($practice->user_id)->toBe($this->initiator->id);
})->with([['Mario', 'Rossi'], ['Rossi', 'Mario'], [' MARIO ', ' ROSSI ']]);

test('the same user matching both name orders is counted only once', function () {
    $excel = User::factory()->create(['first_name' => 'Andrea', 'last_name' => 'Andrea']);
    $practice = ($this->importRow)(['nome_agenzia' => 'Andrea Andrea']);

    expect($practice?->user_id)->toBe($excel->id);
});

test('soft deleted users are excluded from matching and ambiguity checks', function (bool $hasActiveMatch) {
    $deleted = User::factory()->create(['first_name' => 'Mario', 'last_name' => 'Rossi']);
    $deleted->delete();
    $active = $hasActiveMatch
        ? User::factory()->create(['first_name' => 'Mario', 'last_name' => 'Rossi'])
        : $this->initiator;
    $practice = ($this->importRow)(['nome_agenzia' => 'Mario Rossi']);

    expect($practice)->not->toBeNull()
        ->and($practice->user_id)->toBe($active->id);
})->with([false, true]);

test('a real XLSX Nome Agenzia heading assigns each row independently', function () {
    $first = User::factory()->create(['first_name' => 'Mario', 'last_name' => 'Rossi']);
    $second = User::factory()->create(['first_name' => 'Lucia', 'last_name' => 'Bianchi']);
    $sheet = new Spreadsheet();
    $sheet->getActiveSheet()->fromArray([
        ['Nome', 'Cognome', 'CF CL', 'Recapito Cell', 'Applicazione', 'Data Prima Rata', 'Nome Agenzia'],
        ['Cliente', 'Uno', 'RSSMRA80A01H501U', '3331234567', 'prestito personale', '2026-01-01', 'Mario Rossi'],
        ['Cliente', 'Due', 'BNCLCU80A01H501U', '3331234568', 'prestito personale', '2026-01-01', 'Bianchi Lucia'],
    ]);
    $path = tempnam(sys_get_temp_dir(), 'practice-assignment-');

    try {
        (new Xlsx($sheet))->save($path);
        // The test environment uses the synchronous queue; execute actual chunks.
        Excel::queueImport(($this->makeImport)(), $path, null, \Maatwebsite\Excel\Excel::XLSX);

        expect(Practice::count())->toBe(2);
        foreach (['RSSMRA80A01H501U' => $first, 'BNCLCU80A01H501U' => $second] as $taxId => $user) {
            $customer = Customer::where('tax_id', $taxId)->firstOrFail();
            expect(Practice::where('customer_id', $customer->id)->sole()->user_id)->toBe($user->id)
                ->and($customer->user_id)->toBe($user->id);
        }
    } finally {
        unlink($path);
        $sheet->disconnectWorksheets();
    }
});

test('an unavailable initiator fails the row instead of assigning a superadmin', function () {
    Role::findOrCreate('superadmin');
    User::factory()->create()->assignRole('superadmin');
    $this->initiator->delete();
    $practice = ($this->importRow)();
    $failure = $this->report->rows()->sole();

    expect($practice)->toBeNull()
        ->and(Practice::count())->toBe(0)
        ->and(Customer::count())->toBe(0)
        ->and($failure->status)->toBe(ImportReportRowStatus::FAILED)
        ->and($failure->message)->toContain('autore dell\'import non disponibile')
        ->and($failure->errors)->not->toBeEmpty();
});

test('existing customer and lead ownership remains unchanged', function (CustomerStatus $status, bool $hasTaxId) {
    $owner = User::factory()->create();
    $excel = User::factory()->create(['first_name' => 'Mario', 'last_name' => 'Rossi']);
    $customer = Customer::factory()->create([
        'user_id' => $owner->id, 'customer_status' => $status, 'lead_status' => null,
        'first_name' => $this->row['nome'], 'last_name' => $this->row['cognome'],
        'phone' => $this->row['recapito_cell'],
        'tax_id' => $hasTaxId ? $this->row['cf_cl'] : null,
    ]);
    $practice = ($this->importRow)(['nome_agenzia' => 'Mario Rossi']);

    expect($practice)->not->toBeNull()
        ->and($practice->user_id)->toBe($excel->id)
        ->and($practice->customer_id)->toBe($customer->id)
        ->and($customer->fresh()->user_id)->toBe($owner->id)
        ->and(Customer::count())->toBe(1);
})->with([
    [CustomerStatus::CUSTOMER, true],
    [CustomerStatus::LEAD, true],
    [CustomerStatus::LEAD, false],
]);
