<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Installment extends Model
{
    protected $fillable = ['value'];

    protected $casts = [
        'value' => 'integer',
    ];

    /**
     * Get the practices associated with the installment.
     */
    public function practices(): HasMany
    {
        return $this->hasMany(Practice::class);
    }
}
