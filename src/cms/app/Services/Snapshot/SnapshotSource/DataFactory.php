<?php

declare(strict_types=1);

namespace App\Services\Snapshot\SnapshotSource;

use App\Models\Snapshot;
use Illuminate\Support\Facades\View;

abstract class DataFactory
{
    public function generatePrivateMarkdown(Snapshot $snapshot): ?string
    {
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function generatePublicFrontmatter(Snapshot $snapshot): array
    {
        return [];
    }

    public function generatePublicMarkdown(Snapshot $snapshot): ?string
    {
        return null;
    }

    protected function render(string $view, Snapshot $snapshot): string
    {
        return View::make($view, [
            'record' => $snapshot->snapshotSource,
            'snapshot' => $snapshot,
        ])->render();
    }
}
