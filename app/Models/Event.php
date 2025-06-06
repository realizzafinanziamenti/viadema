<?php

namespace App\Models;

use App\Enums\EventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'practice_id',
        'event_type',
        'title',
        'description',
        'start_date',
        'start_time',
        'end_time',
        'is_all_day',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'event_type' => EventType::class,
        'start_date' => 'date:Y-m-d',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
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

    /**
     * Get the practice that owns the events.
     */
    public function practice(): BelongsTo
    {
        return $this->belongsTo(Practice::class);
    }

    // END RELATIONSHIPS

    // ACCESSORS

    /**
     * Accessor to obtain formatted start date.
     */
    protected function formattedStartDate(): Attribute
    {
        return Attribute::get(fn() => $this->start_date?->format('d/m/Y'));
    }

    /**
     * Accessor to obtain formatted start time.
     */
    protected function formattedStartTime(): Attribute
    {
        return Attribute::get(fn() => $this->start_time?->format('H:i'));
    }

    /**
     * Accessor to obtain formatted end time.
     */
    protected function formattedEndTime(): Attribute
    {
        return Attribute::get(fn() => $this->end_time?->format('H:i'));
    }

    // END ACCESSORS

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

    /**
     * Scope a query to filter by search
     */
    public function scopeFilterBySearch(Builder $query, string $search)
    {
        $search = trim($search);

        return $query->when($search, function ($query) use ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        });
    }

    /**
     * Scope a query to only return past events.
     */
    public function scopePastEvents(Builder $query)
    {
        return $query->where(function ($query) {
            $query->where('start_date', '<', today())
                ->orWhere(function ($sub) {
                    $sub->whereDate('start_date', today())
                        ->whereTime('start_time', '<', now()->format('H:i:s'));
                });
        })
            ->orderByDesc('start_date')
            ->orderByDesc('start_time');
    }

    /**
     * Scope a query to only return the upcoming events.
     */
    public function scopeUpcomingEvents(Builder $query)
    {
        return $query->where(function ($query) {
            $query->where('start_date', '>', today())
                ->orWhere(function ($sub) {
                    $sub->whereDate('start_date', today())
                        ->whereTime('start_time', '>=', now()->format('H:i:s'));
                });
        })
            ->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc');
    }

    // END SCOPES
}
