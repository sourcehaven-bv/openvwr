<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\CreateAvgResponsibleProcessingRecord;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Filament\Resources\SystemResource\Pages\CreateSystem;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\System;
use Tests\Helpers\Model\OrganisationTestHelper;

use function __;
use function expect;
use function it;
use function str_repeat;

// Follow-up to the concept saving on the edit pages: creating enforced required fields
// too, so a user who only knew part of the answers could not even start the record.
// Creating now stores a concept as well; required fields are enforced when a version
// (snapshot) is created.

it('creates a concept when a required field is empty', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateAvgResponsibleProcessingRecord::class)
        ->fillForm(['name' => ''])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(AvgResponsibleProcessingRecord::query()->where('name', '')->count())
        ->toBe(1);
});

// The risk from relaxing validation on create: `name` is NOT NULL, and an untouched
// text field arrives as null (ConvertEmptyStringsToNull). Without coercion this hits a
// database error instead of storing a concept. A brand new record is the worst case,
// because every single field is empty.
it('creates a concept from a completely empty form without a database error', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateAvgResponsibleProcessingRecord::class)
        ->call('create')
        ->assertHasNoFormErrors();

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::query()->sole();
    expect($avgResponsibleProcessingRecord->name)
        ->toBe('');
});

it('creates a concept from a completely empty form on a page without an entity number', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateSystem::class)
        ->call('create')
        ->assertHasNoFormErrors();

    expect(System::query()->sole()->description)
        ->toBe('');
});

it('still rejects a created concept whose data is genuinely invalid', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateAvgResponsibleProcessingRecord::class)
        ->fillForm(['name' => str_repeat('a', 256)])
        ->call('create')
        ->assertHasFormErrors(['name']);

    expect(AvgResponsibleProcessingRecord::query()->count())
        ->toBe(0);
});

// The boundary of the `nullable` swap: an empty field is not judged, but a field that
// was actually filled in is still held to every other rule. Without this the relaxation
// would silently accept a lookup value that does not exist.
it('still rejects a filled in value that is invalid, even while saving a concept', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateAvgResponsibleProcessingRecord::class)
        ->fillForm([
            'name' => 'Wel ingevuld',
            'avg_responsible_processing_record_service_id' => '01a05419-0000-7000-8000-000000000000',
        ])
        ->call('create')
        ->assertHasFormErrors(['avg_responsible_processing_record_service_id']);

    expect(AvgResponsibleProcessingRecord::query()->count())
        ->toBe(0);
});

// Relaxing create must not weaken the gate: a record created as an empty concept is
// still incomplete, so it cannot become a version until it is filled in.
it('blocks creating a version for a record that was created as an empty concept', function (): void {
    $organisation = OrganisationTestHelper::create();
    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(CreateAvgResponsibleProcessingRecord::class)
        ->call('create')
        ->assertHasNoFormErrors();

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::query()->sole();

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->callAction('snapshot_create')
        ->assertNotified(__('snapshot.incomplete'));

    expect($avgResponsibleProcessingRecord->refresh()->snapshots)
        ->toHaveCount(0);
});
