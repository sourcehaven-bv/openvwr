<?php

declare(strict_types=1);

use App\Components\Uuid\Uuid;
use App\Components\Uuid\UuidInterface;
use App\Models\Concerns\HasUuidAsId;
use App\Models\User;

it('returns a correct value for the key', function (): void {
    $model = new class {
        use HasUuidAsId;

        public function getKey(): UuidInterface
        {
            return Uuid::generate();
        }
    };

    expect($model->getKey())
        ->toBeInstanceOf(UuidInterface::class);
});

it('returns a uuid for newUniqueId', function (): void {
    $model = new class {
        use HasUuidAsId;
    };

    $uuid = $model->newUniqueId();

    expect($uuid)
        ->toBeInstanceOf(UuidInterface::class);
});

it('returns the correct value for uniqueIds', function (): void {
    $model = new class {
        use HasUuidAsId;
    };

    expect($model->uniqueIds())
        ->toBe(['id']);
});

it('returns the correct value for keyType', function (): void {
    $model = new class {
        use HasUuidAsId;
    };

    expect($model->getKeyType())
        ->toBe('string');
});

it('returns the correct value for Incrementing', function (): void {
    $model = new class {
        use HasUuidAsId;
    };

    expect($model->getIncrementing())
        ->toBeFalse();
});

it('returns a correct value for a model-key', function (): void {
    $user = User::factory()->create();

    expect($user->getKey())
        ->toBe($user->id);
});

it('returns a correct value for a model-id', function (): void {
    $user = User::factory()->create();

    expect($user->getAttribute('id'))
        ->toBeInstanceOf(UuidInterface::class);
});

it('returns a correct value for a model-queueable-id', function (): void {
    $user = User::factory()->create();

    expect($user->getQueueableId())
        ->toBeString();
});

it('returns a scalar route key while retaining the UUID model key', function (): void {
    $user = User::factory()->create();

    expect($user->getRouteKey())->toBe($user->id->toString())
        ->and($user->getKey())->toBeInstanceOf(UuidInterface::class);
});
