<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use App\Config\Feature;
use App\Models\Contracts\Publishable;
use App\Models\Snapshot;
use Filament\Infolists\Components\TextEntry;

use function __;

class SnapshotUrlEntry extends TextEntry
{
    public static function make(?string $name = 'snapshotSource.public_from'): static
    {
        return parent::make($name)
            ->label(__('snapshot.url'))
            ->visible(static function (Snapshot $snapshot): bool {
                if (!Feature::publishingEnabled()) {
                    return false;
                }

                $snapshotSource = $snapshot->snapshotSource;
                if ($snapshotSource instanceof Publishable) {
                    return $snapshotSource->isPublished();
                }

                return false;
            })
            ->view('filament.infolists.components.entries.snapshot-url-entry');
    }
}
