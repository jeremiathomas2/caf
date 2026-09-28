<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Staff accounts come first because the review data references panel
     * members, and seasons come first because almost everything else is
     * season-scoped.
     */
    public function run(): void
    {
        $this->call([
            StaffUserSeeder::class,
            SeasonSeeder::class,
            RegistrationSeeder::class,
            FinanceSeeder::class,
            ReviewSeeder::class,
            ContentSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
