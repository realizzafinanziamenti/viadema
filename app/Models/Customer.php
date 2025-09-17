<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\LeadCommunication;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes, HasFactory;

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
        'lead_source', // Example: 'Tik Tok', 'Meta', 'Search Engine', 'Referral', etc.
        'lead_status', // NEW, CONTACTED, WAITING_REPLY, etc.
        'notes',
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
            'lead_source' => LeadSource::class,
            'lead_status' => LeadStatus::class,
        ];
    }

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

    // END SCOPES
}
