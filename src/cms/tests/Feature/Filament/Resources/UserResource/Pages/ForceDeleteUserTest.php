<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Carbon\CarbonImmutable;
use Livewire\Features\SupportTesting\Testable;

/**
 * Verwijderde records zijn pas zichtbaar zodra het TrashedFilter op "verwijderd"
 * staat -- precies wat een beheerder in de interface doet voordat de actie in
 * beeld komt.
 */
function listTrashedUsers(Testable $component): Testable
{
    return $component->filterTable('trashed', '0');
}

it('permanently deletes a trashed user from the list', function (): void {
    $user = User::factory()->create([
        'deleted_at' => CarbonImmutable::now(),
    ]);

    listTrashedUsers($this->asFilamentUser()->createLivewireTestable(ListUsers::class))
        ->callTableAction('force_delete', $user);

    expect(User::withTrashed()->find($user->id))->toBeNull();
});

// De actie slaat de bewaartermijn bewust over: een betrokkene die om
// verwijdering vraagt (art. 17 AVG) hoeft niet 90 dagen te wachten.
it('ignores the retention period', function (): void {
    config()->set('cleanup.retention_days', 90);

    $user = User::factory()->create([
        'deleted_at' => CarbonImmutable::now(),
    ]);

    listTrashedUsers($this->asFilamentUser()->createLivewireTestable(ListUsers::class))
        ->callTableAction('force_delete', $user);

    expect(User::withTrashed()->find($user->id))->toBeNull();
});

it('is hidden for a user that is not deleted', function (): void {
    $user = User::factory()->create();

    $this->asFilamentUser()
        ->createLivewireTestable(ListUsers::class)
        ->assertTableActionHidden('force_delete', $user);
});

it('is visible for a deleted user', function (): void {
    $user = User::factory()->create([
        'deleted_at' => CarbonImmutable::now(),
    ]);

    listTrashedUsers($this->asFilamentUser()->createLivewireTestable(ListUsers::class))
        ->assertTableActionVisible('force_delete', $user);
});

it('does not show trashed users until the filter is applied', function (): void {
    $active = User::factory()->create();
    $trashed = User::factory()->create(['deleted_at' => CarbonImmutable::now()]);

    $this->asFilamentUser()
        ->createLivewireTestable(ListUsers::class)
        ->assertCanSeeTableRecords([$active])
        ->assertCanNotSeeTableRecords([$trashed]);
});
