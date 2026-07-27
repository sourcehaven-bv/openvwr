<?php

declare(strict_types=1);

use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\ListAvgResponsibleProcessingRecords;
use App\Filament\Tables\DateWindowFilter;
use App\Models\Avg\AvgResponsibleProcessingRecord;
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
            queryParameters: ['tableFilters' => ['review_at' => ['value' => DateWindowFilter::OVERDUE]]],
        )
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
            queryParameters: ['tableFilters' => ['review_at' => ['value' => DateWindowFilter::SOON]]],
        )
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
            queryParameters: ['tableFilters' => ['review_at' => ['value' => $window]]],
        )
        ->assertCanNotSeeTableRecords([$withoutReviewDate]);
})->with([
    DateWindowFilter::OVERDUE,
    DateWindowFilter::SOON,
]);
