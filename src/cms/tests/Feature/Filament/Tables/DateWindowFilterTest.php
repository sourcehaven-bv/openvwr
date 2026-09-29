<?php

declare(strict_types=1);

use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\ListAvgResponsibleProcessingRecords;
use App\Filament\Tables\DateWindowFilter;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Services\Dashboard\DateWindow;
use Carbon\CarbonImmutable;
use Tests\Helpers\Model\OrganisationTestHelper;

it('narrows the register to records whose review date has passed', function (): void {
    $organisation = OrganisationTestHelper::create();

    $overdue = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::yesterday()->toDateString()]);
    $upcoming = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::today()->addMonth()->toDateString()]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(
            ListAvgResponsibleProcessingRecords::class,
        )
        ->set('tableFilters.review_at.value', DateWindowFilter::OVERDUE)
        ->call('applyTableFilters')
        ->assertCanSeeTableRecords([$overdue])
        ->assertCanNotSeeTableRecords([$upcoming]);
});

it('narrows the register to records coming up for review', function (): void {
    $organisation = OrganisationTestHelper::create();

    $overdue = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::yesterday()->toDateString()]);
    $upcoming = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::today()->addMonth()->toDateString()]);
    $distant = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::today()->addYear()->toDateString()]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(
            ListAvgResponsibleProcessingRecords::class,
        )
        ->set('tableFilters.review_at.value', DateWindowFilter::SOON)
        ->call('applyTableFilters')
        ->assertCanSeeTableRecords([$upcoming])
        ->assertCanNotSeeTableRecords([$overdue, $distant]);
});

it('excludes records without a review date from both windows', function (string $window): void {
    $organisation = OrganisationTestHelper::create();

    $withoutReviewDate = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => null]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(
            ListAvgResponsibleProcessingRecords::class,
        )
        ->set('tableFilters.review_at.value', $window)
        ->call('applyTableFilters')
        ->assertCanNotSeeTableRecords([$withoutReviewDate]);
})->with([
    DateWindowFilter::OVERDUE,
    DateWindowFilter::SOON,
]);

it('leaves the register untouched when no window is chosen', function (): void {
    $organisation = OrganisationTestHelper::create();

    $overdue = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::yesterday()->toDateString()]);
    $upcoming = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::today()->addMonth()->toDateString()]);

    // The empty value is the "no filter" state the table starts in; it must not
    // narrow anything away.
    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(
            ListAvgResponsibleProcessingRecords::class,
        )
        ->set('tableFilters.review_at.value', '')
        ->call('applyTableFilters')
        ->assertCanSeeTableRecords([$overdue, $upcoming]);
});

it('carries a custom horizon into the filter', function (): void {
    $filter = DateWindowFilter::make('review_at')
        ->dateWindow(new DateWindow(1));

    expect($filter->getDateWindow()->soonInMonths)
        ->toBe(1);
});
