<?php

declare(strict_types=1);

use App\Models\Snapshot;
use App\Services\Snapshot\SnapshotSource\DataFactory;

it('returns empty defaults for a source that generates no data', function (): void {
    $factory = new class extends DataFactory {
    };

    $snapshot = Snapshot::factory()->create();

    expect($factory->generatePrivateMarkdown($snapshot))->toBeNull()
        ->and($factory->generatePublicMarkdown($snapshot))->toBeNull()
        ->and($factory->generatePublicFrontmatter($snapshot))->toBe([]);
});
