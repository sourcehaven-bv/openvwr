<?php

declare(strict_types=1);

namespace App\Enums\Import;

/**
 * How the analyser arrived at a suggested mapping. Shown to the user so they
 * know which suggestions deserve a second look.
 */
enum MappingConfidence: string
{
    case Exact = 'exact';
    case Label = 'label';
    case Content = 'content';
    case Manual = 'manual';
}
