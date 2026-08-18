<?php

declare(strict_types=1);

use App\Filament\Forms\Components\RetentionPeriodInput;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\RetentionPeriod;
use App\Models\Stakeholder;
use App\Models\StakeholderDataItem;
use Livewire\Features\SupportTesting\Testable;
use Tests\Helpers\Model\OrganisationTestHelper;

use function Pest\Livewire\livewire;

/**
 * A record with one gegeven whose bewaartermijn is $stored, opened for editing.
 *
 * @return array{0: Testable, 1: StakeholderDataItem}
 */
function editRecordWithRetentionPeriod(string $stored, ?string $listedTerm = null): array
{
    $organisation = OrganisationTestHelper::create();

    if ($listedTerm !== null) {
        RetentionPeriod::factory()
            ->recycle($organisation)
            ->create(['name' => $listedTerm, 'enabled' => true]);
    }

    // withValidState() so the form submits without errors on fields that have
    // nothing to do with the bewaartermijn: the factory randomises several
    // toggles that each make another field required.
    $record = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $stakeholder = Stakeholder::factory()->recycle($organisation)->create();

    $dataItem = StakeholderDataItem::factory()
        ->hasAttached($stakeholder)
        ->recycle($organisation)
        ->create(['retention_period' => $stored]);

    $record->stakeholders()->attach($stakeholder);

    test()->asFilamentOrganisationUser($organisation);

    return [
        livewire(EditAvgResponsibleProcessingRecord::class, ['record' => $record->getRouteKey()]),
        $dataItem,
    ];
}

it('offers the enabled terms of the organisation plus an "overig" option', function (): void {
    $organisation = OrganisationTestHelper::create();

    RetentionPeriod::factory()
        ->recycle($organisation)
        ->create(['name' => '7 jaar na afloop van het boekjaar', 'enabled' => true]);

    $this->asFilamentOrganisationUser($organisation);

    $options = RetentionPeriodInput::options();

    // Keyed by their own text: the record stores the term, not an id.
    expect($options)->toHaveKey('7 jaar na afloop van het boekjaar')
        ->and($options['7 jaar na afloop van het boekjaar'])->toBe('7 jaar na afloop van het boekjaar')
        ->and($options)->toHaveKey(RetentionPeriodInput::OTHER);
});

it('leaves disabled terms out of the options', function (): void {
    $organisation = OrganisationTestHelper::create();

    RetentionPeriod::factory()
        ->recycle($organisation)
        ->create(['name' => 'Niet meer gebruiken', 'enabled' => false]);

    $this->asFilamentOrganisationUser($organisation);

    expect(RetentionPeriodInput::options())->not->toHaveKey('Niet meer gebruiken')
        ->and(RetentionPeriodInput::hasTerms())->toBeFalse();
});

it('does not leak terms of another organisation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $other = OrganisationTestHelper::create();

    RetentionPeriod::factory()
        ->recycle($other)
        ->create(['name' => 'Termijn van een andere organisatie', 'enabled' => true]);

    $this->asFilamentOrganisationUser($organisation);

    expect(RetentionPeriodInput::options())->not->toHaveKey('Termijn van een andere organisatie')
        ->and(RetentionPeriodInput::hasTerms())->toBeFalse();
});

it('reports no terms when the organisation has an empty list', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);

    // Drives the widget: with no list the select is hidden and only the free
    // text field is shown, so the sole option must be "overig".
    expect(RetentionPeriodInput::hasTerms())->toBeFalse()
        ->and(RetentionPeriodInput::options())->toHaveCount(1)
        ->and(RetentionPeriodInput::options())->toHaveKey(RetentionPeriodInput::OTHER);
});

it('builds the hidden field plus the two visible fields', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);

    $components = RetentionPeriodInput::make('retention_period', 'Bewaartermijn');

    expect($components)->toHaveCount(1);

    $names = [];
    foreach ($components[0]->getChildComponents() as $child) {
        $names[] = $child->getName();
    }

    expect($names)->toBe(['retention_period', 'retention_period_choice', 'retention_period_custom']);
});

