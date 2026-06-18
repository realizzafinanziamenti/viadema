<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetCrmData extends Command
{
    protected $signature = 'app:reset-crm-data {--force : Conferma reset dati}';

    protected $description = 'Resetta i dati CRM preservando utenti, ruoli e permessi';

    public function handle(): int
    {
        if (! $this->option('force')) {
            $this->error('Operazione bloccata. Usa --force per confermare.');
            return self::FAILURE;
        }

        if (! $this->confirm('Sei sicuro? Verranno eliminati tutti i dati CRM tranne utenti, ruoli e permessi.')) {
            return self::FAILURE;
        }

        $tables = [
            'practices',
            'practice_opportunities',
            'customers',
            'attachments',
            'events',
            'activity_log',
            'notifications',
            'jobs',
            'failed_jobs',
        ];

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    $this->warn("Tabella {$table} non trovata, salto.");
                    continue;
                }

                DB::table($table)->delete();

                $this->resetAutoIncrement($table);

                $this->info("Tabella {$table} resettata.");
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->info('Reset CRM completato. Utenti, ruoli e permessi preservati.');

        return self::SUCCESS;
    }

    private function resetAutoIncrement(string $table): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('DELETE FROM sqlite_sequence WHERE name = ?', [$table]);
            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
        }
    }
}