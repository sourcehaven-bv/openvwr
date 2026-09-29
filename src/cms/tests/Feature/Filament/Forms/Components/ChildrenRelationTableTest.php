<?php

declare(strict_types=1);

use App\Filament\Forms\Components\RelationTable;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\CreateAvgResponsibleProcessingRecord;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use Filament\Forms\Components\Select;
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

    [$keep, $remove] = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->count(2)
        ->create(['parent_id' => $record->id->toString()])
        ->all();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $record->getRouteKey(),
        ])
        ->callFormComponentAction(
            'children',
            RelationTable::REMOVE_ACTION,
            arguments: ['id' => $remove->id->toString()],
        )
        ->assertFormSet(['children' => [$keep->id->toString()]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($remove->refresh()->parent_id)
        ->toBeNull()
        ->and($keep->refresh()->parent_id?->toString())
        ->toBe($record->id->toString());
});

it('offers only linkable verwerkingen in the picker', function (): void {
    $organisation = OrganisationTestHelper::create();

    $parent = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['name' => 'zoekterm hoofdverwerking']);

    $record = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'name' => 'zoekterm zelf',
            'parent_id' => $parent->id->toString(),
        ]);

    $ownChild = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'name' => 'zoekterm eigen subverwerking',
            'parent_id' => $record->id->toString(),
        ]);

    $standalone = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['name' => 'zoekterm kandidaat']);

    $otherParent = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['name' => 'andere hoofdverwerking']);
    AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'name' => 'zoekterm subverwerking van ander',
            'parent_id' => $otherParent->id->toString(),
        ]);

    // Offered: the standalone candidate and the already-linked child. Not
    // offered: the record itself, its hoofdverwerking (would create a cycle)
    // and another record's subverwerking.
    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $record->getRouteKey(),
        ])
        ->assertFormFieldExists('children', function (Select $field) use ($standalone, $ownChild): bool {
            $values = array_column($field->getSearchResultsForJs('zoekterm'), 'value');
            sort($values);

            $expected = [$standalone->id->toString(), $ownChild->id->toString()];
            sort($expected);

            return $values === $expected;
        });
});

it('offers only standalone verwerkingen in the picker for a new record', function (): void {
    $organisation = OrganisationTestHelper::create();

    $standalone = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['name' => 'zoekterm kandidaat']);

    $otherParent = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['name' => 'andere hoofdverwerking']);
    AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'name' => 'zoekterm subverwerking van ander',
            'parent_id' => $otherParent->id->toString(),
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateAvgResponsibleProcessingRecord::class)
        ->assertFormFieldExists('children', function (Select $field) use ($standalone): bool {
            return array_column($field->getSearchResultsForJs('zoekterm'), 'value') === [$standalone->id->toString()];
        });
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
        // The picker never offers another hoofdverwerking's subverwerking, so a
        // value smuggled in past it fails the select's own `in` rule. v3 dropped
        // it silently; being told is the better outcome, and either way the
        // subverwerking keeps the parent it had.
        ->assertHasFormErrors(['children.0']);

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
        // Linking the record's own hoofdverwerking would make a cycle, so the
        // picker leaves it out and the select's `in` rule refuses it. What
        // matters either way: the hoofdverwerking keeps no parent of its own.
        ->assertHasFormErrors(['children.0']);

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
