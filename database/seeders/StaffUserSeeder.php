<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds one staff account per operational role.
 *
 * The accounts are the ones used to click through the system while it is
 * being built, so every one of them shares a single known password. That is
 * only ever appropriate on a local database: `migrate:fresh --seed` will
 * happily create these on a staging environment if you let it.
 *
 * Running this twice is safe. Accounts are matched on email and updated in
 * place, so re-seeding refreshes the role and password without duplicating
 * anyone or losing their two-factor secret.
 */
class StaffUserSeeder extends Seeder
{
    /**
     * @var list<array{0: string, 1: UserRole}>
     */
    private const ACCOUNTS = [
        ['admin@cultureacapellafestival.com', UserRole::SuperAdmin],
        ['administrator@cultureacapellafestival.com', UserRole::Administrator],
        ['coordinator@cultureacapellafestival.com', UserRole::RegistrationOfficer],
        ['finance@cultureacapellafestival.com', UserRole::FinanceOfficer],
        ['floormanager@cultureacapellafestival.com', UserRole::FloorManager],
    ];

    /**
     * @var array<string, string>
     */
    private const NAMES = [
        'admin@cultureacapellafestival.com' => 'Joseph Mwakalinga',
        'administrator@cultureacapellafestival.com' => 'Grace Ndinda',
        'coordinator@cultureacapellafestival.com' => 'Peter Kimaro',
        'finance@cultureacapellafestival.com' => 'Fatuma Salim',
        'floormanager@cultureacapellafestival.com' => 'Emmanuel Massawe',
    ];

    public function run(): void
    {
        $password = Hash::make('password');

        foreach (self::ACCOUNTS as [$email, $role]) {
            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => self::NAMES[$email],
                    'password' => $password,
                    'role' => $role->value,
                ],
            );
        }
    }
}
