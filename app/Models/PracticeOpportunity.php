<?php

namespace App\Models;

use App\Enums\LeadSource;
use App\Enums\ProductionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeOpportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_type_id',
        'acquisition_channel',
        'product_subtype_id',
        'financial_table_id',
        'insurance_id',
        'installment_id',
        'customer_type_id',
        'amount_disbursed',
        'total_amount',
        'rate_amount',
        'tan',
        'teg',
        'taeg',
        'first_installment_date',
        'last_installment_date',
        'renewability_percentage',
        'percentage_alert',
        'is_renewal',
        'production_type',
        'disbursing_institution',
        'financial_institution',
        'previous_finance',
        'notes',
    ];

    protected $casts = [
        'acquisition_channel' => LeadSource::class,
        'amount_disbursed' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'rate_amount' => 'decimal:2',
        'tan' => 'decimal:3',
        'teg' => 'decimal:2',
        'taeg' => 'decimal:2',
        'first_installment_date' => 'date',
        'last_installment_date' => 'date',
        'renewability_percentage' => 'decimal:2',
        'percentage_alert' => 'decimal:2',
        'is_renewal' => 'boolean',
        'production_type' => ProductionType::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function practices(): HasMany
    {
        return $this->hasMany(Practice::class);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function productSubtype(): BelongsTo
    {
        return $this->belongsTo(ProductSubtype::class);
    }

    public function financialTable(): BelongsTo
    {
        return $this->belongsTo(FinancialTable::class);
    }

    public function insurance(): BelongsTo
    {
        return $this->belongsTo(Insurance::class);
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    public function customerType(): BelongsTo
    {
        return $this->belongsTo(CustomerType::class);
    }
}