<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\StaffUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StaffUserSeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_seeds_one_account_per_operational_role(): void
    {
        $this->seed(StaffUserSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@cultureacapellafestival.com', 'role' => UserRole::SuperAdmin->value]);
        $this->assertDatabaseHas('users', ['email' => 'administrator@cultureacapellafestival.com', 'role' => UserRole::Administrator->value]);
        $this->assertDatabaseHas('users', ['email' => 'coordinator@cultureacapellafestival.com', 'role' => UserRole::RegistrationOfficer->value]);
        $this->assertDatabaseHas('users', ['email' => 'finance@cultureacapellafestival.com', 'role' => UserRole::FinanceOfficer->value]);
        $this->assertDatabaseHas('users', ['email' => 'floormanager@cultureacapellafestival.com', 'role' => UserRole::FloorManager->value]);

        $this->assertSame(5, User::query()->count());
    }

    #[Test]
    public function every_seeded_account_can_sign_in_with_the_demo_password(): void
    {
        $this->seed(StaffUserSeeder::class);

        User::query()->get()->each(function (User $user): void {
            $this->assertTrue(Hash::check('password', $user->password), "{$user->email} cannot use the demo password.");
        });
    }

    #[Test]
    public function every_seeded_account_may_reach_the_management_system(): void
    {
        $this->seed(StaffUserSeeder::class);

        User::query()->get()->each(function (User $user): void {
            $this->assertTrue($user->isAdmin(), "{$user->email} is not recognised as staff.");
        });
    }

    #[Test]
    public function re_seeding_updates_the_existing_accounts_instead_of_duplicating_them(): void
    {
        $this->seed(StaffUserSeeder::class);
        $this->seed(StaffUserSeeder::class);

        $this->assertSame(5, User::query()->count());
    }

    #[Test]
    public function the_administrator_role_cannot_change_settings(): void
    {
        $this->seed(StaffUserSeeder::class);

        $administrator = User::query()->where('email', 'administrator@cultureacapellafestival.com')->firstOrFail();

        $this->assertTrue($administrator->canDo('payments.manage'));
        $this->assertTrue($administrator->canDo('registrations.manage'));
        $this->assertFalse($administrator->canDo('settings.manage'));
    }

    #[Test]
    public function the_floor_manager_works_the_floor_without_touching_money_or_scoring(): void
    {
        $this->seed(StaffUserSeeder::class);

        $floor = User::query()->where('email', 'floormanager@cultureacapellafestival.com')->firstOrFail();

        $this->assertTrue($floor->canDo('registrations.manage'));
        $this->assertTrue($floor->canDo('communications.manage'));
        $this->assertFalse($floor->canDo('payments.view'));
        $this->assertFalse($floor->canDo('judging.score'));
        $this->assertFalse($floor->canDo('settings.view'));
    }

    #[Test]
    public function the_finance_officer_can_manage_payments_but_not_registrations(): void
    {
        $this->seed(StaffUserSeeder::class);

        $finance = User::query()->where('email', 'finance@cultureacapellafestival.com')->firstOrFail();

        $this->assertTrue($finance->canDo('payments.manage'));
        $this->assertTrue($finance->canDo('analytics.view'));
        $this->assertFalse($finance->canDo('registrations.manage'));
        $this->assertFalse($finance->canDo('communications.manage'));
    }

    #[Test]
    public function the_coordinator_processes_registrations_without_managing_payments(): void
    {
        $this->seed(StaffUserSeeder::class);

        $coordinator = User::query()->where('email', 'coordinator@cultureacapellafestival.com')->firstOrFail();

        $this->assertTrue($coordinator->canDo('registrations.manage'));
        $this->assertTrue($coordinator->canDo('communications.manage'));
        $this->assertFalse($coordinator->canDo('payments.manage'));
    }
}
