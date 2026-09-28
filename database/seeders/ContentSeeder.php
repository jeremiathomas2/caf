<?php

namespace Database\Seeders;

use App\Enums\ContentType;
use App\Enums\PublishStatus;
use App\Enums\RegistrationStatus;
use App\Models\ContentPage;
use App\Models\FaqItem;
use App\Models\GallerySlide;
use App\Models\ImpactStat;
use App\Models\NavItem;
use App\Models\PaymentMethodOption;
use App\Models\ProgrammeSlot;
use App\Models\Registration;
use App\Models\Season;
use App\Models\SiteActivity;
use App\Models\SiteObjective;
use App\Models\SiteValue;
use App\Models\Sponsor;
use App\Models\TeamMember;
use App\Models\TermsClause;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Public-facing content: pages, navigation, programme, partners, and the
 * smaller "about the festival" blocks the public site renders.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $season = Season::query()->where('number', 2)->firstOrFail();

        $this->pages();
        $this->navigation();
        $this->about();
        $this->programme($season);
        $this->partners($season);
        $this->team();
    }

    private function pages(): void
    {
        $pages = [
            ['about', 'About the Festival', true, 'What the Culture Acapella Festival is, who runs it, and why a cappella matters in East Africa.'],
            ['how-to-participate', 'How to Participate', true, 'Everything a group needs to know before registering for the festival.'],
            ['festival-programme', 'Festival Programme', true, 'Stages, times and running order for the festival weekend.'],
            ['juries', 'Our Juries', true, 'Meet the panel that scores each round of the competition.'],
            ['news', 'News', false, 'Announcements, results and stories from previous seasons.'],
            ['gallery', 'Gallery', false, 'Photographs and video from past festivals.'],
            ['terms-and-conditions', 'Terms & Conditions', true, 'The rules that govern registration, fees and participation.'],
            ['volunteer', 'Volunteer', true, 'Work with us during festival week.'],
        ];

        foreach ($pages as $order => [$slug, $title, $inFooter, $excerpt]) {
            ContentPage::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'type' => $slug === 'news' ? ContentType::News->value : ContentType::Page->value,
                    'status' => PublishStatus::Published->value,
                    'body' => $excerpt."\n\nThis page is managed from Admin > Content & Programme.",
                    'excerpt' => $excerpt,
                    'published_at' => now()->subDays(30 - $order),
                    'is_in_footer' => $inFooter,
                ],
            );
        }
    }

    private function navigation(): void
    {
        $items = [
            ['Home', 'home', '/'],
            ['About', 'about', '/about'],
            ['How to Participate', 'how-to-participate', '/how-to-participate'],
            ['Programme', 'festival-programme', '/festival-programme'],
            ['Juries', 'juries', '/juries'],
            ['News', 'news', '/news'],
            ['Gallery', 'gallery', '/gallery'],
            ['Register', 'register', '/register'],
            ['Check Status', 'status', '/status'],
        ];

        foreach ($items as $order => [$label, $route, $url]) {
            NavItem::query()->updateOrCreate(
                ['route_name' => $route],
                ['label' => $label, 'url' => $url, 'is_visible' => true, 'sort_order' => $order],
            );
        }
    }

    private function about(): void
    {
        $values = [
            ['Music with purpose', 'Every edition is tied to a cause, and every registration fee goes back into community projects.'],
            ['Open to every voice', 'Choirs, barbershop quartets and beatbox crews are all welcome; the only entry requirement is a cappella.'],
            ['Owned by the participants', 'The festival is run by volunteers from the groups who have taken part in it.'],
        ];

        foreach ($values as $order => [$title, $body]) {
            SiteValue::query()->updateOrCreate(['title' => $title], ['body' => $body, 'sort_order' => $order]);
        }

        $objectives = [
            ['Grow a cappella in the region', 'Give East African groups a stage of their own and a reason to keep rehearsing.'],
            ['Raise funds for community causes', 'Route participation fees to local health and education partners each season.'],
            ['Build a judge and coach pipeline', 'Train conductors, arrangers and judges through the season workshops.'],
        ];

        foreach ($objectives as $order => [$title, $body]) {
            SiteObjective::query()->updateOrCreate(['title' => $title], ['body' => $body, 'sort_order' => $order]);
        }

        $activities = [
            ['Open rehearsals', 'Finalists run their sets on the main stage before the public arrives.'],
            ['Arrangement clinics', 'Half-day clinics on voicing, blend and live arrangement.'],
            ['Youth workshop', 'A free Saturday programme for school and youth groups.'],
        ];

        foreach ($activities as $order => [$title, $body]) {
            SiteActivity::query()->updateOrCreate(['title' => $title], ['body' => $body, 'sort_order' => $order]);
        }

        $terms = [
            ['Registration', 'A registration is confirmed once the application has been reviewed and the fee has been settled in full.'],
            ['Fees', 'Fees are quoted per member. Part payments are accepted but a place is only held once the minimum partial percentage has been met.'],
            ['Withdrawals', 'Groups may withdraw up to 30 days before the festival start date; fees already paid are non-refundable inside that window.'],
            ['Performance', 'Each group receives a 12 minute slot including sound check. Sets must be performed live and a cappella.'],
            ['Photography', 'By taking part you consent to being photographed and recorded for festival archives and publicity.'],
        ];

        foreach ($terms as $position => [$title, $body]) {
            TermsClause::query()->updateOrCreate(
                ['position' => $position + 1],
                ['title' => $title, 'body' => $body],
            );
        }

        $faqs = [
            ['Who can take part?', 'Any a cappella group can register, including barbershop quartets, gospel choirs and beatbox crews. Members are normally between 6 and 40.'],

            ['How much does it cost?', 'Fees are charged per member and shown on the registration form. Early-bird pricing applies until the early-bird deadline on each season page.'],

            ['Can we pay in instalments?', 'Yes. A minimum partial percentage is required to hold a place; the balance is due before the festival weekend.'],

            ['What happens after we register?', 'The registration office reviews every application, then shortlisted groups move into judging rounds.'],

            ['How do we check our application?', 'Use the Check Status page with the reference on your confirmation message and the contact email you registered with.'],
        ];

        foreach ($faqs as $order => [$question, $answer]) {
            FaqItem::query()->updateOrCreate(
                ['question' => $question],
                ['answer' => $answer, 'is_public' => true, 'sort_order' => $order],
            );
        }

        $stats = [
            ['1,200+', 'Singers on stage', 'Performers who have taken part since the festival began.', 'season'],
            ['34', 'Groups from 6 countries', 'Choirs, quartets and beatbox crews across the region.', 'season'],
            ['TZS 18M', 'Raised for community causes', 'Registration fees directed to partner projects.', 'season'],
            ['9', 'Counties reached', 'Groups from across mainland Tanzania and Zanzibar.', 'region'],
        ];

        foreach ($stats as $order => [$figure, $label, $body, $kind]) {
            ImpactStat::query()->updateOrCreate(
                ['label' => $label],
                ['figure' => $figure, 'body' => $body, 'kind' => $kind, 'sort_order' => $order],
            );
        }

        $methods = [
            ['Bank transfer', 'CRDB Bank, Arusha Branch. Account name: Culture Acapella Festival.'],
            ['Mobile money (M-Pesa)', 'M-Pesa till 5261890, Culture Acapella Festival.'],
            ['Bank transfer (USD)', 'Payments in USD are accepted at the season rate shown on the invoice.'],
        ];

        foreach ($methods as $order => [$name, $details]) {
            PaymentMethodOption::query()->updateOrCreate(
                ['name' => $name],
                ['account_details' => $details, 'is_public' => true, 'sort_order' => $order],
            );
        }
    }

    private function programme(Season $season): void
    {
        $groups = Registration::query()
            ->where('season_id', $season->getKey())
            ->where('status', RegistrationStatus::Confirmed->value)
            ->orderBy('code')
            ->get();

        $slots = [
            ['Main stage', 'Main stage', '18:00', '19:30', 'Opening ceremony and first half finals'],
            ['Main stage', 'Main stage', '20:00', '22:00', 'Second half finals'],
            ['River stage', 'River stage', '16:00', '18:00', 'Open rehearsal showcase'],
        ];

        foreach ($slots as $order => [$date, $stage, $starts, $ends, $title]) {
            ProgrammeSlot::query()->updateOrCreate(
                [
                    'season_id' => $season->getKey(),
                    'stage' => $stage,
                    'starts_at' => $starts,
                ],
                [
                    'event_date' => $season->starts_on,
                    'stage_location' => $stage === 'Main stage' ? 'Festival main arena' : 'Riverside lawn',
                    'ends_at' => $ends,
                    'title' => $title,
                    'status' => 'confirmed',
                    'is_public' => true,
                    'sort_order' => $order,
                ],
            );
        }

        foreach ($groups as $order => $group) {
            $start = sprintf('%02d:00', 9 + $order);
            $end = sprintf('%02d:30', 9 + $order);

            ProgrammeSlot::query()->updateOrCreate(
                [
                    'season_id' => $season->getKey(),
                    'stage' => 'River stage',
                    'starts_at' => $start,
                ],
                [
                    'registration_id' => $group->getKey(),
                    'event_date' => $season->starts_on,
                    'stage_location' => 'Riverside lawn',
                    'ends_at' => $end,
                    'title' => $group->group_name,
                    'description' => 'Competitive slot for '.$group->group_name.'.',
                    'status' => 'confirmed',
                    'is_public' => true,
                    'sort_order' => 10 + $order,
                ],
            );
        }

        $slides = [
            'Main stage, festival night',
            'Workshop in progress',
            'Group portrait',
            'Closing ceremony',
        ];

        foreach ($slides as $order => $alt) {
            GallerySlide::query()->updateOrCreate(
                ['alt_text' => $alt],
                [
                    'season_id' => $season->getKey(),
                    'image_url' => '/images/gallery/'.($order + 1).'.jpg',
                    'is_public' => true,
                    'sort_order' => $order,
                ],
            );
        }
    }

    private function partners(Season $season): void
    {
        $sponsors = [
            ['Mwanza Breweries', 'platinum', 'Title partner for the festival weekend.'],
            ['Safaricom Tanzania', 'gold', 'Connectivity and mobile money support.'],
            ['Coastal Hotels', 'silver', 'Accommodation for the judging panel.'],
            ['Kigamboni Cultural Centre', 'supporter', 'Venue partner.'],
        ];

        foreach ($sponsors as $order => [$name, $tier, $description]) {
            Sponsor::query()->updateOrCreate(
                ['name' => $name],
                [
                    'season_id' => $season->getKey(),
                    'tier' => $tier,
                    'description' => $description,
                    'website' => 'https://example.com/'.str($name)->slug(),
                    'is_public' => true,
                    'sort_order' => $order,
                ],
            );
        }

        $testimonials = [
            ['Neema Kambi', 'Choir director, Bagamoyo Collective', 'We came for the stage and stayed for the family. The judging feedback alone was worth the trip.'],
            ['BarakaallyMzee', 'Founder, Ukunda Rhythms', 'The festival took our beatbox crew from a rehearsal room to a main stage in two years.'],
            ['Aline Uwimana', 'Rwanda Melody', 'It is the only competition in the region where a cappella is treated as a serious craft.'],
        ];

        foreach ($testimonials as $order => [$author, $role, $quote]) {
            Testimonial::query()->updateOrCreate(
                ['author_name' => $author],
                [
                    'season_id' => $season->getKey(),
                    'author_role' => $role,
                    'quote' => $quote,
                    'is_public' => true,
                    'sort_order' => $order,
                ],
            );
        }
    }

    private function team(): void
    {
        $team = [
            ['Joseph S. Mwakalinga', 'Festival director', 'joseph.mwakalinga@example.com', 'Founded the festival in 2019 and chairs the board.'],
            ['Grace Mwakalinga', 'Head of administration', 'grace.mwakalinga@example.com', 'Runs registration, finance and the volunteer programme.'],
            ['Peter Ndosi', 'Registration officer', 'peter.ndosi@example.com', 'Handles applicant communication and status queries.'],
            ['Fatuma Salim', 'Finance officer', 'fatuma.salim@example.com', 'Responsible for invoicing, payments and reconciliation.'],
        ];

        foreach ($team as $order => [$name, $title, $email, $bio]) {
            TeamMember::query()->updateOrCreate(
                ['name' => $name],
                [
                    'role_title' => $title,
                    'email' => $email,
                    'phone' => '+25575'.str_pad((string) (700000 + $order), 6, '0', STR_PAD_LEFT),
                    'bio' => $bio,
                    'is_public' => true,
                    'sort_order' => $order,
                ],
            );
        }
    }
}
