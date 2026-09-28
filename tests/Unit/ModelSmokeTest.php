<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\Campaign;
use App\Models\ContentPage;
use App\Models\FaqItem;
use App\Models\GallerySlide;
use App\Models\ImpactStat;
use App\Models\Integration;
use App\Models\Invoice;
use App\Models\InvoiceAdjustment;
use App\Models\Judge;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\NavItem;
use App\Models\Payment;
use App\Models\PaymentMethodOption;
use App\Models\ProgrammeSlot;
use App\Models\Registration;
use App\Models\RegistrationMember;
use App\Models\RegistrationStatusEvent;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\ReviewScore;
use App\Models\RubricCriterion;
use App\Models\Season;
use App\Models\Setting;
use App\Models\SiteActivity;
use App\Models\SiteObjective;
use App\Models\SiteValue;
use App\Models\Sponsor;
use App\Models\TeamMember;
use App\Models\TermsClause;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModelSmokeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{class-string}>
     */
    public static function modelProvider(): array
    {
        return [
            'User' => [User::class],
            'Season' => [Season::class],
            'Registration' => [Registration::class],
            'RegistrationMember' => [RegistrationMember::class],
            'RegistrationStatusEvent' => [RegistrationStatusEvent::class],
            'Invoice' => [Invoice::class],
            'Payment' => [Payment::class],
            'InvoiceAdjustment' => [InvoiceAdjustment::class],
            'ReviewRound' => [ReviewRound::class],
            'RubricCriterion' => [RubricCriterion::class],
            'Judge' => [Judge::class],
            'ReviewAssignment' => [ReviewAssignment::class],
            'ReviewScore' => [ReviewScore::class],
            'MessageThread' => [MessageThread::class],
            'Message' => [Message::class],
            'Campaign' => [Campaign::class],
            'ContentPage' => [ContentPage::class],
            'ProgrammeSlot' => [ProgrammeSlot::class],
            'Sponsor' => [Sponsor::class],
            'Testimonial' => [Testimonial::class],
            'FaqItem' => [FaqItem::class],
            'TermsClause' => [TermsClause::class],
            'SiteValue' => [SiteValue::class],
            'SiteObjective' => [SiteObjective::class],
            'SiteActivity' => [SiteActivity::class],
            'ImpactStat' => [ImpactStat::class],
            'PaymentMethodOption' => [PaymentMethodOption::class],
            'TeamMember' => [TeamMember::class],
            'GallerySlide' => [GallerySlide::class],
            'NavItem' => [NavItem::class],
            'AuditLog' => [AuditLog::class],
            'Setting' => [Setting::class],
            'Integration' => [Integration::class],
        ];
    }

    /**
     * @param  class-string  $model
     */
    #[Test]
    #[DataProvider('modelProvider')]
    public function model_maps_to_an_existing_table(string $model): void
    {
        $instance = new $model;

        $this->assertTrue(
            Schema::hasTable($instance->getTable()),
            $model.' maps to missing table '.$instance->getTable(),
        );
    }

    /**
     * @param  class-string  $model
     */
    #[Test]
    #[DataProvider('modelProvider')]
    public function every_fillable_column_exists_in_the_table(string $model): void
    {
        $instance = new $model;
        $columns = Schema::getColumnListing($instance->getTable());
        $primaryKey = $instance->getKeyName();

        $fillable = method_exists($instance, 'getFillable')
            ? $instance->getFillable()
            : [$primaryKey];

        foreach (array_diff($fillable, $columns, [$primaryKey]) as $missing) {
            $this->fail("{$model} fillable column [{$missing}] is missing from table {$instance->getTable()}");
        }

        $this->assertTrue(true);
    }
}
