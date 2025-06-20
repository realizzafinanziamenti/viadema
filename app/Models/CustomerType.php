<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerType extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the practices associated with the customer type.
     */
    public function practices(): HasMany
    {
        return $this->hasMany(Practice::class);
    }

    /**
     * Check if the product subtype can be edited.
     * A product subtype is editable if it has no associated practices.
     */
    public function isEditable(): bool
    {
        return !$this->practices()->exists();
    }

    /**
     * Get the customers associated with the customer type.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
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
