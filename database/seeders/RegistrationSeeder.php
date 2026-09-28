<?php

namespace Database\Seeders;

use App\Enums\RegistrationRole;
use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Models\RegistrationMember;
use App\Models\RegistrationStatusEvent;
use App\Models\Season;
use Illuminate\Database\Seeder;

/**
 * Fifteen applications across the live season, deliberately spread over every
 * status and payment state so the dashboards, filters and badges all have
 * something real to count.
 */
class RegistrationSeeder extends Seeder
{
    /**
     * group, category, members, contact, status, payment, country, city
     *
     * @var list<array{string, string, int, string, string, string, string, string}>
     */
    private const GROUPS = [
        ['Ibera Voices', 'choir', 6, 'Amina Juma', 'confirmed', 'paid', 'Tanzania', 'Arusha'],
        ['Serengeti Singers', 'choir', 12, 'Jonas Mushi', 'confirmed', 'partially_paid', 'Tanzania', 'Moshi'],
        ['Zanzibar Harmony', 'choir', 8, 'Fatma Salim', 'approved', 'paid', 'Tanzania', 'Zanzibar'],
        ['Bagamoyo Collective', 'choir', 4, 'Neema Kambi', 'shortlisted', 'pending', 'Tanzania', 'Bagamoyo'],
        ['Mbeya Sound', 'choir', 10, 'Peter Ndosi', 'shortlisted', 'partially_paid', 'Tanzania', 'Mbeya'],
        ['Rwanda Melody', 'choir', 5, 'Aline Uwimana', 'under_review', 'pending', 'Rwanda', 'Kigali'],
        ['Ukunda Rhythms', 'band', 9, 'BarakaallyMzee', 'under_review', 'unpaid', 'Tanzania', 'Dar es Salaam'],
        ['Tanga Percussion', 'band', 14, 'Salma Omary', 'under_review', 'unpaid', 'Tanzania', 'Tanga'],
        ['Dodoma Voices', 'choir', 7, 'Grace Mwakalinga', 'submitted', 'unpaid', 'Tanzania', 'Dodoma'],
        ['Coastal A Cappella', 'choir', 3, 'Hamisi Said', 'submitted', 'unpaid', 'Kenya', 'Mombasa'],
        ['Kilimanjaro Echo', 'choir', 11, 'Emmanuel Massaewe', 'submitted', 'pending', 'Tanzania', 'Moshi'],
        ['Nyerere Youth Choir', 'choir', 18, 'Daniel Kihongo', 'waitlisted', 'unpaid', 'Tanzania', 'Moshi'],
        ['Lakeside Serenade', 'band', 6, 'Lucy Mhame', 'not_selected', 'unpaid', 'Tanzania', 'Mwanza'],
        ['Pemba Island Voices', 'choir', 9, 'Bakari Said', 'rejected', 'unpaid', 'Tanzania', 'Pemba'],
        ['Safari Sound', 'band', 7, 'Olivia Kimaro', 'disqualified', 'refunded', 'Tanzania', 'Arusha'],
    ];

    public function run(): void
    {
        $season = Season::query()->where('number', 2)->firstOrFail();

        foreach (self::GROUPS as $index => $group) {
            [$name, $category, $members, $contact, $status, $payment, $country, $city] = $group;

            $registration = Registration::query()->updateOrCreate(
                ['code' => sprintf('CAF2-%04d', $index + 1)],
                [
                    'season_id' => $season->getKey(),
                    'group_name' => $name,
                    'category' => $category,
                    'role_type' => RegistrationRole::Singers->value,
                    'country' => $country,
                    'city' => $city,
                    'members_count' => $members,
                    'contact_name' => $contact,
                    'contact_email' => $this->emailFor($contact, $index),
                    'contact_phone' => '+25575'.str_pad((string) (400000 + $index), 6, '0', STR_PAD_LEFT),
                    'performance_link' => 'https://youtube.com/watch?v=demo'.$index,
                    'status' => $status,
                    'payment_status' => $payment,
                    'source' => $index % 4 === 0 ? 'web' : ($index % 3 === 0 ? 'partner' : 'outreach'),
                    'tags' => $this->tagsFor($index),
                    'bio' => "{$name} is a {$category} from {$city} performing at the festival for the first time.",
                    'is_public' => in_array($status, [RegistrationStatus::Confirmed->value], true),
                    'started_at' => now()->subDays(40 - $index),
                    'submitted_at' => now()->subDays(35 - $index),
                    'reviewed_at' => $status === RegistrationStatus::Submitted->value
                        ? null
                        : now()->subDays(20 - $index),
                    'approved_at' => in_array($status, [
                        RegistrationStatus::Approved->value,
                        RegistrationStatus::Confirmed->value,
                        RegistrationStatus::Waitlisted->value,
                    ], true) ? now()->subDays(18 - $index) : null,
                    'confirmed_at' => $status === RegistrationStatus::Confirmed->value
                        ? now()->subDays(12 - $index)
                        : null,
                ],
            );

            $this->members($registration, $members, $contact);
            $this->statusEvent($registration, $status, $contact);
        }
    }

    private function emailFor(string $contact, int $index): string
    {
        $slug = str($contact)->lower()->replaceMatches('/[^a-z]+/', '.')->trim('.')->toString();

        return $slug.'@example.com';
    }

    /**
     * @return list<string>
     */
    private function tagsFor(int $index): array
    {
        $pool = ['choir', 'band', 'youth', 'returning', 'bursary', 'rural', 'newcomer', 'featured'];
        $tags = [$pool[$index % count($pool)]];

        if ($index % 3 === 0) {
            $tags[] = 'priority';
        }

        return array_values(array_unique($tags));
    }

    private function members(Registration $registration, int $count, string $lead): void
    {
        $parts = ['Soprano', 'Alto', 'Tenor', 'Bass', 'Percussion', 'Rhythm', 'Harmony'];

        foreach (range(0, $count - 1) as $position) {
            RegistrationMember::query()->updateOrCreate(
                ['registration_id' => $registration->getKey(), 'sort_order' => $position],
                [
                    'name' => $position === 0
                        ? $lead
                        : fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'phone' => '+25575'.fake()->numerify('######'),
                    'part' => $parts[$position % count($parts)],
                    'is_lead' => $position === 0,
                ],
            );
        }
    }

    private function statusEvent(Registration $registration, string $status, string $contact): void
    {
        $path = [
            RegistrationStatus::Submitted->value,
            RegistrationStatus::UnderReview->value,
            RegistrationStatus::Shortlisted->value,
            RegistrationStatus::Approved->value,
            RegistrationStatus::Confirmed->value,
        ];

        $reached = match ($status) {
            RegistrationStatus::Confirmed->value => $path,
            RegistrationStatus::Approved->value => array_slice($path, 0, 4),
            RegistrationStatus::Shortlisted->value => array_slice($path, 0, 3),
            RegistrationStatus::UnderReview->value => array_slice($path, 0, 2),
            RegistrationStatus::Submitted->value => [$path[0]],
            default => [$path[0], $status],
        };

        $previous = null;

        foreach ($reached as $step) {
            // updateOrCreate, not create: a re-run of db:seed must not append a
            // second copy of the whole history to every registration.
            // updateOrCreate, not create: a re-run of db:seed must not append a
            // second copy of the whole history to every registration.
            RegistrationStatusEvent::query()->updateOrCreate(
                [
                    'registration_id' => $registration->getKey(),
                    'from_status' => $previous,
                    'to_status' => $step,
                ],
                [
                    'actor_label' => $previous === null ? $contact : 'Registration office',
                ],
            );

            $previous = $step;
        }
    }
}
