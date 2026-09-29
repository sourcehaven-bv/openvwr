<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use function explode;
use function sprintf;
use function str_contains;
use function str_starts_with;

/**
 * The strings a mapping uses to point at something other than a plain column:
 * a shared entity ("processors"), one of its attributes ("processors::email"),
 * a sub-record attribute ("processors::address.city"), a lookup list
 * ("lookup:service") or a note on the record ("remarks").
 */
final class RelationKey
{
    /**
     * Separates a relation key from the attribute it fills.
     */
    public const ATTRIBUTE_SEPARATOR = '::';

    /**
     * Marks a target as a lookup list rather than a column.
     */
    public const LOOKUP_PREFIX = 'lookup:';

    /**
     * The target that keeps a column as a note on the record, for text that
     * has no field of its own. Any number of columns may go there.
     */
    public const REMARKS = 'remarks';

    /**
     * The one note the FG keeps on a record; a single value, unlike REMARKS.
     */
    public const FG_REMARK = 'fgRemark';

    /**
     * Notes joined in one cell are separated by a blank line: a note may
     * itself hold a comma or a line break.
     */
    public const NOTE_SEPARATOR = "\n\n";

    public static function attribute(string $relation, string $attribute): string
    {
        return sprintf('%s%s%s', $relation, self::ATTRIBUTE_SEPARATOR, $attribute);
    }

    public static function lookup(string $key): string
    {
        return sprintf('%s%s', self::LOOKUP_PREFIX, $key);
    }

    public static function isLookup(string $target): bool
    {
        return str_starts_with($target, self::LOOKUP_PREFIX);
    }

    public static function isRemarks(string $target): bool
    {
        return $target === self::REMARKS;
    }

    /**
     * Either kind of note.
     */
    public static function isNote(string $target): bool
    {
        return $target === self::REMARKS || $target === self::FG_REMARK;
    }

    /**
     * Splits "processors::email" into its relation key and attribute; a plain
     * relation key yields a null attribute.
     *
     * @return array{0: string, 1: ?string}
     */
    public static function split(string $target): array
    {
        if (!str_contains($target, self::ATTRIBUTE_SEPARATOR)) {
            return [$target, null];
        }

        [$key, $attribute] = explode(self::ATTRIBUTE_SEPARATOR, $target, 2);

        return [$key, $attribute];
    }
}
