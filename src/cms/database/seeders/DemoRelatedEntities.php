<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\ContactPerson;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\System;
use App\Models\Tag;

/**
 * Entities shared between the demo records: created once per organisation and
 * attached to the records that reference them, so the relation tables on a
 * detail page show real cross-references rather than one-offs.
 *
 * The list-shaped fields are indexed by position, because DemoContent refers
 * to them by index (a record naming systems [0, 5] means the first and sixth
 * entry of DemoContent::SYSTEMS).
 */
final class DemoRelatedEntities
{
    /**
     * @param array<string, AvgResponsibleProcessingRecordService> $services keyed by department name
     * @param list<Tag> $tags
     * @param list<System> $systems
     * @param list<Processor> $processors
     * @param list<Receiver> $receivers
     * @param list<ContactPerson> $contactPersons
     */
    public function __construct(
        public array $services,
        public array $tags,
        public array $systems,
        public array $processors,
        public array $receivers,
        public Responsible $responsible,
        public array $contactPersons,
    ) {
    }
}
