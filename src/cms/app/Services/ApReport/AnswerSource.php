<?php

declare(strict_types=1);

namespace App\Services\ApReport;

/**
 * Where an answer in the AP preparation came from. The distinction matters: a
 * notification to the AP is a legal statement about *this* breach, while a
 * derived answer only describes a processing record the breach is linked to.
 * Those may differ (a leak of the addresses in a health-related processing does
 * not itself involve health data), so derived answers are offered as a
 * suggestion for the officer to confirm, never as an established fact.
 */
enum AnswerSource: string
{
    /** Recorded on the data breach record itself. */
    case RECORDED = 'recorded';

    /** Inferred from linked content; needs confirmation before it is filed. */
    case DERIVED = 'derived';

    /** Not held anywhere in the register; the officer supplies it. */
    case MISSING = 'missing';
}
