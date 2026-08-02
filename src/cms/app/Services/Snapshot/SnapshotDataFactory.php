<?php

declare(strict_types=1);

namespace App\Services\Snapshot;

use App\Models;
use App\Models\Snapshot;
use App\Models\SnapshotData;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class SnapshotDataFactory
{
    public function createDataForSnapshot(Snapshot $snapshot): SnapshotData
    {
        $snapshotDataFactory = $this->getSnapshotDataFactory($snapshot);

        return SnapshotData::create([
            'snapshot_id' => $snapshot->id,
            'private_markdown' => $snapshotDataFactory->generatePrivateMarkdown($snapshot),
            'public_frontmatter' => $snapshotDataFactory->generatePublicFrontmatter($snapshot),
            'public_markdown' => $snapshotDataFactory->generatePublicMarkdown($snapshot),
        ]);
    }

    private function getSnapshotDataFactory(Snapshot $snapshot): SnapshotSourceDataFactory
    {
        $snapshotSource = $snapshot->snapshotSource;
        Assert::isInstanceOf($snapshotSource, Model::class);
        $className = $snapshotSource::class;

        $snapshotSourceDataFactory = match ($className) {
            Models\Algorithm\AlgorithmRecord::class => SnapshotSource\AlgorithmRecordDataFactory::class,
            Models\Avg\AvgProcessorProcessingRecord::class => SnapshotSource\AvgProcessorProcessingRecordDataFactory::class,
            Models\Avg\AvgResponsibleProcessingRecord::class => SnapshotSource\AvgResponsibleProcessingRecordDataFactory::class,
            Models\Dpia\DpiaRecord::class => SnapshotSource\DpiaRecordDataFactory::class,
            Models\Wpg\WpgProcessingRecord::class => SnapshotSource\WpgProcessingRecordDataFactory::class,

            Models\ContactPerson::class => SnapshotSource\ContactPersonDataFactory::class,
            Models\Processor::class => SnapshotSource\ProcessorDataFactory::class,
            Models\Receiver::class => SnapshotSource\ReceiverDataFactory::class,
            Models\Responsible::class => SnapshotSource\ResponsibleDataFactory::class,
            Models\System::class => SnapshotSource\SystemDataFactory::class,

            default => throw new InvalidArgumentException('missing snapshot-data factory for model'),
        };

        return new $snapshotSourceDataFactory();
    }
}
