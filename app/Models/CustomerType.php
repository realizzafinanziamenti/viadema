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
}
