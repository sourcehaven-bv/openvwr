<?php

declare(strict_types=1);

namespace Tests\Helpers\Dpia;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;

use function is_string;

/**
 * A stand-in for Filament's Get that reads straight from an array.
 *
 * Lets the live-status logic be tested on its own, without building a whole
 * form just to hand it a few answers.
 */
class ArrayGet extends Get
{
    /**
     * @param array<string, mixed> $state
     */
    public function __construct(private readonly array $state)
    {
    }

    public function __invoke(string|Component $path = '', bool $isAbsolute = false): mixed
    {
        return is_string($path) ? ($this->state[$path] ?? null) : null;
    }
}
