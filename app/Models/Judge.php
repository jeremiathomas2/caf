<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'season_id', 'user_id', 'name', 'email', 'role_title', 'bio',
    'photo_url', 'is_public', 'is_active',
])]
class Judge extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Season, $this>
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ReviewAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class);
    }

    public function pendingCount(): int
    {
        return $this->assignments()->whereIn('status', ['pending', 'in_progress'])->count();
    }

    public function initials(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
    }

    public function isManager(): bool
    {
        return $this->user?->role === UserRole::SuperAdmin
            || $this->user?->role === UserRole::SeasonManager;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
