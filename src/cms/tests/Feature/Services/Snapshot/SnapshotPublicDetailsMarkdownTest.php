<?php

declare(strict_types=1);

use App\Models\Snapshot;
use App\Models\SnapshotData;
use App\Services\Snapshot\SnapshotPublicDetailsMarkdown;

/**
 * @param array<string, mixed> $frontmatter
 */
function snapshotWithFrontmatter(array $frontmatter): Snapshot
{
    $snapshot = Snapshot::factory()->create();
    SnapshotData::factory()->for($snapshot)->create(['public_frontmatter' => $frontmatter]);

    return $snapshot->refresh();
}

it('renders nothing without public details', function (array $frontmatter): void {
    expect(app(SnapshotPublicDetailsMarkdown::class)->render(snapshotWithFrontmatter($frontmatter)))->toBeNull();
})->with([
    'no record' => [[]],
    'record without details' => [['record' => ['reference' => 'A-1']]],
    'record is not a list' => [['record' => 'A-1']],
]);

it('renders every public field for the approver', function (): void {
    $markdown = app(SnapshotPublicDetailsMarkdown::class)->render(snapshotWithFrontmatter([
        'record' => [
            'details' => [
                'summary' => 'Uitleg voor de bewoner',
                'goals' => [['goal' => 'Vergunning beoordelen', 'legal_basis' => 'public_task'], ['goal' => 'Onbekend', 'legal_basis' => null]],
                'stakeholders' => [['description' => 'Bewoners', 'special_categories' => ['health'], 'citizen_service_number' => true]],
                'has_processors' => true,
                'outside_eu' => ['applies' => true, 'description' => 'Verenigde Staten', 'adequacy_decision' => false, 'safeguards' => 'Modelcontract'],
                'automated_decision_making' => ['applies' => true, 'logic' => 'Een rekenregel', 'consequences' => 'Snellere beslissing'],
            ]]]));

    expect($markdown)
        ->toContain(__('snapshot.public_details'))
        ->toContain('Uitleg voor de bewoner')
        ->toContain('Vergunning beoordelen')
        ->toContain(__('avg_goal_legal_base.options')[4])
        ->toContain('Bewoners')
        ->toContain(__('stakeholder.health'))
        ->toContain('Verenigde Staten')
        ->toContain('Modelcontract')
        ->toContain('Een rekenregel')
        ->toContain('Snellere beslissing');
});

it('leaves out the detail lines when a transfer or decision does not apply', function (): void {
    $markdown = app(SnapshotPublicDetailsMarkdown::class)->render(snapshotWithFrontmatter([
        'record' => [
            'details' => [
                'summary' => null,
                'goals' => [],
                'stakeholders' => [],
                'has_processors' => false,
                'outside_eu' => null,
                'automated_decision_making' => null,
            ]]]));

    expect($markdown)
        ->not->toContain(__('avg_responsible_processing_record.outside_eu_description'))
        ->not->toContain(__('avg_responsible_processing_record.logic'));
});
