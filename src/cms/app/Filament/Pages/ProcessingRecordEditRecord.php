<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\SavesConceptWithoutRequiredFields;
use App\Filament\Widgets\FgRemarksWidget;
use App\Models\Contracts\EntityNumerable;
use App\Models\Contracts\SnapshotSource;
use Filament\Resources\Pages\EditRecord;
use Webmozart\Assert\Assert;

use function sprintf;

class ProcessingRecordEditRecord extends EditRecord
{
    use SavesConceptWithoutRequiredFields;

    protected function getHeaderWidgets(): array
    {
        return [
            FgRemarksWidget::class,
        ];
    }

    public function getTitle(): string
    {
        $record = $this->getRecord();
        Assert::implementsInterface($record, EntityNumerable::class);
        Assert::implementsInterface($record, SnapshotSource::class);

        return sprintf('%s (%s)', $record->getDisplayName(), $record->getNumber());
    }
}
