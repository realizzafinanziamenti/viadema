<?php

namespace App\Models;

use App\Enums\PracticeStatus;
use App\Observers\PracticeObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(PracticeObserver::class)]
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

        // Snapshot
        'product_subtype_label',          // snapshot del tipo di prodotto (es. Mutuo Under 36)
        'financial_table_percentage',     // snapshot della percentuale della tabella finanziaria (es. 0.50, 1.00, 1.50)
        'insurance_label',              // snapshot dell'assicurazione (es. Assicurazione Casa, Assicurazione Vita)
        'installment_value',              // snapshot del numero di rate (es. 12, 24, 36)
        'customer_type_label',            // snapshot della tipologia cliente

        // Importi finanziari
        'amount_disbursed',       // finanziato
        'total_amount',           // montante
        'rate_amount',            // importo rata
        'tan',                    // TAN
        'teg',                    // TEG
        'taeg',                   // TAEG

        // Date
        'inserted_at',            // inserimento sistema
        'first_installment_date',         // data prima rata
        'last_installment_date',          // data ultima rata
        'early_settlement_date',                // data liquidazione
        'disbursement_date',        // data estinzione anticipata

        // Rinnovo
        'renewability_percentage',  // percentuale di rinnovo su ammortamento
        'renewability_date',           // data rinnovabilità (calcolata)
        'percentage_alert',       // percentuale di alert su rinnovo
        'alert_date',             // data alert (calcolata)

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

        'financial_table_percentage' => 'decimal:2',
        'installment_value' => 'integer',

        'inserted_at' => 'date',
        'first_installment_date' => 'date',
        'last_installment_date' => 'date',
        'early_settlement_date' => 'date',
        'disbursement_date' => 'date',

        'renewability_percentage' => 'decimal:2',
        'renewability_date' => 'datetime',
        'percentage_alert' => 'decimal:2',
        'alert_date' => 'datetime',

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
     * Get the user associated with the practice.
     */
    public function user(): BelongsTo
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
     * Get the events associated with the practice.
     */
    public function event(): HasOne
    {
        return $this->hasOne(Event::class);
    }

    /**
     * Get the customer type associated with the practice.
     */
    public function customerType(): BelongsTo
    {
        return $this->belongsTo(CustomerType::class);
    }

    /**
     * Get the attachments associated with the practice.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // END RELATIONSHIPS

    // ACCESSORS

    /**
     * Accessor to obtain formatted first installment date.
     */
    protected function formattedFirstInstallmentDate(): Attribute
    {
        return Attribute::get(fn() => $this->first_installment_date?->format('d/m/y'));
    }

    /**
     * Accessor to obtain formatted early settlement date.
     */
    protected function formattedEarlySettlementDate(): Attribute
    {
        return Attribute::get(fn() => $this->early_settlement_date?->format('d/m/y'));
    }

    /**
     * Accessor to obtain formatted disbursement date.
     */
    protected function formattedDisbursementDate(): Attribute
    {
        return Attribute::get(fn() => $this->disbursement_date?->format('d/m/y'));
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
        return $query
            ->when(
                $expired === true,
                fn($q) => $q->where('practice_status', PracticeStatus::DISBURSED->value)
            )
            ->when(
                $expired === false,
                fn($q) => $q->where('practice_status', '!=', PracticeStatus::DISBURSED->value)
            );
    }

    /**
     * Scope a query to filter by search
     */
    public function scopeFilterBySearch(Builder $query, string $search)
    {
        $search = trim($search);

        return $query->when($search, function ($query) use ($search) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $search . '%'])
                    ->orWhere('tax_id', 'like', "%{$search}%");
            })->orWhere('practice_code', 'like', "%{$search}%");
        });
    }

    // END SCOPES
}
