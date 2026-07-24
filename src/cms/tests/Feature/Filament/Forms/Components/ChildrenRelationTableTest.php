<?php

declare(strict_types=1);

use App\Filament\Forms\Components\RelationTable;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use Tests\Helpers\Model\OrganisationTestHelper;

function createEditableRecord(mixed $organisation): AvgResponsibleProcessingRecord
{
    return AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create([
            'has_security' => false,
            'outside_eu' => false,
            'outside_eu_protection_level' => true,
        ]);
}

it('shows the subverwerkingen as table rows', function (): void {
    $organisation = OrganisationTestHelper::create();
    $record = createEditableRecord($organisation);

    $child = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['parent_id' => $record->id->toString()]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $record->getRouteKey(),
        ])
        ->assertFormSet(['children' => [$child->id->toString()]])
        ->assertSee($child->name);
});

it('links the subverwerking number to its edit page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $record = createEditableRecord($organisation);

    $child = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['parent_id' => $record->id->toString()]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $record->getRouteKey(),
        ])
        ->assertSeeHtml('href="' . AvgResponsibleProcessingRecordResource::getUrl('edit', [
            'record' => $child,
        ]) . '"');
});

it('links a standalone verwerking as subverwerking when selected and saved', function (): void {
    $organisation = OrganisationTestHelper::create();
    $record = createEditableRecord($organisation);
    $standalone = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $record->getRouteKey(),
        ])
        ->fillForm([
            'children' => [$standalone->id->toString()],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($standalone->refresh()->parent_id?->toString())
        ->toBe($record->id->toString());
});

it('unlinks a subverwerking through the remove action, making it standalone again', function (): void {
    $organisation = OrganisationTestHelper::create();
    $record = createEditableRecord($organisation);

    $child = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['parent_id' => $record->id->toString()]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $record->getRouteKey(),
        ])
        ->callFormComponentAction(
            'children',
            RelationTable::REMOVE_ACTION,
            arguments: ['id' => $child->id->toString()],
        )
        ->assertFormSet(['children' => []])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($child->refresh()->parent_id)
        ->toBeNull();
});

it('does not steal a subverwerking that belongs to another hoofdverwerking', function (): void {
    $organisation = OrganisationTestHelper::create();
    $record = createEditableRecord($organisation);

    $otherParent = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();
    $otherChild = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['parent_id' => $otherParent->id->toString()]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $record->getRouteKey(),
        ])
        ->fillForm([
            'children' => [$otherChild->id->toString()],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($otherChild->refresh()->parent_id?->toString())
        ->toBe($otherParent->id->toString());
});

it('does not link its own hoofdverwerking as subverwerking', function (): void {
    $organisation = OrganisationTestHelper::create();
    $parent = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();

    $record = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create([
            'has_security' => false,
            'outside_eu' => false,
            'outside_eu_protection_level' => true,
            'parent_id' => $parent->id->toString(),
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $record->getRouteKey(),
        ])
        ->fillForm([
            'children' => [$parent->id->toString()],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($parent->refresh()->parent_id)
        ->toBeNull();
});

it('rejects a subverwerking from another organisation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $record = createEditableRecord($organisation);

    $otherOrganisation = OrganisationTestHelper::create();
    $foreignRecord = AvgResponsibleProcessingRecord::factory()->recycle($otherOrganisation)->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $record->getRouteKey(),
        ])
        ->fillForm([
            'children' => [$foreignRecord->id->toString()],
        ])
        ->call('save')
        ->assertHasFormErrors(['children']);

    expect($foreignRecord->refresh()->parent_id)
        ->toBeNull();
});
