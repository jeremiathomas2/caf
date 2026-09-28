<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'registration_id', 'from_status', 'to_status', 'actor_id', 'actor_label', 'note',
])]
class RegistrationStatusEvent extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Registration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function actorName(): string
    {
        return $this->actor_label
            ?? $this->actor?->name
            ?? 'System';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'to_status' => RegistrationStatus::class,
        ];
    }
}
