<?php

namespace App\Models;

use App\Enums\ImportReportRowStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportReportRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_report_id',
        'run_uuid',
        'row_number',
        'status',
        'entity_type',
        'entity_id',
        'label',
        'message',
        'errors',
        'raw_data',
    ];

    protected $casts = [
        'status' => ImportReportRowStatus::class,
        'row_number' => 'integer',
        'entity_id' => 'integer',
        'errors' => 'array',
        'raw_data' => 'array',
    ];

    /**
     * Import report containing this row.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(ImportReport::class, 'import_report_id');
    }

    /**
     * Restrict rows to one specific import execution.
     */
    public function scopeForRun(
        Builder $query,
        string $runUuid
    ): Builder {
        return $query->where('run_uuid', $runUuid);
    }

    /**
     * Restrict rows to a specific result.
     */
    public function scopeWithStatus(
        Builder $query,
        ImportReportRowStatus $status
    ): Builder {
        return $query->where('status', $status->value);
    }

    /**
     * Determine whether the row belongs to the supplied execution.
     */
    public function matchesRun(string $runUuid): bool
    {
        return $this->run_uuid === $runUuid;
    }
}