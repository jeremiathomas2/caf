<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\RegistrationStatusEvent;
use Database\Seeders\RegistrationSeeder;
use Database\Seeders\SeasonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * RegistrationSeeder resolves season 2, so the season must exist first.
     * Mirrors the order DatabaseSeeder uses.
     */
    private function seedRegistrations(): void
    {
        $this->seed(SeasonSeeder::class);
        $this->seed(RegistrationSeeder::class);
    }

    #[Test]
    public function re_seeding_does_not_append_a_second_copy_of_the_status_history(): void
    {
        $this->seedRegistrations();
        $afterFirstRun = RegistrationStatusEvent::query()->count();

        $this->seed(RegistrationSeeder::class);

        // Status events were previously created with create() inside a loop, so
        // every db:seed appended a fresh copy of the full history to every
        // registration. The public status page then listed the same steps over
        // and over.
        $this->assertGreaterThan(0, $afterFirstRun);
        $this->assertSame($afterFirstRun, RegistrationStatusEvent::query()->count());
    }

    #[Test]
    public function every_seeded_registration_keeps_a_single_path_through_the_statuses(): void
    {
        $this->seedRegistrations();
        $this->seed(RegistrationSeeder::class);

        Registration::query()
            ->with('statusEvents')
            ->get()
            ->each(function (Registration $registration): void {
                $duplicates = $registration->statusEvents
                    ->groupBy(fn (RegistrationStatusEvent $event): string => ($event->from_status ?? 'start').'>'.$event->to_status->value)
                    ->filter(fn ($group): bool => $group->count() > 1);

                $this->assertCount(
                    0,
                    $duplicates,
                    "{$registration->code} has a repeated status transition.",
                );
            });
    }
}
