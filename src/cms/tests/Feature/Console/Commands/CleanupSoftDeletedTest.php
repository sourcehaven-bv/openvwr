<?php

declare(strict_types=1);

use App\Models\ContactPerson;
use App\Models\Document;
use App\Models\Organisation;
use App\Models\Remark;
use App\Models\Tag;
use App\Models\User;
use Carbon\CarbonImmutable;

it('permanently deletes records that are past the retention period', function (): void {
    config()->set('cleanup.retention_days', 90);

    $user = User::factory()->create([
        'deleted_at' => CarbonImmutable::now()->subDays(91),
    ]);

    $this->artisan('cleanup:soft-deleted')
        ->assertSuccessful();

    expect(User::withTrashed()->find($user->id))->toBeNull();
});

it('keeps records that are still within the retention period', function (): void {
    config()->set('cleanup.retention_days', 90);

    $user = User::factory()->create([
        'deleted_at' => CarbonImmutable::now()->subDays(89),
    ]);

    $this->artisan('cleanup:soft-deleted')
        ->assertSuccessful();

    expect(User::withTrashed()->find($user->id))->not->toBeNull();
});

it('keeps records that are not deleted at all', function (): void {
    $user = User::factory()->create();

    $this->artisan('cleanup:soft-deleted')
        ->assertSuccessful();

    expect(User::withTrashed()->find($user->id))->not->toBeNull();
});

// De grens zelf: precies op de bewaartermijn blijft staan, één seconde erover
// gaat weg. Zonder deze twee is een `<` / `<=`-verwisseling onzichtbaar.
it('keeps a record exactly on the retention boundary', function (): void {
    config()->set('cleanup.retention_days', 90);

    $user = User::factory()->create([
        'deleted_at' => CarbonImmutable::now()->subDays(90),
    ]);

    $this->artisan('cleanup:soft-deleted')
        ->assertSuccessful();

    expect(User::withTrashed()->find($user->id))->not->toBeNull();
});

it('deletes a record one second past the retention boundary', function (): void {
    config()->set('cleanup.retention_days', 90);

    $user = User::factory()->create([
        'deleted_at' => CarbonImmutable::now()->subDays(90)->subSecond(),
    ]);

    $this->artisan('cleanup:soft-deleted')
        ->assertSuccessful();

    expect(User::withTrashed()->find($user->id))->toBeNull();
});

it('respects a configured retention period other than the default', function (): void {
    config()->set('cleanup.retention_days', 7);

    $user = User::factory()->create([
        'deleted_at' => CarbonImmutable::now()->subDays(8),
    ]);

    $this->artisan('cleanup:soft-deleted')
        ->assertSuccessful();

    expect(User::withTrashed()->find($user->id))->toBeNull();
});

// remarks.user_id is een foreign key zónder ON DELETE CASCADE. Wordt de
// gebruiker vóór de opmerking verwijderd, dan faalt de query op een
// constraint-violation. Deze test bewaakt die volgorde.
it('deletes remarks before the user they point at', function (): void {
    config()->set('cleanup.retention_days', 90);

    $deletedAt = CarbonImmutable::now()->subDays(120);

    $user = User::factory()->create(['deleted_at' => $deletedAt]);
    $remark = Remark::factory()->create([
        'user_id' => $user->id,
        'deleted_at' => $deletedAt,
    ]);

    $this->artisan('cleanup:soft-deleted')
        ->assertSuccessful();

    expect(Remark::withTrashed()->find($remark->id))->toBeNull()
        ->and(User::withTrashed()->find($user->id))->toBeNull();
});

it('cleans up several related models in one run', function (): void {
    config()->set('cleanup.retention_days', 90);

    $deletedAt = CarbonImmutable::now()->subDays(200);
    $organisation = Organisation::factory()->create();

    $tag = Tag::factory()->for($organisation)->create(['deleted_at' => $deletedAt]);
    $contactPerson = ContactPerson::factory()->for($organisation)->create(['deleted_at' => $deletedAt]);
    $document = Document::factory()->for($organisation)->create(['deleted_at' => $deletedAt]);

    $this->artisan('cleanup:soft-deleted')
        ->assertSuccessful();

    expect(Tag::withTrashed()->find($tag->id))->toBeNull()
        ->and(ContactPerson::withTrashed()->find($contactPerson->id))->toBeNull()
        ->and(Document::withTrashed()->find($document->id))->toBeNull();
});

it('reports how many records were deleted', function (): void {
    config()->set('cleanup.retention_days', 90);

    User::factory()->create(['deleted_at' => CarbonImmutable::now()->subDays(120)]);

    $this->artisan('cleanup:soft-deleted')
        ->expectsOutputToContain('1 records permanently deleted.')
        ->assertSuccessful();
});

it('succeeds when there is nothing to clean up', function (): void {
    $this->artisan('cleanup:soft-deleted')
        ->expectsOutputToContain('0 records permanently deleted.')
        ->assertSuccessful();
});
