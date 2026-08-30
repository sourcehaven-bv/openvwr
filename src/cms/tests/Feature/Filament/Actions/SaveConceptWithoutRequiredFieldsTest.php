<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use Tests\Helpers\Model\OrganisationTestHelper;

use function __;
use function expect;
use function it;
use function str_repeat;

// Tester feedback: stepping through the wizard never complains about empty fields,
// but pressing save suddenly reports required fields. Saving a concept half-finished
// is intentional, so required fields are only enforced when a version is created.

it('saves a concept even when a required field is empty', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->fillForm(['name' => ''])
        ->call('save')
        ->assertHasNoFormErrors();

    $avgResponsibleProcessingRecord->refresh();
    expect($avgResponsibleProcessingRecord->name)
        ->toBe('');
});

it('still rejects a concept whose data is genuinely invalid', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->fillForm(['name' => str_repeat('a', 256)])
        ->call('save')
        ->assertHasFormErrors(['name']);
});

it('blocks creating a version when a required field is empty and names that field', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $component = $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->fillForm(['name' => ''])
        ->call('save')
        ->callAction('snapshot_create');

    $component->assertNotified(__('snapshot.incomplete'));

    $avgResponsibleProcessingRecord->refresh();
    expect($avgResponsibleProcessingRecord->snapshots)
        ->toHaveCount(0);
});

it('creates a version when every required field is filled', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->callAction('snapshot_create')
        ->assertNotified(__('snapshot.created'));

    $avgResponsibleProcessingRecord->refresh();
    expect($avgResponsibleProcessingRecord->snapshots)
        ->toHaveCount(1);
});
