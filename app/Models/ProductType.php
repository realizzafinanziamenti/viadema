<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductType extends Model
{
    protected $fillable = ['name', 'slug'];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the practices associated with the product type.
     */
    public function practices(): HasMany
    {
        return $this->hasMany(Practice::class);
    }

    /**
     * Get the installments associated with the product type.
     */
    public function installments(): BelongsToMany
    {
        return $this->belongsToMany(Installment::class, 'installment_product_default')
            ->using(InstallmentProductDefault::class)
            ->withPivot('renewability_percentage', 'percentage_alert', 'alert_months')
            ->withTimestamps();
    }
}
