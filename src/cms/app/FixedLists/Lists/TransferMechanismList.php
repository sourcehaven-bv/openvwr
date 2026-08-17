<?php

declare(strict_types=1);

namespace App\FixedLists\Lists;

use App\FixedLists\FixedList;
use App\FixedLists\FixedListEntry;

/**
 * Mechanisms that legitimise a transfer of personal data outside the EER (hoofdstuk V AVG).
 *
 * Unlike the country list these values are stable keys, with the label kept in the translations.
 */
class TransferMechanismList extends FixedList
{
    protected function entries(): array
    {
        return [
            FixedListEntry::current('adequaatheidsbesluit'),
            FixedListEntry::current('scc'),
            FixedListEntry::current('bcr'),
            FixedListEntry::current('overig'),
        ];
    }
}
