<?php

declare(strict_types=1);

namespace App\Services\Snapshot\SnapshotSource;

use App\Enums\SitemapType;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Snapshot;
use App\Services\Snapshot\SnapshotSourceDataFactory;
use Webmozart\Assert\Assert;

class AvgResponsibleProcessingRecordDataFactory extends DataFactory implements SnapshotSourceDataFactory
{
    public function generatePrivateMarkdown(Snapshot $snapshot): string
    {
        return $this->render('snapshot-data-create.avg-responsible-processing-record.private-markdown', $snapshot);
    }

    /**
     * @return array<string, mixed>
     */
    public function generatePublicFrontmatter(Snapshot $snapshot): array
    {
        $record = $snapshot->snapshotSource;
        Assert::isInstanceOf($record, AvgResponsibleProcessingRecord::class);

        $description = $record->avgGoals->map(static function (AvgGoal $avgGoal) {
            return $avgGoal->goal;
        })->join(', ');

        return [
            'title' => $record->name,
            'type' => SitemapType::PROCESSING_RECORD->value,
            'record' => [
                'reference' => $record->getNumber(),
                'title' => $record->name,
                'description' => $description,
                'details' => (new AvgResponsibleProcessingRecordPublicDetails())->forRecord($record),
            ],
        ];
    }

    public function generatePublicMarkdown(Snapshot $snapshot): ?string
    {
        return $this->render('snapshot-data-create.avg-responsible-processing-record.public-markdown', $snapshot);
    }
}
