<?php

namespace App\Models;

use App\Enums\EventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory, SoftDeletes, LogsActivity;

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

    // ACTIVITY LOGGING
    /**
     * Activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                // Relazioni
                'user_id' => 'Creatore',
                'practice_id' => 'Pratica associata',

                // Dati evento
                'event_type' => 'Tipo evento',
                'title' => 'Titolo',
                'description' => 'Descrizione',
                'start_date' => 'Data evento',
                'start_time' => 'Ora inizio',
                'end_time' => 'Ora fine',
                'is_all_day' => 'Tutto il giorno',
            ])
            ->logOnlyDirty() // Solo campi che sono stati modificati
            ->useLogName('user') // Nome del log
            ->dontSubmitEmptyLogs() // Non creare log se non ci sono modifiche
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => "Evento {$this->full_name} creato",
                'updated' => "Evento {$this->full_name} modificato",
                'deleted' => "Evento {$this->full_name} eliminato",
                'restored' => "Evento {$this->full_name} ripristinato",
                default => "Evento {$eventName}"
            });
    }

    /**
     * Customize activity before saving
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        // Prepara le properties base
        $activity->properties = $activity->properties->merge([
            'field_translations' => [
                // Relazioni
                'user_id' => 'Creatore',
                'practice_id' => 'Pratica associata',

                // Dati evento
                'event_type' => 'Tipo evento',
                'title' => 'Titolo',
                'description' => 'Descrizione',
                'start_date' => 'Data evento',
                'start_time' => 'Ora inizio',
                'end_time' => 'Ora fine',
                'is_all_day' => 'Tutto il giorno',
            ],
        ]);
    }
    // END ACTIVITY LOGGING

    // RELATIONSHIPS

    /**
     * Get the user that owns the events.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The participants that belong to the event.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user', 'event_id', 'user_id')->withTimestamps();
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

        return $query->where(function ($query) use ($user) {
            $query->where('user_id', $user->id)  // events created by the user
                ->orWhereHas('participants', function ($participantQuery) use ($user) {
                    $participantQuery->where('user_id', $user->id);  // events the user is participating in
                });
        });
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
