<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Widgets\FgRemarksWidget;
use App\Models\Contracts\EntityNumerable;
use App\Models\Contracts\SnapshotSource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Webmozart\Assert\Assert;

use function sprintf;

class ProcessingRecordViewRecord extends ViewRecord
{
    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
            DeleteAction::make(),
        ];
    }

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
