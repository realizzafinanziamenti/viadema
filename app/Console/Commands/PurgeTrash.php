<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Practice;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

class PurgeTrash extends Command
{
    protected $signature = 'trash:purge';

    protected $description = 'Elimina definitivamente gli elementi nel cestino da oltre 30 giorni';

    public function handle(): int
    {
        $threshold = now()->subDays(30);

        $deletedCount = 0;
        $failedCount = 0;

        /*
         * Eliminiamo prima le pratiche.
         *
         * È intenzionale: sono entità dipendenti dai Customer
         * ed evitiamo problemi con eventuali foreign key.
         */
        $this->purgeModel(
            modelClass: Practice::class,
            threshold: $threshold,
            deletedCount: $deletedCount,
            failedCount: $failedCount
        );

        /*
         * Poi Customer e Lead.
         *
         * Entrambi vivono nella tabella customers.
         */
        $this->purgeModel(
            modelClass: Customer::class,
            threshold: $threshold,
            deletedCount: $deletedCount,
            failedCount: $failedCount
        );

        $this->info(
            "Purge completato: {$deletedCount} eliminati, {$failedCount} errori."
        );

        return $failedCount === 0
            ? self::SUCCESS
            : self::FAILURE;
    }

    private function purgeModel(
        string $modelClass,
        $threshold,
        int &$deletedCount,
        int &$failedCount
    ): void {
        $modelClass::onlyTrashed()
            ->where(
                'deleted_at',
                '<=',
                $threshold
            )
            ->orderBy('id')
            ->chunkById(
                100,
                function ($entities) use (
                    &$deletedCount,
                    &$failedCount
                ): void {
                    foreach ($entities as $entity) {
                        try {
                            $entity->forceDelete();

                            $deletedCount++;
                        } catch (Throwable $e) {
                            $failedCount++;

                            $this->logFailure(
                                $entity,
                                $e
                            );
                        }
                    }
                }
            );
    }

    private function logFailure(
        Model $entity,
        Throwable $exception
    ): void {
        Log::error(
            'Errore durante il purge automatico del cestino',
            [
                'model' => $entity::class,
                'model_id' => $entity->getKey(),
                'deleted_at' => $entity->deleted_at,
                'exception' => $exception,
            ]
        );
    }
}
