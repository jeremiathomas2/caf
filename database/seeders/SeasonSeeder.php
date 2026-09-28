<?php

namespace Database\Seeders;

use App\Enums\SeasonState;
use App\Models\Season;
use Illuminate\Database\Seeder;

/**
 * The three seasons the system is designed around: a current live one, an
 * upcoming draft, and an archived season so the archive and reporting screens
 * have something to show.
 */
class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        Season::query()->updateOrCreate(
            ['number' => 1],
            [
                'name' => 'Voices Rising',
                'slug' => 'voices-rising',
                'theme' => 'Voices Rising',
                'tagline' => 'Where every voice finds its harmony',
                'state' => SeasonState::Archived->value,
                'is_current' => false,
                'venue' => 'Uhuru Stadium',
                'city' => 'Dar es Salaam',
                'country' => 'Tanzania',
                'starts_on' => '2024-07-19',
                'ends_on' => '2024-07-21',
                'registration_opens_at' => '2024-02-01 00:00:00',
                'registration_closes_at' => '2024-06-30 23:59:59',
                'early_bird_closes_at' => '2024-04-15 23:59:59',
                'currency' => 'TZS',
                'secondary_currency' => 'USD',
                'per_head_fee' => 120000,
                'early_bird_fee' => 100000,
                'min_partial_payment_pct' => 50,
                'rounding_increment' => 100,
                'usd_fx_rate' => 2512.60,
                'summary' => 'The inaugural Culture Acapella Festival.',
            ],
        );

        Season::query()->updateOrCreate(
            ['number' => 2],
            [
                'name' => 'Singing to Save Lives',
                'slug' => 'singing-to-save-lives',
                'theme' => 'Singing to Save Lives',
                'tagline' => 'Every voice raises funds for a cause',
                'state' => SeasonState::Live->value,
                'is_current' => true,
                'venue' => 'Kigamboni Cultural Centre',
                'city' => 'Arusha',
                'country' => 'Tanzania',
                'starts_on' => '2027-07-17',
                'ends_on' => '2027-07-18',
                'registration_opens_at' => '2026-09-01 00:00:00',
                'registration_closes_at' => '2027-05-31 23:59:59',
                'early_bird_closes_at' => '2027-03-31 23:59:59',
                'currency' => 'TZS',
                'secondary_currency' => 'USD',
                'per_head_fee' => 50000,
                'early_bird_fee' => 42000,
                'min_partial_payment_pct' => 50,
                'rounding_increment' => 100,
                'usd_fx_rate' => 2585.40,
                'summary' => 'Groups compete while raising funds for community health partners.',
            ],
        );

        Season::query()->updateOrCreate(
            ['number' => 3],
            [
                'name' => 'Harvest of Harmony',
                'slug' => 'harvest-of-harmony',
                'theme' => 'Harvest of Harmony',
                'tagline' => 'Celebrating the harvest, together',
                'state' => SeasonState::Draft->value,
                'is_current' => false,
                'venue' => 'To be announced',
                'city' => 'Moshi',
                'country' => 'Tanzania',
                'starts_on' => '2028-07-14',
                'ends_on' => '2028-07-15',
                'registration_opens_at' => null,
                'registration_closes_at' => null,
                'early_bird_closes_at' => null,
                'currency' => 'TZS',
                'secondary_currency' => 'USD',
                'per_head_fee' => 55000,
                'early_bird_fee' => null,
                'min_partial_payment_pct' => 50,
                'rounding_increment' => 100,
                'usd_fx_rate' => null,
                'summary' => 'Still being planned. Fees and venues are placeholders.',
            ],
        );
    }
}
