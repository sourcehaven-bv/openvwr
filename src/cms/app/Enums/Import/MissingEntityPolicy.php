<?php

declare(strict_types=1);

namespace App\Enums\Import;

/**
 * What to do when a source names a shared entity the register does not hold.
 */
enum MissingEntityPolicy
{
    /**
     * Create it. Suppliers and systems are meant to grow with the data.
     */
    case Create;

    /**
     * Report it and leave the link unmade. Registers that are maintained
     * deliberately, such as processing records, must not silently gain empty
     * entries because a data breach mentioned an unknown name.
     */
    case Report;
}
