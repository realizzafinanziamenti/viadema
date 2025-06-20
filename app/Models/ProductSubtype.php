<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSubtype extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the practices associated with the product subtype.
     */
    public function practices(): HasMany
    {
        return $this->hasMany(Practice::class);
    }

    /**
     * Accessor to ensure the name is always trimmed.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn($value) => trim($value)
        );
    }

    /**
     * Check if the product subtype can be edited.
     * A product subtype is editable if it has no associated practices.
     */
    public function isEditable(): bool
    {
        return !$this->practices()->exists();
    }
}
