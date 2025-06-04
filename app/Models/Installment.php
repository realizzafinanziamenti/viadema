<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    /**
     * Get the product types associated with the installment.
     */
    public function productTypes(): BelongsToMany
    {
        return $this->belongsToMany(ProductType::class, 'installment_product_default')
            ->using(InstallmentProductDefault::class)
            ->withPivot('renewability_percentage', 'percentage_alert', 'alert_months')
            ->withTimestamps();
    }
}
