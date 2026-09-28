<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Registration;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function __construct(private readonly Request $request) {}

    /**
     * @param  array<string, mixed>|null  $detail
     */
    public function record(
        string $action,
        string $category = 'general',
        ?string $entityType = null,
        ?string $entityId = null,
        ?string $entityLabel = null,
        ?string $detail = null,
        ?Authenticatable $actor = null,
    ): AuditLog {
        $actor ??= Auth::user();

        return AuditLog::create([
            'actor_id' => $actor?->getAuthIdentifier(),
            'actor_label' => $actor === null ? 'System' : null,
            'action' => $action,
            'category' => $category,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'entity_label' => $entityLabel,
            'detail' => $detail,
            'ip_address' => config('caf.audit.record_ip') ? $this->request->ip() : null,
            'user_agent' => config('caf.audit.record_user_agent') ? $this->request->userAgent() : null,
        ]);
    }

    /**
     * Record that a registration moved between statuses.
     */
    public function registrationStatusChanged(
        Registration $registration,
        ?string $from,
        string $to,
        ?string $note = null,
    ): AuditLog {
        return $this->record(
            action: 'registration.status',
            category: 'registration',
            entityType: 'registration',
            entityId: (string) $registration->getKey(),
            entityLabel: $registration->group_name,
            detail: trim(sprintf(
                '%s → %s%s',
                $from ?? 'new',
                $to,
                $note !== null ? " ({$note})" : '',
            )),
        );
    }
}
