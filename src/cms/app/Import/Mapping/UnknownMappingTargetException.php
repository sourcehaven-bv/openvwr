<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use InvalidArgumentException;

use function sprintf;

/**
 * The browser sent a mapping target the server never offered. The screen only
 * lists allowed targets, so this is tampering rather than a user mistake.
 */
class UnknownMappingTargetException extends InvalidArgumentException
{
    public function __construct(string $target)
    {
        parent::__construct(sprintf('mapping target "%s" is not offered for this register', $target));
    }
}
