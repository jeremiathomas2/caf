<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'actor_id', 'actor_label', 'action', 'category', 'entity_type', 'entity_id',
    'entity_label', 'detail', 'ip_address', 'user_agent',
])]
class AuditLog extends Model
{
    use HasFactory;

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
     * Badge tone, matching the names admin.css defines.
     */
    public function tone(): string
    {
        return match (true) {
            str_starts_with($this->action, 'delete'), str_starts_with($this->action, 'disqualify') => 'red',
            str_starts_with($this->action, 'create'), str_starts_with($this->action, 'import') => 'green',
            str_starts_with($this->action, 'update'), str_starts_with($this->action, 'status') => 'amber',
            default => 'blue',
        };
    }
}
