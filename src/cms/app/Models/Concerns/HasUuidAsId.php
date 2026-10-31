<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Components\Uuid\Uuid;
use App\Components\Uuid\UuidInterface;
use App\Models\Casts\UuidCast;
use Webmozart\Assert\Assert;

/**
 * @property UuidInterface $id
 */
trait HasUuidAsId
{
    final public function initializeHasUuidAsId(): void
    {
        $this->usesUniqueIds = true;

        $this->mergeCasts([
            'id' => UuidCast::class,
        ]);
    }

    final public function getKey(): UuidInterface
    {
        $key = $this->getAttribute($this->getKeyName());
        Assert::isInstanceOf($key, UuidInterface::class);

        return $key;
    }

    final public function getRouteKey(): string
    {
        // Route parameters, including Filament's Livewire re-authorisation,
        // must be scalar even though the model exposes a UUID value object.
        return $this->getKey()->toString();
    }

    final public function getQueueableId(): string
    {
        return $this->getKey()->toString();
    }

    /**
     * The Eloquent-Model uses a HasUniqueIds-trait, which forces to return a string here...
     *
     * @return UuidInterface
     */
    // @phpstan-ignore method.childReturnType
    final public function newUniqueId() // phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
    {
        return Uuid::generate();
    }

    /**
     * @return array<string>
     */
    final public function uniqueIds(): array
    {
        return ['id'];
    }

    final public function getKeyType(): string
    {
        return 'string';
    }

    final public function getIncrementing(): false
    {
        return false;
    }
}
