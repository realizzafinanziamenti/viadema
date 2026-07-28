<?php

namespace App\Models;

use App\Enums\ImportReportStatus;
use App\Enums\ImportReportType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'run_uuid',
        'file_name',
        'status',
        'total_rows',
        'imported_rows',
        'failed_rows',
        'started_at',
        'completed_at',
        'viewed_at',
        'error_message',
    ];

    protected $casts = [
        'type' => ImportReportType::class,
        'status' => ImportReportStatus::class,

        'total_rows' => 'integer',
        'imported_rows' => 'integer',
        'failed_rows' => 'integer',

        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'viewed_at' => 'datetime',
    ];

    /**
     * User who started the import.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Rows processed during the import.
     */
    public function rows(): HasMany
    {
        return $this->hasMany(ImportReportRow::class);
    }

    /**
     * Restrict the query to a user's report for a specific import type.
     */
    public function scopeForUserAndType(
        Builder $query,
        int $userId,
        ImportReportType $type
    ): Builder {
        return $query
            ->where('user_id', $userId)
            ->where('type', $type->value);
    }

    /**
     * Determine whether the import is still running.
     */
    public function isRunning(): bool
    {
        return in_array(
            $this->status,
            [
                ImportReportStatus::PENDING,
                ImportReportStatus::PROCESSING,
            ],
            true
        );
    }

    /**
     * Determine whether the import has finished.
     */
    public function isFinished(): bool
    {
        return in_array(
            $this->status,
            [
                ImportReportStatus::COMPLETED,
                ImportReportStatus::FAILED,
            ],
            true
        );
    }

    /**
     * Ensure that a queued job still belongs to the current execution.
     */
    public function matchesRun(string $runUuid): bool
    {
        return $this->run_uuid === $runUuid;
    }
}