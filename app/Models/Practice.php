<?php

namespace App\Models;

use App\Enums\PracticeStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Practice extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        // Relazioni
        'product_type_id',        // es. Cessione, Mutuo
        'product_subtype_id',     // es. Mutuo Under 36
        'user_id',                // collaboratore
        'customer_id',            // cliente
        'financial_table_id',     // tabella provvigione
        'insurance_id',           // assicurazione
        'installment_id',         // numero rate
        'customer_type_id',       // tipologia cliente

        // Importi finanziari
        'amount_disbursed',       // finanziato
        'total_amount',           // montante
        'rate_amount',            // importo rata
        'tan',                    // TAN
        'teg',                    // TEG
        'taeg',                   // TAEG

        // Date
        'inserted_at',            // inserimento sistema
        'started_at',             // data decorrenza
        'paid_at',                // data liquidazione
        'first_due_date',         // data prima rata
        'last_due_date',          // data ultima rata
        'extinguished_at',        // data estinzione anticipata
        'renewable_at',           // data rinnovabilità (calcolata)

        // Stato e flag
        'practice_status',        // stato pratica
        'days_transformation',    // trasformazione GG
        'sum_dec_plus_35',        // somma dec + 35%

        // Dettagli
        'previous_finance',       // finanziaria estinta
        'practice_code',          // ID pratica univoco
        'notes',                  // note libere
    ];

    protected $casts = [
        'amount_disbursed' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'rate_amount' => 'decimal:2',
        'tan' => 'decimal:3',
        'teg' => 'decimal:2',
        'taeg' => 'decimal:2',

        'inserted_at' => 'date',
        'started_at' => 'date',
        'paid_at' => 'date',
        'first_due_date' => 'date',
        'last_due_date' => 'date',
        'extinguished_at' => 'date',
        'renewable_at' => 'date',

        'practice_status' => PracticeStatus::class,
        'days_transformation' => 'integer',
        'sum_dec_plus_35' => 'decimal:2',
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
        return $this->belongsTo(User::class, 'user_id')
            ->withTrashed();
    }

    /**
     * Get the customer associated with the practice.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)
            ->withTrashed();
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

    // ACCESSORS

    /**
     * Accessor to obtain formatted started at date.
     */
    protected function formattedStartedAt(): Attribute
    {
        return Attribute::get(fn() => $this->started_at?->format('d/m/y'));
    }

    /**
     * Accessor to obtain formatted paid at date.
     */
    protected function formattedPaidAt(): Attribute
    {
        return Attribute::get(fn() => $this->paid_at?->format('d/m/y'));
    }

    /**
     * Accessor to obtain formatted extinguished at date.
     */
    protected function formattedExtinguishedAt(): Attribute
    {
        return Attribute::get(fn() => $this->extinguished_at?->format('d/m/y'));
    }

    /**
     * Accessor to obtain formatted taeg.
     */
    protected function formattedTaeg(): Attribute
    {
        return Attribute::get(fn() => $this->taeg !== null ? number_format($this->taeg, 2, ',', '.') . '%' : '-');
    }

    /**
     * Accessor to obtain formatted tan.
     */
    protected function formattedTan(): Attribute
    {
        return Attribute::get(fn() => $this->tan !== null ? number_format($this->tan, 2, ',', '.') . '%' : '-');
    }

    /**
     * Accessor to obtain formatted rate amount.
     */
    protected function formattedRateAmount(): Attribute
    {
        return Attribute::get(fn() => $this->rate_amount !== null ? number_format($this->rate_amount, 2, ',', '.') . '€' : '-');
    }

    /**
     * Accessor to obtain formatted amount disbursed.
     */
    protected function formattedAmountDisbursed(): Attribute
    {
        return Attribute::get(fn() => $this->amount_disbursed !== null ? number_format($this->amount_disbursed, 2, ',', '.') . '€' : '-');
    }

    /**
     * Accessor to obtain formatted total amount.
     */
    protected function formattedTotalAmount(): Attribute
    {
        return Attribute::get(fn() => $this->total_amount !== null ? number_format($this->total_amount, 2, ',', '.') . '€' : '-');
    }

    // END ACCESSORS

    // SCOPES

    /**
     * Scope a query to only include practices of a given product type.
     */
    public function scopeFilterByProductType($query, $type): Builder
    {
        return $query->when($type, fn($q) => $q->where('product_type_id', $type->id));
    }

    /**
     * Scope a query to only include expired practices.
     */
    public function scopeIsExpired($query, bool $expired): Builder
    {
        $now = now();

        return $query
            ->when(
                $expired === true,
                fn($q) => $q
                    ->whereNotNull('extinguished_at')
                    ->where('extinguished_at', '<=', $now)
            )
            ->when(
                $expired === false,
                fn($q) => $q
                    ->where(function ($q) use ($now) {
                        $q->whereNull('extinguished_at')
                            ->orWhere('extinguished_at', '>', $now);
                    })
            );
    }

    // END SCOPES
}
