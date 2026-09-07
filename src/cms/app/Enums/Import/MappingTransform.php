<?php

declare(strict_types=1);

namespace App\Enums\Import;

use function __;
use function sprintf;

/**
 * The closed set of conversions a mapping may apply.
 *
 * Deliberately not an expression language: every entry maps onto a converter
 * that already exists in DataConverters, so a profile can never express
 * something the import pipeline cannot execute.
 */
enum MappingTransform: string
{
    case Text = 'text';
    case Date = 'date';
    case Boolean = 'boolean';
    case Integer = 'integer';
    case StringList = 'string_list';

    /**
     * A yes/no source column feeding a date field: "yes" becomes a chosen date,
     * "no" stays empty. Sources often record only *that* something was reported,
     * while the register also wants to know *when*.
     */
    case BooleanToDate = 'boolean_to_date';

    public function label(): string
    {
        return __(sprintf('import_mapping.transform.%s', $this->value));
    }
}
