<?php

declare(strict_types=1);

namespace Tests\Doubles\FixedLists;

use App\FixedLists\FixedListEntry;
use App\FixedLists\Lists\AdequacyDecisionCountryList;

/**
 * Stands in for the real country list, so tests do not break when an actual adequacy decision is granted
 * or withdrawn.
 */
class CountryListWithRetiredEntry extends AdequacyDecisionCountryList
{
    public const RETIRED_VALUE = 'Verenigde Staten';
    public const RETIRED_REASON = 'adequacy decision withdrawn';

    protected function entries(): array
    {
        return [
            FixedListEntry::current('Japan'),
            FixedListEntry::retired(self::RETIRED_VALUE, self::RETIRED_REASON),
        ];
    }
}
