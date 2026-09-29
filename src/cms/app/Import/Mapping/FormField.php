<?php

declare(strict_types=1);

namespace App\Import\Mapping;

/**
 * One field of a register's form: what it is called in the database and on
 * screen, and the relation it edits when it is a link rather than a value.
 */
final class FormField
{
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly ?string $relation = null,
    ) {
    }
}
