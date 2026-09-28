<?php

namespace Tests\Feature\Admin;

use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\Registration;
use App\Models\Season;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Renders every management screen as a signed-in super admin.
 *
 * Screens are cheap to render and expensive to break: a mistyped component
 * name or a route that no longer exists only shows up at runtime, so each one
 * is walked once here.
 */
class AdminScreenSmokeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[DataProvider('screens')]
    public function every_management_screen_renders(string $screen): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $season = Season::factory()->live()->create();

        $this->actingAs($user)
            ->get($this->url($screen, $season))
            ->assertOk();
    }

    #[Test]
    public function an_admin_request_looks_up_the_seasons_only_once(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        Season::factory()->live()->create();
        Season::factory()->archived()->create();

        $queries = [];

        DB::listen(function (QueryExecuted $query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk();

        // The shell needs the active season and the switcher list, so two
        // lookups is the floor. More means SeasonContext is resolving per call.
        $this->assertLessThanOrEqual(
            2,
            collect($queries)->filter(fn (string $sql): bool => str_contains($sql, '"seasons"'))->count(),
            'The admin shell is querying the seasons table more than twice per request.',
        );
    }

    #[Test]
    public function the_sidebar_shows_the_public_caf_logo(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        Season::factory()->live()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('class="brand-logo"', false)
            ->assertSee(asset('caf.png'), false);

        // The sidebar references the file the public site uses, so a missing or
        // renamed asset would otherwise only surface as a broken image.
        $this->assertFileExists(public_path('caf.png'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function screens(): array
    {
        return [
            'dashboard' => ['dashboard'],
            'seasons index' => ['seasons.index'],
            'season create' => ['seasons.create'],
            'season show' => ['seasons.show'],
            'season edit' => ['seasons.edit'],
            'registrations index' => ['registrations.index'],
            'registration create' => ['registrations.create'],
            'judging index' => ['judging.index'],
            'payments index' => ['payments.index'],
            'analytics index' => ['analytics.index'],
            'content index' => ['content.index'],
            'portal index' => ['portal.index'],
            'teams index' => ['teams.index'],
            'settings index' => ['settings.index'],
        ];
    }

    #[Test]
    public function the_registration_show_screen_renders(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $registration = Registration::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.registrations.show', $registration))
            ->assertOk()
            ->assertSee($registration->group_name)
            ->assertSee($registration->code);
    }

    #[Test]
    public function the_registration_edit_screen_renders_while_editable(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $registration = Registration::factory()
            ->withStatus(RegistrationStatus::Submitted)
            ->create();

        $this->actingAs($user)
            ->get(route('admin.registrations.edit', $registration))
            ->assertOk()
            ->assertSee('members_count', false);
    }

    #[Test]
    public function the_dashboard_scopes_its_numbers_to_the_season_in_the_query_string(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $current = Season::factory()->live()->create(['name' => 'Singing to Save Lives']);
        $older = Season::factory()->archived()->create(['name' => 'Voices Rising']);

        Registration::factory()->forSeason($current)->count(3)->confirmed()->create();
        Registration::factory()->forSeason($older)->count(7)->confirmed()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('3 confirmed', false)
            ->assertDontSee('7 confirmed', false);

        $this->actingAs($user)
            ->get(route('admin.dashboard', ['season' => $older->getKey()]))
            ->assertOk()
            ->assertSee('7 confirmed', false)
            ->assertDontSee('3 confirmed', false);
    }

    #[Test]
    public function the_season_choice_sticks_for_the_next_request(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $current = Season::factory()->live()->create();
        $older = Season::factory()->archived()->create();

        Registration::factory()->forSeason($current)->count(3)->confirmed()->create();
        Registration::factory()->forSeason($older)->count(7)->confirmed()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard', ['season' => $older->getKey()]))
            ->assertOk();

        // No query string on the follow-up request: the choice was remembered.
        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('7 confirmed', false);
    }

    #[Test]
    public function an_unknown_season_in_the_query_string_falls_back_to_the_current_one(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        Season::factory()->live()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard', ['season' => 999999]))
            ->assertOk();
    }

    private function url(string $screen, Season $season): string
    {
        return match ($screen) {
            'dashboard' => route('admin.dashboard'),
            'seasons.index' => route('admin.seasons.index'),
            'seasons.create' => route('admin.seasons.create'),
            'seasons.show' => route('admin.seasons.show', $season),
            'seasons.edit' => route('admin.seasons.edit', $season),
            'registrations.index' => route('admin.registrations.index'),
            'registrations.create' => route('admin.registrations.create'),
            'judging.index' => route('admin.judging.index'),
            'payments.index' => route('admin.payments.index'),
            'analytics.index' => route('admin.analytics.index'),
            'content.index' => route('admin.content.index'),
            'portal.index' => route('admin.portal.index'),
            'teams.index' => route('admin.teams.index'),
            'settings.index' => route('admin.settings.index'),
        };
    }
}
