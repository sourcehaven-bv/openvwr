<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Models\LookupListModel;

/**
 * A lookup list a source column can fill, such as the service a processing
 * record belongs to.
 *
 * These behave like relations but resolve to a single record referenced by a
 * foreign key, so the value is set on the record itself rather than attached.
 */
readonly class LookupTarget
{
    /**
     * @param class-string<LookupListModel> $modelClass
     * @param string $foreignKey column on the importing record
     */
    public function __construct(
        public string $key,
        public string $modelClass,
        public string $foreignKey,
        public string $labelKey,
    ) {
    }
}
