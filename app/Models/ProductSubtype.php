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
}
