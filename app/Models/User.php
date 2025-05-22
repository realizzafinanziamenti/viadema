<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Namu\WireChat\Traits\Chatable;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes, Chatable;

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
     * Description roles
     */
    protected array $roleDescriptions = [
        'superadmin' => 'SuperAdmin',
        'team_member' => 'Collaboratore',
        'observer' => 'Osservatore',
    ];

    /**
     * Check if the user role is superamin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Check if the user role is team_member
     */
    public function isTeamMember(): bool
    {
        return $this->hasRole('team_member');
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
        return $this->roleDescriptions[$role] ?? 'Ruolo non definito';
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

    // END RELATIONSHIPS

    // SCOPES
    /**
     * Scope a query to only include users with team member role.
     */
    public function scopeTeamMembers(Builder $query)
    {
        return $query->role('team_member');
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
