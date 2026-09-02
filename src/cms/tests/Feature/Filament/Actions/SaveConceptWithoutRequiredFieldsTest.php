<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\InReview;
use Tests\Helpers\Model\OrganisationTestHelper;

use function expect;
use function it;
use function str_repeat;

// Tester feedback: stepping through the wizard never complains about empty fields,
// but pressing save suddenly reports required fields. Saving a concept half-finished
// is intentional, so required fields are only enforced when the concept is sent to review.

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

it('blocks submitting for review when a required field is empty, on the field itself', function (): void {
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
        ->callAction('snapshot_submit_for_review')
        // The point of moving this to the form: a real validation error on the field,
        // which the wizard turns into a marked step, instead of a corner notification.
        ->assertHasFormErrors(['name']);

    $snapshot = $avgResponsibleProcessingRecord->refresh()->snapshots->sole();
    expect($snapshot->state)->toBeInstanceOf(Concept::class);
});

it('saves and submits in one press when every required field is filled', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    // Deliberately not saved first: pressing submit means "submit what I see".
    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->fillForm(['name' => 'Klaar voor review'])
        ->callAction('snapshot_submit_for_review')
        ->assertHasNoFormErrors();

    $avgResponsibleProcessingRecord->refresh();
    $snapshot = $avgResponsibleProcessingRecord->snapshots->sole();

    expect($avgResponsibleProcessingRecord->name)->toBe('Klaar voor review')
        ->and($snapshot->state)->toBeInstanceOf(InReview::class)
        ->and($snapshot->name)->toBe('Klaar voor review');
});
