<?php

declare(strict_types=1);

use App\Enums\Snapshot\SnapshotDataSection;

return [
    // for backwards compatability, use the full classname (e.g. App\Models\Responsible) instead of references (e.g. Responsible::class)
    'render-templates' => [
        SnapshotDataSection::PRIVATE->value => [
            'App\Models\ContactPerson' => 'snapshot-data-render.contact-person.private-markdown-list',
            'App\Models\Processor' => 'snapshot-data-render.processor.private-markdown-list',
            'App\Models\Receiver' => 'snapshot-data-render.receiver.private-markdown-list',
            'App\Models\Responsible' => 'snapshot-data-render.responsible.private-markdown-list',
            'App\Models\System' => 'snapshot-data-render.system.private-markdown-list',
            'App\Models\Algorithm\AlgorithmRecord' => 'snapshot-data-render.algorithm-record.private-markdown-list',
        ],
        SnapshotDataSection::PUBLIC->value => [
            'App\Models\ContactPerson' => 'snapshot-data-render.contact-person.private-markdown-list',
            'App\Models\Processor' => 'snapshot-data-render.processor.private-markdown-list',
            'App\Models\Receiver' => 'snapshot-data-render.receiver.public-markdown-list',
            'App\Models\Responsible' => 'snapshot-data-render.responsible.public-markdown-list',
            'App\Models\System' => 'snapshot-data-render.system.private-markdown-list',
        ],
    ],
];
