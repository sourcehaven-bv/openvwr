<?php

declare(strict_types=1);

use App\Enums\LabelColor;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\ListAvgResponsibleProcessingRecords;
use App\Models\Tag;
use Filament\Forms\Components\Select;

/**
 * The filter renders the selected labels itself, which is where an earlier
 * version broke: Filament's getOptionLabelFromRecordUsing keys its result by
 * the record's key, and ids are cast to a Uuid object here, which PHP cannot
 * use as an array key.
 *
 * That path is getOptionLabels(), reached when the filter panel draws the
 * values already chosen. Rendering the table alone does not reach it, so the
 * call is made directly rather than through a page render.
 */

/**
 * @param array<int, string> $values
 */
function tagFilterSelect(array $values): Select
{
    $page = test()->createLivewireTestable(ListAvgResponsibleProcessingRecords::class)
        ->set('tableFilters.tag.values', $values)
        ->instance();

    foreach ($page->getTable()->getFiltersForm()->getFlatComponents(true) as $component) {
        if ($component instanceof Select && str_contains($component->getStatePath(), 'tag')) {
            return $component;
        }
    }

    throw new RuntimeException('The label filter is not on the table.');
}

it('labels the selected labels', function (): void {
    $tag = Tag::factory()->create(['color' => LabelColor::TEAL]);

    test()->asFilamentOrganisationUser($tag->organisation);

    $labels = tagFilterSelect([$tag->id->toString()])->getOptionLabels();

    expect($labels)->toHaveKey($tag->id->toString())
        ->and($labels[$tag->id->toString()])->toContain($tag->name)
        ->and($labels[$tag->id->toString()])->toContain('teal-600');
});

it('labels a selected label that has no colour', function (): void {
    $tag = Tag::factory()->create();
    $tag->forceFill(['color' => null])->saveQuietly();

    test()->asFilamentOrganisationUser($tag->organisation);

    $labels = tagFilterSelect([$tag->id->toString()])->getOptionLabels();

    // The name still renders; only the dot is left off.
    expect($labels[$tag->id->toString()])->toContain($tag->name)
        ->and($labels[$tag->id->toString()])->not->toContain('-600');
});

it('shows no indicators when nothing is selected', function (): void {
    $tag = Tag::factory()->create();

    test()->asFilamentOrganisationUser($tag->organisation);

    // The filter renders its own indicators; with no selection there is
    // nothing to show, and the bar stays empty.
    $indicators = test()->createLivewireTestable(ListAvgResponsibleProcessingRecords::class)
        ->instance()
        ->getTable()
        ->getFilter('tag')
        ->getIndicators();

    expect($indicators)->toBe([]);
});
