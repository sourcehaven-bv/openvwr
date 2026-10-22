<?php

declare(strict_types=1);

use App\Filament\Resources\AdminLogEntryResource;
use App\Filament\Resources\AdminLogEntryResource\Pages\ListAdminLogEntries;
use App\Models\AdminLogEntry;
use Illuminate\Database\Eloquent\Factories\Sequence;

it('loads the list page', function (): void {
    $adminLogEntries = AdminLogEntry::factory()
        ->count(5)
        ->create();

    $this->asFilamentUser()
        ->createLivewireTestable(ListAdminLogEntries::class)
        ->assertCanSeeTableRecords($adminLogEntries);
});

it('can handle non-string values in the context', function (): void {
    $adminLogEntries = AdminLogEntry::factory()
        ->count(5)
        ->state(new Sequence(
            ['context' => [fake()->numberBetween(0, 5)]],
            ['context' => [fake()->boolean()]],
            ['context' => [[fake()->word()]]],
            ['context' => [[fake()->word() => [fake()->word() => fake()->word()]]]],
            ['context' => [new stdClass()]],
        ))
        ->create();

    $this->asFilamentUser()
        ->createLivewireTestable(ListAdminLogEntries::class)
        ->assertCanSeeTableRecords($adminLogEntries);
});


it('uses the Dutch audit record label', function (): void {
    expect(AdminLogEntryResource::getModelLabel())->toBe('Admin Logregel');
});
