<?php

declare(strict_types=1);

namespace App\Services\Snapshot;

use App\Models\Snapshot;

interface SnapshotSourceDataFactory
{
    public function generatePrivateMarkdown(Snapshot $snapshot): ?string;

    /**
     * @return array<string, mixed>
     */
    public function generatePublicFrontmatter(Snapshot $snapshot): array;

    public function generatePublicMarkdown(Snapshot $snapshot): ?string;
}
