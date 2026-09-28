<?php

namespace App\Support;

use App\Enums\ThreadStatus;
use App\Models\MessageThread;
use App\Models\Season;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/**
 * Builds the sidebar navigation, command palette entries and badge counts
 * for the active season, filtered to what the signed-in user may see.
 */
class AdminNav
{
    public function __construct(private readonly ?Season $season = null) {}

    /**
     * Whether the missing-route warning has already been written this request.
     */
    private bool $reportedMissing = false;

    /**
     * Nav groups, each holding only the items the user can reach.
     *
     * An item whose route has not been built yet is still rendered, but marked
     * disabled. Silently dropping it would hide a missing screen from whoever
     * is trying to find it.
     *
     * @return list<array{label: string, items: list<array<string, mixed>>}>
     */
    public function groups(?Authenticatable $user): array
    {
        $groups = [];
        $missing = [];

        foreach ((array) config('caf.nav', []) as $group) {
            $items = [];

            foreach ($group['items'] ?? [] as $item) {
                if (! $this->allowed($user, $item['permission'] ?? null)) {
                    continue;
                }

                $exists = Route::has($item['route']);

                // array_merge, not +, so the resolved count wins over the badge
                // key the config declares.
                $items[] = array_merge($item, [
                    'enabled' => $exists,
                    'badge' => $this->badge($user, $item['badge'] ?? null),
                ]);

                if (! $exists) {
                    $missing[$item['route']] = $item['label'];
                }
            }

            if ($items !== []) {
                $groups[] = ['label' => $group['label'], 'items' => $items];
            }
        }

        $this->reportMissing($missing);

        return $groups;
    }

    /**
     * Record unbuilt screens once per request. Every admin page builds the nav,
     * so warning per item would write the same lines on every request.
     *
     * @param  array<string, string>  $missing
     */
    private function reportMissing(array $missing): void
    {
        if ($missing === [] || $this->reportedMissing) {
            return;
        }

        $this->reportedMissing = true;

        Log::warning('Admin nav items point at routes that do not exist yet.', [
            'routes' => array_keys($missing),
            'items' => array_values($missing),
        ]);
    }

    /**
     * Flat list of reachable items, used by the command palette. Items whose
     * screen does not exist yet are left out — there is nowhere to go.
     *
     * @return list<array<string, mixed>>
     */
    public function commands(?Authenticatable $user): array
    {
        $commands = [];

        foreach ($this->groups($user) as $group) {
            foreach ($group['items'] as $item) {
                if (! $item['enabled']) {
                    continue;
                }

                $commands[] = [
                    'group' => 'Navigate',
                    'label' => $item['label'],
                    'icon' => $item['icon'],
                    'url' => route($item['route']),
                ];
            }
        }

        return $commands;
    }

    /**
     * Action palette entries, only offered to users who may run them.
     *
     * @return list<array<string, mixed>>
     */
    public function actions(?Authenticatable $user): array
    {
        $actions = [
            ['label' => 'New registration', 'route' => 'admin.registrations.create', 'icon' => 'plus', 'permission' => 'registrations.manage'],
            ['label' => 'Record a payment', 'route' => 'admin.payments.index', 'icon' => 'card', 'permission' => 'payments.manage'],
            ['label' => 'Compose campaign', 'route' => 'admin.communications.campaigns.create', 'icon' => 'send', 'permission' => 'communications.manage'],
            ['label' => 'New season', 'route' => 'admin.seasons.create', 'icon' => 'plus', 'permission' => 'seasons.manage'],
        ];

        $commands = [];

        foreach ($actions as $action) {
            if (! $this->allowed($user, $action['permission']) || ! Route::has($action['route'])) {
                continue;
            }

            $commands[] = [
                'group' => 'Actions',
                'label' => $action['label'],
                'icon' => $action['icon'],
                'url' => route($action['route']),
            ];
        }

        return $commands;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function palette(?Authenticatable $user): array
    {
        return [...$this->commands($user), ...$this->actions($user)];
    }

    public function isActive(string $routeName): bool
    {
        return $routeName === 'admin.dashboard'
            ? request()->routeIs('admin.dashboard')
            : request()->routeIs($routeName) || request()->routeIs($routeName.'.*');
    }

    private function allowed(?Authenticatable $user, ?string $permission): bool
    {
        if ($user === null) {
            return false;
        }

        if ($permission === null) {
            return true;
        }

        return method_exists($user, 'canDo') && $user->canDo($permission);
    }

    private function badge(?Authenticatable $user, ?string $key): ?int
    {
        if ($key === null || $this->season === null) {
            return null;
        }

        $count = match ($key) {
            'needs_attention' => $this->season->registrations()->needsAttention()->count(),
            'pending_scores' => $this->season->reviewRounds()
                ->with(['assignments' => fn ($q) => $q->whereIn('status', ['pending', 'in_progress'])])
                ->get()
                ->sum(fn ($round) => $round->assignments->count()),
            'unsettled_invoices' => $this->season->invoices()->outstanding()->count(),
            'open_threads' => MessageThread::query()
                ->where('season_id', $this->season->getKey())
                ->whereIn('status', [ThreadStatus::Open->value, ThreadStatus::Pending->value])
                ->count(),
            default => null,
        };

        return $count !== null && $count > 0 ? $count : null;
    }
}
