<?php

declare(strict_types=1);

use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\ListAvgResponsibleProcessingRecords;
use App\Filament\Tables\Columns\ExpiringDateColumn;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\ValueObjects\CalendarDate;
use Carbon\CarbonImmutable;
use Tests\Helpers\Model\OrganisationTestHelper;

it('can make the column if the field is null', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => null]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAvgResponsibleProcessingRecords::class)
        ->assertTableColumnExists('review_at', static function (ExpiringDateColumn $column): bool {
            return $column->getColor($column->getState()) === null;
        }, $avgResponsibleProcessingRecord);
});

it('can make the column if the field is in the past', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CalendarDate::createFromFormat('Y-m-d', CarbonImmutable::yesterday()->format('Y-m-d'))]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAvgResponsibleProcessingRecords::class)
        ->assertTableColumnExists('review_at', static function (ExpiringDateColumn $column): bool {
            return $column->getColor($column->getState()) === 'danger';
        }, $avgResponsibleProcessingRecord);
});


it('can make the column if the field is in the future', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CalendarDate::createFromFormat('Y-m-d', CarbonImmutable::tomorrow()->format('Y-m-d'))]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAvgResponsibleProcessingRecords::class)
        ->assertTableColumnExists('review_at', static function (ExpiringDateColumn $column): bool {
            return $column->getColor('danger') === null;
        }, $avgResponsibleProcessingRecord);
});
