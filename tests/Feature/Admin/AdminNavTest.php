<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Season;
use App\Models\User;
use App\Support\AdminNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The sidebar is built from config, so a configured item whose route has not
 * been implemented must stay visible as a disabled entry rather than
 * disappearing. These tests pin that behaviour and the single warning it
 * writes.
 */
class AdminNavTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Point the config at a route that does not exist, alongside one that does,
     * so the disabled-entry behaviour stays testable now that every real
     * module is implemented.
     */
    private function navWithOneMissingRoute(): void
    {
        $group = config('caf.nav.0');

        config(['caf.nav' => [
            [
                'label' => $group['label'] ?? 'Main',
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'route' => 'admin.dashboard',
                        'icon' => 'grid',
                        'permission' => 'dashboard.view',
                    ],
                    [
                        'label' => 'Imaginary Module',
                        'route' => 'admin.imaginary.index',
                        'icon' => 'star',
                        'permission' => 'dashboard.view',
                    ],
                ],
            ],
        ]]);
    }

    #[Test]
    public function unbuilt_screens_are_rendered_as_disabled_instead_of_being_hidden(): void
    {
        $this->navWithOneMissingRoute();

        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        Season::factory()->live()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertSee('Imaginary Module');
        $response->assertSee('aria-disabled="true"', escape: false);
        $response->assertSee('is-disabled', escape: false);
    }

    #[Test]
    public function unbuilt_screens_are_excluded_from_the_command_palette(): void
    {
        $this->navWithOneMissingRoute();

        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();

        $labels = array_column((new AdminNav)->commands($user), 'label');

        $this->assertContains('Dashboard', $labels);
        $this->assertNotContains('Imaginary Module', $labels);
    }

    #[Test]
    public function missing_routes_are_warned_about_once_per_request(): void
    {
        $this->navWithOneMissingRoute();

        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        Season::factory()->live()->create();

        Log::spy();

        $nav = new AdminNav;
        $nav->groups($user);
        $nav->groups($user);

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn (string $message, array $context) => str_contains($message, 'do not exist yet')
                && in_array('admin.imaginary.index', $context['routes'] ?? [], true));
    }

    #[Test]
    public function every_configured_item_the_user_may_reach_is_present(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();

        $items = collect((new AdminNav)->groups($user))->flatMap(fn (array $group) => $group['items']);

        $configured = collect((array) config('caf.nav'))
            ->flatMap(fn (array $group) => $group['items'] ?? [])
            ->map(fn (array $item) => $item['route']);

        foreach ($configured as $route) {
            $this->assertTrue(
                $items->contains('route', $route),
                "Configured nav route [{$route}] was dropped from the sidebar.",
            );
        }
    }

    #[Test]
    public function every_configured_screen_is_now_implemented(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();

        $items = collect((new AdminNav)->groups($user))->flatMap(fn (array $group) => $group['items']);

        $disabled = $items
            ->reject(fn (array $item): bool => $item['enabled'])
            ->pluck('label')
            ->all();

        $this->assertSame([], $disabled, 'These configured screens still have no route.');
    }

    #[Test]
    public function no_warning_is_logged_when_every_screen_is_built(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        Season::factory()->live()->create();

        Log::spy();

        (new AdminNav)->groups($user);

        Log::shouldNotHaveReceived('warning');
    }

    #[Test]
    public function users_without_permission_do_not_see_restricted_items(): void
    {
        $user = User::factory()->withRole(UserRole::FinanceOfficer)->create();

        $items = collect((new AdminNav)->groups($user))->flatMap(fn (array $group) => $group['items']);

        $this->assertTrue($items->contains('route', 'admin.payments.index'));
        $this->assertFalse($items->contains('route', 'admin.settings.index'));
        $this->assertFalse($items->contains('route', 'admin.judging.index'));
    }
}
