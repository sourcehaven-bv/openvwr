<?php

declare(strict_types=1);

namespace App\FixedLists\Lists;

use App\FixedLists\FixedList;
use App\FixedLists\FixedListEntry;

/**
 * Countries outside the EER that the European Commission has granted an adequacy decision (art. 45 AVG).
 *
 * Personal data may be transferred to these countries without additional safeguards. The list follows the
 * Commission, so it changes when an adequacy decision is granted or withdrawn: add or retire an entry here
 * and deploy. Existing records keep their value; retiring only stops the value from being selected anew.
 *
 * Values are the Dutch labels, because that is what is stored in the country columns of the AVG processing
 * records. Never edit an existing value: that orphans the records holding it.
 */
class AdequacyDecisionCountryList extends FixedList
{
    protected function entries(): array
    {
        return [
            FixedListEntry::current('Andorra'),
            FixedListEntry::current('Argentinië'),
            FixedListEntry::current('Canada (alleen commerciële bedrijven)'),
            FixedListEntry::current('Faeröer Eilanden'),
            FixedListEntry::current('Guernsey'),
            FixedListEntry::current('Isle of Man'),
            FixedListEntry::current('Israël'),
            FixedListEntry::current('Japan'),
            FixedListEntry::current('Jersey'),
            FixedListEntry::current('Nieuw-Zeeland'),
            FixedListEntry::current('Uruguay'),
            FixedListEntry::current('Verenigd Koninkrijk'),
            FixedListEntry::current('Verenigde Staten (organisaties die meedoen aan het Data Privacy Framework)'),
            FixedListEntry::current('Zwitserland'),
            FixedListEntry::current('Zuid-Korea'),
        ];
    }
}
