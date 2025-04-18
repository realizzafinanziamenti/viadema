<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
     * Check if the user role is agency
     */
    public function isTeamMember(): bool
    {
        return $this->hasRole('team_member');
    }

    /**
     * Check if the user role is agent
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
        return self::$roleDescriptions[$role] ?? 'Ruolo non definito';
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
}
