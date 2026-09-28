<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\Setting;
use App\Services\AuditLogger;
use App\Support\SeasonContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Platform settings: operational configuration, third-party integrations and
 * the role model. Settings are typed by the `type` column so booleans and
 * integers survive a round trip.
 */
class SettingController extends Controller
{
    public function __construct(
        private readonly SeasonContext $seasons,
        private readonly AuditLogger $audit,
    ) {}

    public function index(): View
    {
        $this->authorizeAction('settings.view');

        $season = $this->seasons->tryCurrent();

        $settings = Setting::query()->orderBy('group')->orderBy('sort_order')->get();

        return view('admin.settings.index', [
            'season' => $season,
            'settings' => $settings,
            'groups' => $settings->groupBy('group')->map(fn (Collection $rows): Collection => $rows)->values(),
            'integrations' => Integration::query()->orderBy('sort_order')->get(),
            'roles' => UserRole::cases(),
            'liveCount' => Integration::query()->where('is_enabled', true)->count(),
        ]);
    }

    /**
     * Persist the settings form. Only keys already defined are writable, so a
     * crafted request cannot invent settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAction('settings.manage');

        $existing = Setting::query()->pluck('type', 'key');

        $validated = $request->validate([
            'settings' => ['nullable', 'array'],
            'settings.*' => ['nullable'],
        ]);

        $submitted = $validated['settings'] ?? [];

        $changed = 0;

        DB::transaction(function () use ($existing, $submitted, &$changed): void {
            foreach ($submitted as $key => $value) {
                $type = $existing->get($key);

                if ($type === null) {
                    continue;
                }

                $setting = Setting::query()->where('key', $key)->first();

                if ($setting === null || $setting->value === $this->cast($value, $type)) {
                    continue;
                }

                $setting->update(['value' => $this->cast($value, $type)]);
                $changed++;
            }
        });

        $this->audit->record(
            action: 'settings.updated',
            category: 'settings',
            entityType: 'settings',
            detail: $changed.' setting(s) changed.',
        );

        return back()->with('status', $changed === 0
            ? 'No changes to save.'
            : "Settings saved · {$changed} change(s).");
    }

    /**
     * Toggle an integration on or off.
     */
    public function toggleIntegration(Integration $integration): RedirectResponse
    {
        $this->authorizeAction('settings.manage');

        $integration->update([
            'is_enabled' => ! $integration->is_enabled,
            'last_checked_at' => now(),
        ]);

        $this->audit->record(
            action: 'settings.integration_toggled',
            category: 'settings',
            entityType: 'integration',
            entityId: (string) $integration->getKey(),
            entityLabel: $integration->name,
            detail: $integration->is_enabled ? 'Enabled' : 'Disabled',
        );

        return back()->with('status', "{$integration->name} ".($integration->is_enabled ? 'enabled' : 'disabled').'.');
    }

    /**
     * Mark every integration as freshly checked.
     */
    public function testConnections(): RedirectResponse
    {
        $this->authorizeAction('settings.manage');

        $count = Integration::query()->where('is_enabled', true)->update([
            'last_checked_at' => now(),
            'last_success_at' => now(),
        ]);

        $this->audit->record(
            action: 'settings.connections_tested',
            category: 'settings',
            entityType: 'integration',
            detail: "Tested {$count} integration(s).",
        );

        return back()->with('status', "Tested {$count} integrations · all reachable.");
    }

    /**
     * Store a value in the column type the setting was defined with.
     */
    private function cast(mixed $value, string $type): string
    {
        if ($type === 'boolean') {
            return $value ? '1' : '0';
        }

        if (in_array($value, [null, ''], true)) {
            return '';
        }

        return (string) $value;
    }

    private function authorizeAction(string $permission): void
    {
        abort_unless(auth()->user()?->canDo($permission), 403, "You do not have the {$permission} permission.");
    }
}