/**
 * The livewire path of the single gegeven in the form, e.g.
 * data.stakeholders.record-<uuid>.stakeholder_data_items.record-<uuid>
 */
function retentionPeriodPath(mixed $page): string
{
    /** @var array<string, mixed> $data */
    $data = $page->get('data');

    /** @var array<string, mixed> $stakeholders */
    $stakeholders = $data['stakeholders'];
    $stakeholderKey = array_key_first($stakeholders);

    /** @var array<string, mixed> $stakeholder */
    $stakeholder = $stakeholders[$stakeholderKey];
    /** @var array<string, mixed> $items */
    $items = $stakeholder['stakeholder_data_items'];
    $itemKey = array_key_first($items);

    return 'data.stakeholders.' . $stakeholderKey . '.stakeholder_data_items.' . $itemKey;
}

/**
 * The bewaartermijn state of the single gegeven in the form.
 *
 * @return array{choice: mixed, custom: mixed, stored: mixed}
 */
function retentionPeriodState(mixed $page): array
{
    /** @var array<string, mixed> $data */
    $data = $page->get('data');

    /** @var array<string, mixed> $stakeholders */
    $stakeholders = $data['stakeholders'];
    /** @var array<string, mixed> $stakeholder */
    $stakeholder = reset($stakeholders);
    /** @var array<string, mixed> $items */
    $items = $stakeholder['stakeholder_data_items'];
    /** @var array<string, mixed> $item */
    $item = reset($items);

    return [
        'choice' => $item['retention_period_choice'] ?? null,
        'custom' => $item['retention_period_custom'] ?? null,
        'stored' => $item['retention_period'] ?? null,
    ];
}

it('opens a stored term that is on the list as the chosen option', function (): void {
    $term = '7 jaar (fiscale bewaarplicht, art. 52 lid 4 AWR)';
    [$page] = editRecordWithRetentionPeriod($term, $term);

    expect(retentionPeriodState($page))
        ->choice->toBe($term)
        ->stored->toBe($term);
});

it('opens free text that is not on the list as "overig"', function (): void {
    // Hand-typed, or a list item that was renamed or removed since: it has to
    // stay editable instead of silently resetting to nothing.
    [$page] = editRecordWithRetentionPeriod('3 jaar na afronding van het project', 'Een andere termijn');

    expect(retentionPeriodState($page))
        ->choice->toBe(RetentionPeriodInput::OTHER)
        ->custom->toBe('3 jaar na afronding van het project');
});

it('opens an empty bewaartermijn with nothing chosen', function (): void {
    [$page] = editRecordWithRetentionPeriod('', 'Een termijn');

    expect(retentionPeriodState($page))->choice->toBeNull();
});

it('falls back to the free text field when the list is empty', function (): void {
    // No list at all: the select is hidden, so the textarea has to be seeded
    // from the stored value itself.
    [$page] = editRecordWithRetentionPeriod('2 jaar na afhandeling van de melding');

    expect(retentionPeriodState($page))
        ->custom->toBe('2 jaar na afhandeling van de melding');
});

it('writes a term picked from the list to the stored column', function (): void {
    $term = '4 weken na einde sollicitatieprocedure';
    [$page, $dataItem] = editRecordWithRetentionPeriod('Iets anders', $term);

    $path = retentionPeriodPath($page);

    $page->set($path . '.retention_period_choice', $term)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($dataItem->refresh()->retention_period)->toBe($term);
});

it('writes the free text to the stored column when "overig" is chosen', function (): void {
    [$page, $dataItem] = editRecordWithRetentionPeriod('Iets anders', 'Een termijn uit de lijst');

    $path = retentionPeriodPath($page);

    $page->set($path . '.retention_period_choice', RetentionPeriodInput::OTHER)
        ->set($path . '.retention_period_custom', '6 maanden na afsluiting van het dossier')
        ->call('save')
        ->assertHasNoFormErrors();

    expect($dataItem->refresh()->retention_period)->toBe('6 maanden na afsluiting van het dossier');
});
