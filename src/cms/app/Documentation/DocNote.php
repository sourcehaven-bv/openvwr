<?php

declare(strict_types=1);

namespace App\Documentation;

use Attribute;

/**
 * An explanation of a form section, meant for the generated documentation
 * (`php artisan docs:datamodel`).
 *
 * This is a different kind of text than the InformationBlockSection in the form
 * itself: that one tells someone filling in the form how to answer, while this
 * note tells an outside reader what the system can record at this point. The
 * two are independent and have their own audience.
 *
 * The note sits on the schema method so it travels in the same change as the
 * fields it describes. It holds a translation key, not the text itself, so the
 * documentation can be generated in any locale the application supports:
 *
 *     #[DocNote('documentation.avg_responsible.stakeholders')]
 *     public static function getStakeholder(): array
 *
 * Has no effect on the application; only the documentation generator reads
 * these attributes.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class DocNote
{
    public function __construct(
        public string $key,
    ) {
    }
}
