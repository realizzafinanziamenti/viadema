<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insurance extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the practices associated with the insurance.
     */
    public function practices(): HasMany
    {
        return $this->hasMany(Practice::class);
    }
}
