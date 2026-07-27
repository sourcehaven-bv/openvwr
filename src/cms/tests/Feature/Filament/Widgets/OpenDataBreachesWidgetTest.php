<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Filament\Widgets\OpenDataBreachesWidget;
use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecord\Closed;
use App\Models\States\DataBreachRecord\InResponse;
use App\Models\States\DataBreachRecord\NoBreach;
use App\Models\States\DataBreachRecord\Reported;
use App\Models\States\DataBreachRecord\Verified;
use App\Services\Dashboard\DataBreachProgress;
use Carbon\CarbonImmutable;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('lists a breach whose handling is not finished', function (): void {
    $organisation = OrganisationTestHelper::create();

    $dataBreachRecord = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create([
            'name' => 'Laptop kwijtgeraakt',
            'completed_at' => null,
            'discovered_at' => CarbonImmutable::now()->subDays(5),
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(OpenDataBreachesWidget::class)
        ->assertSee($dataBreachRecord->name);
});

it('keeps listing an open breach that was reported to the ap', function (): void {
    $organisation = OrganisationTestHelper::create();

    $dataBreachRecord = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create([
            'name' => 'Gemeld maar nog niet afgerond',
            'completed_at' => null,
            'ap_reported' => true,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(OpenDataBreachesWidget::class)
        ->assertSee($dataBreachRecord->name);
});

it('drops a breach once its handling is completed, reported or not', function (bool $apReported): void {
    $organisation = OrganisationTestHelper::create();

    DataBreachRecord::factory()
        ->recycle($organisation)
        ->create([
            'completed_at' => CarbonImmutable::yesterday(),
            'ap_reported' => $apReported,
            'discovered_at' => CarbonImmutable::now()->subMonths(4),
        ]);

    $this->asFilamentOrganisationUser($organisation);

    expect(OpenDataBreachesWidget::canView())
        ->toBeFalse();
})->with([
    'assessed as not notifiable' => [false],
    'reported to the ap' => [true],
]);

it('drops a breach the state machine considers finished', function (string $state): void {
    $organisation = OrganisationTestHelper::create();

    DataBreachRecord::factory()
        ->recycle($organisation)
        ->create([
            // completed_at deliberately left empty: the state alone must be
            // enough to take the breach off the list.
            'completed_at' => null,
            'state' => $state,
            'discovered_at' => CarbonImmutable::now()->subMonths(4),
        ]);

    $this->asFilamentOrganisationUser($organisation);

    expect(OpenDataBreachesWidget::canView())
        ->toBeFalse();
})->with([
    'closed' => [Closed::$name],
    'assessed as no breach' => [NoBreach::$name],
]);

it('keeps listing a breach that is still moving through the workflow', function (string $state): void {
    $organisation = OrganisationTestHelper::create();

    DataBreachRecord::factory()
        ->recycle($organisation)
        ->create([
            'name' => 'Nog in behandeling',
            'completed_at' => null,
            'state' => $state,
            'discovered_at' => CarbonImmutable::now()->subDays(2),
        ]);

    $this->asFilamentOrganisationUser($organisation);

    expect(OpenDataBreachesWidget::canView())
        ->toBeTrue();
})->with([
    'reported' => [Reported::$name],
    'verified' => [Verified::$name],
    'in response' => [InResponse::$name],
]);

it('does not show another organisation\'s breaches', function (): void {
    $organisation = OrganisationTestHelper::create();
    $otherOrganisation = OrganisationTestHelper::create();

    DataBreachRecord::factory()
        ->recycle($otherOrganisation)
        ->create(['completed_at' => null]);

    $this->asFilamentOrganisationUser($organisation);

    expect(OpenDataBreachesWidget::canView())
        ->toBeFalse();
});

it('puts the longest-open breach first and undated ones last', function (): void {
    $organisation = OrganisationTestHelper::create();

    $undated = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['name' => 'Zonder datum', 'completed_at' => null, 'discovered_at' => null]);
    $recent = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create([
            'name' => 'Recent',
            'completed_at' => null,
            'discovered_at' => CarbonImmutable::now()->subDays(4),
        ]);
    $oldest = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create([
            'name' => 'Langst open',
            'completed_at' => null,
            'discovered_at' => CarbonImmutable::now()->subDays(40),
        ]);

    $this->asFilamentOrganisationUser($organisation);

    $names = array_column((new OpenDataBreachesWidget())->getRows(), 'name');

    expect($names)
        ->toBe([$oldest->name, $recent->name, $undated->name]);
});

it('cannot be viewed without the data breach permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['completed_at' => null]);

    $this->withPermissions($user, [Permission::CORE_ENTITY_VIEW])
        ->withFilamentSession($user, $organisation);

    expect(OpenDataBreachesWidget::canView())
        ->toBeFalse();
});

it('flags an unreported breach only once the 72 hour mark has passed', function (int $hoursAgo, bool $expected): void {
    $dataBreachRecord = DataBreachRecord::factory()->make([
        'discovered_at' => CarbonImmutable::now()->subHours($hoursAgo),
        'ap_reported' => false,
    ]);

    expect(DataBreachProgress::for($dataBreachRecord)->needsUrgentAttention())
        ->toBe($expected);
})->with([
    'within the window' => [24, false],
    'just past the window' => [73, true],
]);

it('never flags a reported or undated breach as urgent', function (): void {
    $reported = DataBreachRecord::factory()->make([
        'discovered_at' => CarbonImmutable::now()->subMonths(6),
        'ap_reported' => true,
    ]);
    $undated = DataBreachRecord::factory()->make([
        'discovered_at' => null,
        'ap_reported' => false,
    ]);

    expect(DataBreachProgress::for($reported)->needsUrgentAttention())->toBeFalse()
        ->and(DataBreachProgress::for($undated)->needsUrgentAttention())->toBeFalse();
});
