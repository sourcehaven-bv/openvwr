<?php

declare(strict_types=1);

namespace App\Filament\Resources\DataBreachRecord\Pages;

use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Pages\EntityNumberCreateRecord;
use App\Filament\Resources\DataBreachRecordResource;
use App\Models\DataBreachRecord;
use App\Services\Notification\DataBreachNotificationService;
use Illuminate\Support\Facades\App;
use Webmozart\Assert\Assert;

class CreateDataBreachRecord extends EntityNumberCreateRecord
{
    protected static string $resource = DataBreachRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
        ];
    }

    protected function afterCreate(): void
    {
        $dataBreachRecord = $this->record;
        Assert::isInstanceOf($dataBreachRecord, DataBreachRecord::class);

        if ($dataBreachRecord->ap_reported === false) {
            return;
        }

        /** @var DataBreachNotificationService $dataBreachNotificationService */
        $dataBreachNotificationService = App::get(DataBreachNotificationService::class);

        $dataBreachNotificationService->sendNotifications($dataBreachRecord);
    }
}
