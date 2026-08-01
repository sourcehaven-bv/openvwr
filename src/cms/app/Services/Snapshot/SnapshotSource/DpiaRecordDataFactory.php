<?php

declare(strict_types=1);

namespace App\Services\Snapshot\SnapshotSource;

use App\Models\Snapshot;
use App\Services\Snapshot\SnapshotSourceDataFactory;

/**
 * Renders a DPIA into the markdown a snapshot stores.
 *
 * Only private markdown: a DPIA is not published to the static website, so
 * there is nothing public to generate. The snapshot exists to freeze a version
 * that can be vastgesteld and compared, not to publish it.
 */
class DpiaRecordDataFactory extends DataFactory implements SnapshotSourceDataFactory
{
    public function generatePrivateMarkdown(Snapshot $snapshot): string
    {
        return $this->render('snapshot-data-create.dpia-record.private-markdown', $snapshot);
    }
}
