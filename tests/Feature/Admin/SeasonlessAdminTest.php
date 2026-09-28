<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * A brand new install has no seasons, and almost every screen needs one.
 * These cover what a staff member sees before the first one is created.
 */
class SeasonlessAdminTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_dashboard_sends_the_user_to_the_seasons_screen(): void
    {
        $this->actingAs(User::factory()->withRole(UserRole::SuperAdmin)->create())
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.seasons.index'))
            ->assertSessionHas('status');
    }

    #[Test]
    public function other_season_bound_screens_redirect_instead_of_erroring(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();

        foreach (['admin.registrations.index', 'admin.communications.index', 'admin.audit.index'] as $screen) {
            $this->actingAs($user)
                ->get(route($screen))
                ->assertRedirect(route('admin.seasons.index'));
        }
    }

    #[Test]
    public function the_seasons_screens_still_work(): void
    {
        $this->actingAs(User::factory()->withRole(UserRole::SuperAdmin)->create())
            ->get(route('admin.seasons.index'))
            ->assertOk();
    }

    #[Test]
    public function a_role_without_season_access_is_still_refused_by_the_seasons_screen(): void
    {
        // Every staff role can see seasons, so the redirect target is what
        // guards this. Group leaders never reach the middleware at all.
        $this->actingAs(User::factory()->withRole(UserRole::GroupLeader)->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    #[Test]
    public function once_a_season_exists_the_dashboard_renders_again(): void
    {
        Season::factory()->live()->create();

        $this->actingAs(User::factory()->withRole(UserRole::SuperAdmin)->create())
            ->get(route('admin.dashboard'))
            ->assertOk();
    }
}
