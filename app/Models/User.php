<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'avatar_path'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isAdmin(): bool
    {
        return in_array($this->role, UserRole::adminRoles(), true);
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role->value;
    }

    /**
     * The stored role as an enum, falling back to the least privileged role
     * when the column holds a value the enum does not know about.
     */
    public function roleEnum(): UserRole
    {
        return UserRole::tryFrom($this->role) ?? UserRole::Auditor;
    }

    public function roleLabel(): string
    {
        return $this->roleEnum()->label();
    }

    /**
     * @return list<string>
     */
    public function permissions(): array
    {
        return UserRole::from($this->role)->permissions();
    }

    public function canDo(string $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }

    public function initials(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
    }

    public function requiresTwoFactorChallenge(): bool
    {
        return filled($this->two_factor_secret);
    }

    public function hasConfirmedTwoFactor(): bool
    {
        return filled($this->two_factor_confirmed_at);
    }

    /**
     * @return HasMany<Registration, $this>
     */
    public function reviewedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'reviewer_id');
    }

    /**
     * @return HasMany<MessageThread, $this>
     */
    public function assignedThreads(): HasMany
    {
        return $this->hasMany(MessageThread::class, 'assigned_to_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
