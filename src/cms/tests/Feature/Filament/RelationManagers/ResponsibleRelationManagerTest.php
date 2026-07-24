<?php

declare(strict_types=1);

use App\Filament\RelationManagers\ResponsibleRelationManager;
use App\Filament\Resources\DataBreachRecord\Pages\EditDataBreachRecord;
use App\Models\DataBreachRecord;
use App\Models\Responsible;

it('loads the table', function (): void {
    $responsible = Responsible::factory()
        ->create();
    $dataBreachRecord = DataBreachRecord::factory()
        ->hasAttached($responsible)
        ->create();

    $this->asFilamentUser()
        ->createLivewireTestable(ResponsibleRelationManager::class, [
            'ownerRecord' => $dataBreachRecord,
            'pageClass' => EditDataBreachRecord::class,
        ])
        ->assertCanSeeTableRecords([$responsible]);
});
