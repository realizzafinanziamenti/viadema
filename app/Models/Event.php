<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'is_all_day',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_all_day' => 'boolean',
    ];

    // RELATIONSHIPS

    /**
     * Get the user that owns the events.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // END RELATIONSHIPS

    // SCOPES

    /**
     * Scope a query to only include events for the current user.
     */
    public function scopeVisibleByUser(Builder $query, User $user): Builder
    {
        if ($user->can('view all events')) {       // superadmin
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    // END SCOPES
}
