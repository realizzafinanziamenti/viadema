<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class InstallmentProductDefault extends Pivot
{
    protected $table = 'installment_product_default';

    protected $fillable = [
        'installment_id',
        'product_type_id',
        'renewability_percentage',
        'percentage_alert',
        'alert_months',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'renewability_percentage' => 'decimal:2',
            'percentage_alert' => 'decimal:2',
            'alert_months' => 'integer',
        ];
    }

    /**
     * Get the installment associated with the defaults.
     */
    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    /**
     * Get the product type associated with the defaults.
     */
    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }
}
