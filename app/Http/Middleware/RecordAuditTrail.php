<?php

namespace App\Http\Middleware;

use App\Services\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordAuditTrail
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * Wrap mutating admin requests so the audit log stays complete even when
     * a controller forgets to log explicitly.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') && ! $response->isRedirection() && $response->isSuccessful()) {
            $this->audit->record(
                action: $request->method().' '.$request->route()?->getName(),
                category: $this->category($request),
                entityType: $request->route()?->parameter('registration') ? 'registration' : null,
                entityId: (string) ($request->route()?->parameter('registration')?->getKey() ?? ''),
                detail: $request->fullUrl(),
            );
        }

        return $response;
    }

    private function category(Request $request): string
    {
        $segment = $request->segment(2);

        return match ($segment) {
            'registrations' => 'registration',
            'judging' => 'judging',
            'payments' => 'payment',
            'communications' => 'communication',
            'content', 'programme' => 'content',
            'settings', 'seasons' => 'system',
            default => 'general',
        };
    }
}
