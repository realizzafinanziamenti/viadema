<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormDocument extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
    ];

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
