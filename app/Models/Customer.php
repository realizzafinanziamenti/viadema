<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\LeadCommunication;
use App\Enums\LeadStatus;
use App\Observers\CustomerObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy(CustomerObserver::class)]
class Customer extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'customer_type_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'date_of_birth',
        'tax_id',
        'address',
        'city',
        'state',
        'postal_code',
        'customer_status', // LEAD or CUSTOMER
        'lead_status', // NEW, CONTACTED, WAITING_REPLY, etc.
        'notes',
        'recontact_date',
        'recontact_notified_for_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'datetime',
            'customer_status' => CustomerStatus::class,
            'lead_status' => LeadStatus::class,
            'recontact_date' => 'date',
            'recontact_notified_for_date' => 'date',
        ];
    }

    // ACTIVITY LOGGING
    /**
     * Activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        $logName = $this->isLead() ? 'lead' : 'customer';

        return LogOptions::defaults()
            ->logOnly([
                'user_id',
                'customer_type_id',
                'first_name',
                'last_name',
                'phone',
                'email',
                'date_of_birth',
                'tax_id',
                'address',
                'city',
                'state',
                'postal_code',
                'customer_status',
                'lead_status',
                'recontact_date',
                'notes',
            ])
            ->logOnlyDirty() // Solo campi che sono stati modificati
            ->useLogName($logName) // Nome del log
            ->dontSubmitEmptyLogs() // Non creare log se non ci sono modifiche
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => $this->isLead() ? "Lead {$this->full_name} creato" : "Cliente {$this->full_name} creato",
                'updated' => $this->isLead() ? "Lead {$this->full_name} modificato" : "Cliente {$this->full_name} modificato",
                'deleted' => $this->isLead() ? "Lead {$this->full_name} eliminato" : "Cliente {$this->full_name} eliminato",
                'restored' => $this->isLead() ? "Lead {$this->full_name} ripristinato" : "Cliente {$this->full_name} ripristinato",
                default => $this->isLead() ? "Lead {$eventName}" : "Cliente {$eventName}"
            });
    }

    /**
     * Customize activity before saving
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        // Aggiunge l'URL della pratica (se non è stata eliminata)
        if ($eventName !== 'deleted') {
            if ($this->isLead()) {
                $activity->properties = $activity->properties->put('url', route('lead.show', $this->id));
            } elseif ($this->isCustomer()) {
                $activity->properties = $activity->properties->put('url', route('customer.show', $this->id));
            }
        }

        $activity->properties = $activity->properties->merge([
            'field_translations' => [
                // Relazioni
                'user_id' => 'Collaboratore',
                'customer_type_id' => 'Tipologia cliente',

                // Dati anagrafici
                'first_name' => 'Nome',
                'last_name' => 'Cognome',
                'email' => 'Email',
                'phone' => 'Telefono',
                'date_of_birth' => 'Data di nascita',
                'tax_id' => 'Codice fiscale',

                // Indirizzo
                'address' => 'Indirizzo',
                'city' => 'Città',
                'state' => 'Provincia',
                'postal_code' => 'CAP',

                // Status e lead
                'customer_status' => 'Stato cliente',
                'lead_status' => 'Stato lead',
                'recontact_date' => 'Data ricontatto',

                // Note
                'notes' => 'Note',
            ],
        ]);
    }
    // END ACTIVITY LOGGING

    /**
     * Check if the customer is a CUSTOMER.
     */
    public function isCustomer(): bool
    {
        return $this->customer_status === CustomerStatus::CUSTOMER;
    }

    /**
     * Check if the customer is a LEAD.
     */
    public function isLead(): bool
    {
        return $this->customer_status === CustomerStatus::LEAD;
    }

    // RELATIONSHIPS

    /**
     * Get the user that owns the customer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the practices for the customer.
     */
    public function practices(): HasMany
    {
        return $this->hasMany(Practice::class);
    }

    /**
     * Get the customer type that owns the customer.
     */
    public function customerType(): BelongsTo
    {
        return $this->belongsTo(CustomerType::class);
    }

    // END RELATIONSHIPS

    // ACCESSORS

    /**
     * Accessor to obtain full name.
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn() => "{$this->first_name} {$this->last_name}");
    }

    /**
     * Accessor to obtain formatted date of birth.
     */
    protected function formattedDateOfBirth(): Attribute
    {
        return Attribute::get(fn() => $this->date_of_birth?->format('d/m/y'));
    }

    /**
     * Accessor to obtain formatted created at.
     */
    protected function formattedCreatedAt(): Attribute
    {
        return Attribute::get(fn() => $this->created_at?->format('d/m/Y'));
    }

    /**
     * Accessor to obtain formatted updated at.
     */
    protected function formattedUpdatedAt(): Attribute
    {
        return Attribute::get(fn() => $this->updated_at?->format('d/m/Y'));
    }

    /**
     * Accessor to obtain formatted number.
     * Example: T00001
     */
    protected function formattedId(): Attribute
    {
        return Attribute::get(fn() => 'T' . str_pad($this->id, 5, '0', STR_PAD_LEFT));
    }

    // END ACCESSORS

    // SCOPES

    /**
     * Scope a query to filter customer by customer status
     */
    public function scopeCustomers(Builder $query)
    {
        return $query->where('customer_status', CustomerStatus::CUSTOMER);
    }

    /**
     * Scope a query to filter lead by customer status
     */
    public function scopeLeads(Builder $query)
    {
        return $query->where('customer_status', CustomerStatus::LEAD);
    }

    /**
     * Scope a query to filter by search
     */
    public function scopeFilterBySearch(Builder $query, string $search)
    {
        $search = trim($search);

        return $query->when($search, function ($query) use ($search) {
            $query->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $search . '%']);
        });
    }
    public function practiceOpportunities(): HasMany
{
    return $this->hasMany(PracticeOpportunity::class);
}
/**
 * Latest practice opportunity associated with the customer profile.
 */
public function latestPracticeOpportunity(): HasOne
{
    return $this->hasOne(PracticeOpportunity::class)
        ->latestOfMany();
}

    /**
     * Scope a query to filter practices for a given department/role.
     */
    public function scopeFilteredForDepartment(Builder $query)
    {
        if (auth()->user()->isConsultant() || auth()->user()->isExternal()) {
            return $query->where('customer_status', CustomerStatus::LEAD->value)
                ->where('user_id', auth()->id());
        }

        return $query;
    }

    // END SCOPES
}