<?php

declare(strict_types=1);

namespace App\FixedLists\Audit;

/**
 * The three ways stored data and a fixed list can disagree. They are kept apart because each one asks a
 * different person for a different fix.
 */
enum FixedListFindingType: string
{
    /**
     * Records hold a value that the list still knows, but that has been retired. The value was valid when it
     * was chosen, so this is not a data error: it is a signal that the underlying basis lapsed and the
     * records need to be reassessed.
     */
    case RETIRED = 'retired';

    /**
     * Records hold a value the list has never contained. Typically an import artefact or a value that was
     * edited in the list instead of being retired.
     */
    case UNKNOWN = 'unknown';

    /**
     * The list contains a value that no record uses. Harmless, but it shows what can be dropped.
     */
    case UNUSED = 'unused';
}
