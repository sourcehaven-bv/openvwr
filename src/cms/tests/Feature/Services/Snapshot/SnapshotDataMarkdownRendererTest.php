<?php

declare(strict_types=1);

use App\Enums\Snapshot\SnapshotDataSection;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\Receiver;
use App\Models\RelatedSnapshotSource;
use App\Models\Snapshot;
use App\Models\SnapshotData;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Services\Snapshot\SnapshotDataMarkdownRenderer;

it('can render', function (SnapshotDataSection $snapshotDataSection): void {
    $organisation = Organisation::factory()
        ->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create();

    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->recycle($organisation)
        ->create();
    $snapshotData = SnapshotData::factory()
        ->for($snapshot)
        ->create([
            'public_markdown' => 'Nam quae temporibus et molestiae voluptatibus enim.',
        ]);

    /** @var SnapshotDataMarkdownRenderer $snapshotDataMarkdownRenderer */
    $snapshotDataMarkdownRenderer = $this->app->get(SnapshotDataMarkdownRenderer::class);

    $markdown = $snapshotDataMarkdownRenderer->fromSnapshotMarkdown($snapshot, $snapshotData->public_markdown, $snapshotDataSection);

    expect($markdown)->toMatchSnapshot();
})->with(SnapshotDataSection::cases());

it('can render even if no public_markdown', function (SnapshotDataSection $snapshotDataSection): void {
    $organisation = Organisation::factory()
        ->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create();

    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->recycle($organisation)
        ->create();
    $snapshotData = SnapshotData::factory()
        ->for($snapshot)
        ->create([
            'public_markdown' => null,
        ]);

    /** @var SnapshotDataMarkdownRenderer $snapshotDataMarkdownRenderer */
    $snapshotDataMarkdownRenderer = $this->app->get(SnapshotDataMarkdownRenderer::class);

    $markdown = $snapshotDataMarkdownRenderer->fromSnapshotMarkdown($snapshot, $snapshotData->public_markdown, $snapshotDataSection);

    expect($markdown)->toMatchSnapshot();
})->with(SnapshotDataSection::cases());

it('can render with related records', function (SnapshotDataSection $snapshotDataSection): void {
    $organisation = Organisation::factory()
        ->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create();

    $avgResponsibleProcessingRecordSnapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->recycle($organisation)
        ->create();
    $avgResponsibleProcessingRecordSnapshotData = SnapshotData::factory()
        ->for($avgResponsibleProcessingRecordSnapshot)
        ->create([
            'public_markdown' => 'Nam quae temporibus et molestiae voluptatibus enim.<!--- #App\Models\Receiver# --->',
        ]);

    $receiver = Receiver::factory()
        ->hasAttached($avgResponsibleProcessingRecord)
        ->create();
    $receiverSnapshot = Snapshot::factory()
        ->for($receiver, 'snapshotSource')
        ->recycle($organisation)
        ->create([
            'state' => Established::class,
        ]);
    SnapshotData::factory()
        ->for($receiverSnapshot)
        ->create([
            'public_markdown' => 'Doloribus eum qui fugit hic voluptas aliquam id.',
            'private_markdown' => 'Cupiditate iure voluptatem aspernatur.',
        ]);

    RelatedSnapshotSource::factory()
        ->create([
            'snapshot_id' => $avgResponsibleProcessingRecordSnapshot->id,
            'snapshot_source_id' => $receiver->id,
            'snapshot_source_type' => $receiver::class,
        ]);

    /** @var SnapshotDataMarkdownRenderer $snapshotDataMarkdownRenderer */
    $snapshotDataMarkdownRenderer = $this->app->get(SnapshotDataMarkdownRenderer::class);

    $markdown = $snapshotDataMarkdownRenderer->fromSnapshotMarkdown(
        $avgResponsibleProcessingRecordSnapshot,
        $avgResponsibleProcessingRecordSnapshotData->public_markdown,
        $snapshotDataSection,
    );

    expect($markdown)->toMatchSnapshot();
})->with(SnapshotDataSection::cases());

it('can render but without related records if snapshot not yet established', function (SnapshotDataSection $snapshotDataSection): void {
    $organisation = Organisation::factory()
        ->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create();

    $avgResponsibleProcessingRecordSnapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->recycle($organisation)
        ->create();
    $avgResponsibleProcessingRecordSnapshotData = SnapshotData::factory()
        ->for($avgResponsibleProcessingRecordSnapshot)
        ->create([
            'public_markdown' => 'Nam quae temporibus et molestiae voluptatibus enim.<!--- #App\Models\Receiver# --->',
        ]);

    $receiver = Receiver::factory()
        ->hasAttached($avgResponsibleProcessingRecord)
        ->create();
    $receiverSnapshot = Snapshot::factory()
        ->for($receiver, 'snapshotSource')
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);
    SnapshotData::factory()
        ->for($receiverSnapshot)
        ->create([
            'public_markdown' => 'Doloribus eum qui fugit hic voluptas aliquam id.',
        ]);

    RelatedSnapshotSource::factory()
        ->create([
            'snapshot_id' => $avgResponsibleProcessingRecordSnapshot->id,
            'snapshot_source_id' => $receiver->id,
            'snapshot_source_type' => $receiver::class,
        ]);

    /** @var SnapshotDataMarkdownRenderer $snapshotDataMarkdownRenderer */
    $snapshotDataMarkdownRenderer = $this->app->get(SnapshotDataMarkdownRenderer::class);

    $markdown = $snapshotDataMarkdownRenderer->fromSnapshotMarkdown(
        $avgResponsibleProcessingRecordSnapshot,
        $avgResponsibleProcessingRecordSnapshotData->public_markdown,
        $snapshotDataSection,
    );

    expect($markdown)->toMatchSnapshot();
})->with(SnapshotDataSection::cases());

it('skips related records whose source has been hard-deleted', function (): void {
    $organisation = Organisation::factory()
        ->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create();

    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->recycle($organisation)
        ->create();
    $snapshotData = SnapshotData::factory()
        ->for($snapshot)
        ->create([
            'public_markdown' => 'Voorwoord.<!--- #App\Models\Receiver# --->',
        ]);

    // The polymorphic target has no foreign key, so hard-deleting the source
    // leaves an orphan row. Rendering must skip it instead of dereferencing null.
    $receiver = Receiver::factory()
        ->hasAttached($avgResponsibleProcessingRecord)
        ->create();
    RelatedSnapshotSource::factory()
        ->create([
            'snapshot_id' => $snapshot->id,
            'snapshot_source_id' => $receiver->id,
            'snapshot_source_type' => $receiver::class,
        ]);
    Receiver::query()->whereKey($receiver->id)->forceDelete();

    /** @var SnapshotDataMarkdownRenderer $snapshotDataMarkdownRenderer */
    $snapshotDataMarkdownRenderer = $this->app->get(SnapshotDataMarkdownRenderer::class);

    $markdown = $snapshotDataMarkdownRenderer->fromSnapshotMarkdown(
        $snapshot->fresh(),
        $snapshotData->public_markdown,
        SnapshotDataSection::PUBLIC,
    );

    expect($markdown)->toContain('Voorwoord.');
});
