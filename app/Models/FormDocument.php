<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FormDocument extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'title',
        'description',
    ];

    // ACTIVITY LOGGING
    /**
     * Activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'description',
            ])
            ->logOnlyDirty() // Solo campi che sono stati modificati
            ->useLogName('form_document') // Nome del log
            ->dontSubmitEmptyLogs() // Non creare log se non ci sono modifiche
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => "Modulistica {$this->full_name} caricata",
                'updated' => "Modulistica {$this->full_name} modificata",
                'deleted' => "Modulistica {$this->full_name} eliminata",
                'restored' => "Modulistica {$this->full_name} ripristinata",
                default => "Modulistica {$eventName}"
            });
    }

    /**
     * Customize activity before saving
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        // Aggiunge le traduzioni dei campi
        $activity->properties = $activity->properties->merge([
            'field_translations' => [
                'title' => 'Titolo',
                'description' => 'Descrizione',
            ],
        ]);
    }
    // END ACTIVITY LOGGING

    /**
     * Get the attachment associated with the form document.
     */
    public function attachment(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable');
    }

    /**
     * Scope a query to filter by search
     */
    public function scopeFilterBySearch(Builder $query, string $search)
    {
        $search = trim($search);

        return $query->when($search, function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%");
        });
    }

    /**
     * Accessor to obtain formatted created at.
     */
    protected function formattedCreatedAt(): Attribute
    {
        return Attribute::get(fn() => $this->created_at?->format('d/m/y'));
    }

    /**
     * Scope a query to filter by date
     */
    public function scopeFilterByDate(Builder $query, string $date)
    {
        return $query->when($date, function ($query) use ($date) {
            $query->whereDate('created_at', $date);
        });
    }
}
