<?php

namespace App\Models;

use App\Enums\PracticeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Practice extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'product_type_id',  // tipo prodotto
        'product_subtype_id',  // sottotipo prodotto
        'user_id',  // collaboratore
        'customer_id',  // cliente
        'financial_table_id',  // tabella/piano finanziario
        'insurance_id',  // assicurazione
        'installment_id',  // numero rate
        'customer_type_id',  // tipologia cliente
        'previous_finance',  // finanziaria estinta
        'inserted_at',  // data di inserimento in sistema
        'started_at',  // data di apertura pratica
        'paid_at',  // data liquidazione
        'extinguished_at',  // data di estinzione
        'renewable_at',  // data di rinnovo
        'practice_status',  // stato prodotto
        'rate_amount',  // importo rata
        'tan',  // TAN
        'teg',  // TEG
        'taeg',  // TAEG
        'notes', // note
        'practice_code',  // id univoco pratica
    ];

    protected $casts = [
        'practice_status' => PracticeStatus::class,
        'inserted_at' => 'datetime',
        'started_at' => 'datetime',
        'paid_at' => 'datetime',
        'extinguished_at' => 'datetime',
        'renewable_at' => 'datetime',
        'rate_amount' => 'decimal:2',
        'tan' => 'decimal:3',
        'teg' => 'decimal:2',
        'taeg' => 'decimal:2',
    ];

    // RELATIONSHIPS

    /**
     * Get the product type associated with the practice.
     */
    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * Get the product subtype associated with the practice.
     */
    public function productSubtype(): BelongsTo
    {
        return $this->belongsTo(ProductSubtype::class);
    }

    /**
     * Get the team member associated with the practice.
     */
    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer associated with the practice.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the financial table associated with the practice.
     */
    public function financialTable(): BelongsTo
    {
        return $this->belongsTo(FinancialTable::class);
    }

    /**
     * Get the insurance associated with the practice.
     */
    public function insurance(): BelongsTo
    {
        return $this->belongsTo(Insurance::class);
    }

    /**
     * Get the installment associated with the practice.
     */
    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    /**
     * Get the customer type associated with the practice.
     */
    public function customerType(): BelongsTo
    {
        return $this->belongsTo(CustomerType::class);
    }

    // END RELATIONSHIPS
}
