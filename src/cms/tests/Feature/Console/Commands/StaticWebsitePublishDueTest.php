<?php

declare(strict_types=1);

use App\Events\StaticWebsite\BuildEvent;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\PublicWebsiteTree;
use App\Models\StaticWebsiteCheck;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\ConfigTestHelper;

beforeEach(function (): void {
    CarbonImmutable::setTestNow('2026-10-26 12:00:00');
});

/**
 * Created with the build event faked, so saving the record does not trigger
 * the observer's own build.
 */
function lastBuildAt(string $buildDate): void
{
    StaticWebsiteCheck::factory()->create(['build_date' => CarbonImmutable::parse($buildDate)]);
}

it('builds when a record public from date passed since the last build', function (): void {
    Event::fake([BuildEvent::class]);
    lastBuildAt('2026-10-26 11:50:00');
    AvgResponsibleProcessingRecord::factory()->create(['public_from' => CarbonImmutable::parse('2026-10-26 11:55:00')]);

    $this->artisan('static-website:publish-due')->assertSuccessful();

    Event::assertDispatchedTimes(BuildEvent::class, 1);
});

it('builds when a website section public from date passed since the last build', function (): void {
    Event::fake([BuildEvent::class]);
    lastBuildAt('2026-10-25 23:00:00');
    PublicWebsiteTree::factory()->create(['public_from' => '2026-10-26']);

    $this->artisan('static-website:publish-due')->assertSuccessful();

    Event::assertDispatched(BuildEvent::class);
});

it('does not build when no date passed since the last build', function (): void {
    Event::fake([BuildEvent::class]);
    AvgResponsibleProcessingRecord::factory()->create(['public_from' => CarbonImmutable::parse('2026-10-26 11:40:00')]);
    AvgResponsibleProcessingRecord::factory()->create(['public_from' => CarbonImmutable::parse('2026-10-27 09:00:00')]);
    lastBuildAt('2026-10-26 11:50:00');

    $this->artisan('static-website:publish-due')->assertSuccessful();

    Event::assertNotDispatched(BuildEvent::class);
});

it('stops retrying an hour after the date passed', function (): void {
    Event::fake([BuildEvent::class]);
    lastBuildAt('2026-10-26 09:00:00');
    AvgResponsibleProcessingRecord::factory()->create(['public_from' => CarbonImmutable::parse('2026-10-26 10:30:00')]);

    $this->artisan('static-website:publish-due')->assertSuccessful();

    Event::assertNotDispatched(BuildEvent::class);
});

it('does nothing before the first checked build', function (): void {
    Event::fake([BuildEvent::class]);
    AvgResponsibleProcessingRecord::factory()->create(['public_from' => CarbonImmutable::parse('2026-10-26 11:55:00')]);

    $this->artisan('static-website:publish-due')->assertSuccessful();

    Event::assertNotDispatched(BuildEvent::class);
});

it('does nothing when publishing is disabled', function (): void {
    ConfigTestHelper::set('features.publishing', false);
    Event::fake([BuildEvent::class]);
    lastBuildAt('2026-10-26 11:50:00');
    AvgResponsibleProcessingRecord::factory()->create(['public_from' => CarbonImmutable::parse('2026-10-26 11:55:00')]);

    $this->artisan('static-website:publish-due')->assertSuccessful();

    Event::assertNotDispatched(BuildEvent::class);
});
