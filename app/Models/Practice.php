<?php

namespace App\Models;

use App\Enums\PracticeStatus;
use App\Enums\ProductionType;
use App\Enums\UserDepartment;
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
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy(PracticeObserver::class)]
class Practice extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'customer_id',
        'practice_opportunity_id',

        'inserted_at',
        'early_settlement_date',
        'disbursement_date',
        'renewability_date',
        'alert_date',

        'practice_status',
        'days_transformation',
        'sum_dec_plus_35',
    ];

    protected $casts = [
        'inserted_at' => 'date',
        'early_settlement_date' => 'date',
        'disbursement_date' => 'date',

        'renewability_date' => 'datetime',
        'alert_date' => 'datetime',

        'practice_status' => PracticeStatus::class,
        'days_transformation' => 'integer',
        'sum_dec_plus_35' => 'decimal:2',
    ];

    // ACTIVITY LOGGING
    /**
     * Activity log options.
     */
    public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly([
            'user_id',
            'customer_id',
            'practice_opportunity_id',

            'inserted_at',
            'early_settlement_date',
            'disbursement_date',
            'renewability_date',
            'alert_date',

            'practice_status',
            'days_transformation',
            'sum_dec_plus_35',
        ])
        ->logOnlyDirty()
        ->useLogName('practice')
        ->dontSubmitEmptyLogs()
        ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
            'created' => "Pratica {$this->practice_code} creata",
            'updated' => "Pratica {$this->practice_code} modificata",
            'deleted' => "Pratica {$this->practice_code} eliminata",
            'restored' => "Pratica {$this->practice_code} ripristinata",
            default => "Pratica {$eventName}",
        });
}

    /**
     * Customize activity before saving
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        if ($eventName !== 'deleted') {
            $activity->properties = $activity->properties->put('url', route('practice.show', $this->id));
        }

        $activity->properties = $activity->properties->merge([
            'field_translations' => [
                'user_id' => 'Collaboratore',
                'customer_id' => 'Cliente',
                'practice_opportunity_id' => 'Opportunità pratica',

                'inserted_at' => 'Data inserimento sistema',
                'early_settlement_date' => 'Data estinzione anticipata',
                'disbursement_date' => 'Data liquidazione',
                'renewability_date' => 'Data rinnovabilità',
                'alert_date' => 'Data alert',

                'practice_status' => 'Stato pratica',
                'days_transformation' => 'Trasformazione GG',
                'sum_dec_plus_35' => 'Somma dec + 35%',
            ],
        ]);
    }
    // END ACTIVITY LOGGING

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
     * Accessor to obtain the practice code.
     */
    public function practiceCode(): Attribute
    {
        return Attribute::get(fn() => 'P' . str_pad($this->id, 5, '0', STR_PAD_LEFT));
    }

    /**
     * Accessor to obtain formatted inserted at date.
     */
    protected function formattedInsertedAt(): Attribute
    {
        return Attribute::get(fn() => $this->inserted_at?->format('d/m/y'));
    }

    /**
     * Accessor to obtain formatted created at date.
     */
    protected function formattedCreatedAt(): Attribute
    {
        return Attribute::get(fn() => $this->created_at?->format('d/m/y'));
    }

    /**
     * Accessor to obtain formatted first installment date.
     */
    protected function formattedFirstInstallmentDate(): Attribute
    {
        return Attribute::get(fn() => $this->first_installment_date?->format('d/m/y'));
    }

    /**
     * Accessor to obtain formatted last installment date.
     */
    protected function formattedLastInstallmentDate(): Attribute
    {
        return Attribute::get(fn() => $this->last_installment_date?->format('d/m/y'));
    }

    /**
     * Accessor to obtain formatted early settlement date.
     */
    protected function formattedEarlySettlementDate(): Attribute
    {
        return Attribute::get(fn() => $this->early_settlement_date?->format('d/m/y'));
    }

    /**
     * Accessor to obtain formatted renewability date.
     */
    protected function formattedRenewabilityDate(): Attribute
    {
        return Attribute::get(fn() => $this->renewability_date?->format('d/m/y'));
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
        return Attribute::get(fn() => $this->taeg !== null ? number_format($this->taeg, 2, ',', '.') . '%' : 'N/D');
    }

    /**
     * Accessor to obtain formatted tan.
     */
    protected function formattedTan(): Attribute
    {
        return Attribute::get(fn() => $this->tan !== null ? number_format($this->tan, 2, ',', '.') . '%' : 'N/D');
    }

    /**
     * Accessor to obtain formatted rate amount.
     */
    protected function formattedRateAmount(): Attribute
    {
        return Attribute::get(fn() => $this->rate_amount !== null ? number_format($this->rate_amount, 2, ',', '.') . '€' : 'N/D');
    }

    /**
     * Accessor to obtain formatted amount disbursed.
     */
    protected function formattedAmountDisbursed(): Attribute
    {
        return Attribute::get(fn() => $this->amount_disbursed !== null ? number_format($this->amount_disbursed, 2, ',', '.') . '€' : 'N/D');
    }

    /**
     * Accessor to obtain formatted total amount.
     */
    protected function formattedTotalAmount(): Attribute
    {
        return Attribute::get(fn() => $this->total_amount !== null ? number_format($this->total_amount, 2, ',', '.') . '€' : 'N/D');
    }

    // END ACCESSORS

    // SCOPES

    /**
     * Scope a query to only include practices of a given product type.
     */
        public function scopeFilterByProductType($query, $type): Builder
    {
        return $query->when($type, function ($q) use ($type) {
            $q->whereHas('opportunity', function ($opportunityQuery) use ($type) {
                $opportunityQuery->where('product_type_id', $type->id);
            });
        });
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
            })->orWhere('id', 'like', "%{$search}%");
        });
    }
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(PracticeOpportunity::class, 'practice_opportunity_id');
    }
    /**
     * Scope a query to filter practices for a given department/role.
     */
    public function scopeFilteredForDepartment(Builder $query)
    {
        // Coordinatore di sala vede solo le pratiche assegnate a collaboratori dello stesso dipartimento
        if (auth()->user()->isFloorManager()) {
            $floorManagerIds = User::whereHas('roles', function ($q) {
                $q->where('name', UserDepartment::FLOOR_MANAGER->value)
                    ->orWhere('name', UserDepartment::CONSULTANT->value);
            })->pluck('id');

            return $query->whereIn('user_id', $floorManagerIds);
        }

        // I consulenti e gli esterni vedono solo le loro pratiche
        if (auth()->user()->isConsultant() || auth()->user()->isExternal()) {
            return $query->where('user_id', auth()->id());
        }

        return $query;
    }

    // END SCOPES
}
