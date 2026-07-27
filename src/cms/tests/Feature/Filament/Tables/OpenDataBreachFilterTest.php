<?php

declare(strict_types=1);

use App\Filament\Resources\DataBreachRecord\Pages\ListDataBreachRecords;
use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecord\Closed;
use Carbon\CarbonImmutable;
use Tests\Helpers\Model\OrganisationTestHelper;

it('narrows the register to breaches still being handled', function (): void {
    $organisation = OrganisationTestHelper::create();

    $open = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['completed_at' => null, 'state' => null]);
    $closed = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['completed_at' => null, 'state' => Closed::class]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(
            ListDataBreachRecords::class,
            queryParameters: ['tableFilters' => ['open' => ['value' => '1']]],
        )
        ->assertCanSeeTableRecords([$open])
        ->assertCanNotSeeTableRecords([$closed]);
});

it('narrows the register to breaches whose handling is finished', function (): void {
    $organisation = OrganisationTestHelper::create();

    $open = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['completed_at' => null, 'state' => null]);
    $closedByState = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['completed_at' => null, 'state' => Closed::class]);
    // Finished the old way: completed_at filled while the state never moved.
    $closedByDate = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['completed_at' => CarbonImmutable::yesterday(), 'state' => null]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(
            ListDataBreachRecords::class,
            queryParameters: ['tableFilters' => ['open' => ['value' => '0']]],
        )
        ->assertCanSeeTableRecords([$closedByState, $closedByDate])
        ->assertCanNotSeeTableRecords([$open]);
});

it('leaves the register untouched when the breach filter is not set', function (): void {
    $organisation = OrganisationTestHelper::create();

    $open = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['completed_at' => null, 'state' => null]);
    $closed = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['completed_at' => null, 'state' => Closed::class]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(
            ListDataBreachRecords::class,
            queryParameters: ['tableFilters' => ['open' => ['value' => '']]],
        )
        ->assertCanSeeTableRecords([$open, $closed]);
});
