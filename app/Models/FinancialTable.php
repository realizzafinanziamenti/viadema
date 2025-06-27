<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialTable extends Model
{
    protected $fillable = ['percentage'];

    protected $casts = [
        'percentage' => 'decimal:2',
    ];

    /**
     * Get the practices associated with the financial table.
     */
    public function practices(): HasMany
    {
        return $this->hasMany(Practice::class);
    }
}
