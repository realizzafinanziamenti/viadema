<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserDepartment;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Namu\WireChat\Traits\Chatable;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes, Chatable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'profile_photo_path',
        'notifications_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notifications_enabled' => 'boolean',
        ];
    }

    // ACTIVITY LOGGING
    /**
     * Activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'first_name',
                'last_name',
                'email',
                'profile_photo_path',
                'notifications_enabled',
                'password',
            ])
            ->logOnlyDirty() // Solo campi che sono stati modificati
            ->useLogName('user') // Nome del log
            ->dontSubmitEmptyLogs() // Non creare log se non ci sono modifiche
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => "Collaboratore {$this->full_name} creato",
                'updated' => "Collaboratore {$this->full_name} modificato",
                'deleted' => "Collaboratore {$this->full_name} eliminato",
                'restored' => "Collaboratore {$this->full_name} ripristinato",
                default => "Collaboratore {$eventName}"
            });
    }

    /**
     * Customize activity before saving
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        // Nasconde il valore della password
        if (isset($activity->properties['attributes']['password'])) {
            $activity->properties = $activity->properties->put(
                'attributes',
                collect($activity->properties['attributes'])
                    ->except('password')
                    ->put('password', '[NASCOSTA]')
                    ->toArray()
            );

            if (isset($activity->properties['old']['password'])) {
                $activity->properties = $activity->properties->put(
                    'old',
                    collect($activity->properties['old'])
                        ->except('password')
                        ->put('password', '[NASCOSTA]')
                        ->toArray()
                );
            }
        }

        // Aggiunge l'URL del collaboratore (se non è stato eliminato)
        if ($eventName !== 'deleted') {
            $activity->properties = $activity->properties->put('url', route('user.show', $this->id));
        }

        $activity->properties = $activity->properties->merge([
            'field_translations' => [
                'first_name' => 'Nome',
                'last_name' => 'Cognome',
                'email' => 'Email',
                'profile_photo_path' => 'Foto profilo',
                'notifications_enabled' => 'Notifiche abilitate',
                'password' => 'Password',
            ],
        ]);
    }
    // END ACTIVITY LOGGING

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->full_name)
            ->explode(' ')
            ->map(fn(string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /**
     * Check if the user role is superamin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Check if the user role is observer
     */
    public function isObserver(): bool
    {
        return $this->hasRole('observer');
    }

    /**
     * Get role description
     */
    public function getRoleDescription(): string
    {
        $role = $this->getRoleNames()->first();

        if ($role === 'superadmin') {
            return 'SuperAdmin';
        } else {
            return UserDepartment::tryFrom($role)->getLabelText() ?? 'Ruolo non definito';
        }
    }

    /**
     * Get the profile photo path.
     */
    public function getProfilePhotoUrl(): string
    {
        return $this->profile_photo_path
            ? asset("storage/{$this->profile_photo_path}")
            : asset('images/placeholder-user.jpg');
    }

    /**
     * Accessor to obtain full name.
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn() => "{$this->first_name} {$this->last_name}");
    }

    /**
     * Accessor to obtain the user's department based on their role.
     *
     * @return Attribute<UserDepartment|null>
     */
    public function department(): Attribute
    {
        return Attribute::get(function () {
            $role = $this->roles->pluck('name')->first();
            return $role ? UserDepartment::from($role) : null;
        });
    }

    /**
     * Accessor to obtain formatted number.
     * Example: T00001
     */
    protected function formattedId(): Attribute
    {
        return Attribute::get(fn() => 'T' . str_pad($this->id, 5, '0', STR_PAD_LEFT));
    }


    // RELATIONSHIPS

    /**
     * Get the user's profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the user's customers.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get the user's practices.
     */
    public function practices(): HasMany
    {
        return $this->hasMany(Practice::class);
    }

    /**
     * Get the user's events.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * The events that the user is participating in.
     */
    public function sharedEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_user', 'user_id', 'event_id')->withTimestamps();
    }

    // END RELATIONSHIPS

    // SCOPES
    /**
     * Scope a query to only include users with team member role.
     */
    public function scopeAssignableUsers(Builder $query)
    {
        // Exclude observer role
        $excluded = UserDepartment::OBSERVER;

        // Push superadmin in roles array
        $roles = collect(UserDepartment::cases())
            ->reject(fn($case) => $case === $excluded)
            ->map(fn($case) => $case->value)
            ->push('superadmin')
            ->toArray();

        return $query->whereHas('roles', function ($q) use ($roles) {
            $q->whereIn('name', $roles);
        });
    }

    /**
     * Scope a query to exclude the authenticated user.
     */
    public function scopeExcludeAuthenticatedUser(Builder $query)
    {
        return $query->where('id', '!=', auth()->id());
    }

    /**
     * Scope a query to exclude the event owner.
     */
    public function scopeExcludeEventOwner(Builder $query, ?int $eventOwnerId)
    {
        if ($eventOwnerId) {
            return $query->where('id', '!=', $eventOwnerId);
        }

        return $query;
    }

    /**
     * Scope a query to exclude superadmin.
     */
    public function scopeWithoutSuperadmin(Builder $query): Builder
    {
        return $query->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'superadmin');
        });
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

    // NOTIFICATIONS
    /**
     * The channels the user receives notification broadcasts on.
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'users.' . $this->id;
    }

    // WIRECHAT TRAITS AND METHODS
    /**
     * Returns the URL for the user's cover image for chats (avatar).
     */
    public function getCoverUrlAttribute(): ?string
    {
        return $this->profile_photo_path
            ? asset("storage/{$this->profile_photo_path}")
            : asset('images/placeholder-user.jpg');
    }

    /**
     * Accessor Returns the display name for the user.
     */
    public function getDisplayNameAttribute(): ?string
    {
        return $this->full_name ?? 'user';
    }

    /**
     * Search for users when creating a new chat or adding members to a group.
     */
    public function searchChatables(string $search): ?Collection
    {
        $search = trim($search);

        $query = User::where('id', '!=', auth()->id())
            ->where(function ($query) use ($search) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $search . '%'])
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            })
            ->limit(20);

        return $query->get();
    }
    // WIRECHAT TRAITS AND METHODS END
}
