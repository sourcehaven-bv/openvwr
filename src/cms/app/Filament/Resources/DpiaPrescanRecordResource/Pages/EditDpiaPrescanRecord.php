<?php

declare(strict_types=1);

namespace App\Filament\Resources\DpiaPrescanRecordResource\Pages;

use App\Enums\Dpia\PrescanOutcome;
use App\Filament\Actions\StartDpiaFromPrescanAction;
use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Resources\DpiaPrescanRecordResource;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Services\Dpia\PrescanEvaluator;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

use function app;

class EditDpiaPrescanRecord extends EditRecord
{
    protected static string $resource = DpiaPrescanRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
            // Only offered when the pre-scan actually points at a DPIA, so the
            // button cannot be used to route around the outcome.
            StartDpiaFromPrescanAction::make()
                ->visible(function (): bool {
                    $record = $this->getRecord();

                    // Narrowing for static analysis: this page only ever holds
                    // a DpiaPrescanRecord, so this cannot happen at runtime.
                    // @codeCoverageIgnoreStart
                    if (!$record instanceof DpiaPrescanRecord) {
                        return false;
                    }
                    // @codeCoverageIgnoreEnd

                    return app(PrescanEvaluator::class)->dpiaOutcome($record) !== PrescanOutcome::NOT_REQUIRED;
                }),
            DeleteAction::make(),
        ];
    }
}
