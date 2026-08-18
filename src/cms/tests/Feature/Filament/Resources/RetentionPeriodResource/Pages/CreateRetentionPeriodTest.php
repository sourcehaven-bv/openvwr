<?php

declare(strict_types=1);

use App\Filament\Resources\RetentionPeriodResource;
use App\Filament\Resources\RetentionPeriodResource\Pages\CreateRetentionPeriod;
use App\Models\RetentionPeriod;

it('loads the create page', function (): void {
    $this->asFilamentUser()
        ->get(RetentionPeriodResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create an entry', function (): void {
    $name = fake()->uuid();

    $this->asFilamentUser()
        ->createLivewireTestable(CreateRetentionPeriod::class)
        ->fillForm([
            'name' => $name,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(RetentionPeriod::class, [
        'name' => $name,
    ]);
});
