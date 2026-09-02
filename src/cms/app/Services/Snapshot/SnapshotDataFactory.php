<?php

declare(strict_types=1);

namespace App\Services\Snapshot;

use App\Models\Algorithm\AlgorithmRecord;
use App\Services\Snapshot\SnapshotSource\AlgorithmRecordDataFactory;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Services\Snapshot\SnapshotSource\AvgProcessorProcessingRecordDataFactory;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Services\Snapshot\SnapshotSource\AvgResponsibleProcessingRecordDataFactory;
use App\Models\Dpia\DpiaRecord;
use App\Services\Snapshot\SnapshotSource\DpiaRecordDataFactory;
use App\Models\Wpg\WpgProcessingRecord;
use App\Services\Snapshot\SnapshotSource\WpgProcessingRecordDataFactory;
use App\Models\ContactPerson;
use App\Services\Snapshot\SnapshotSource\ContactPersonDataFactory;
use App\Models\Processor;
use App\Services\Snapshot\SnapshotSource\ProcessorDataFactory;
use App\Models\Receiver;
use App\Services\Snapshot\SnapshotSource\ReceiverDataFactory;
use App\Models\Responsible;
use App\Services\Snapshot\SnapshotSource\ResponsibleDataFactory;
use App\Models\System;
use App\Services\Snapshot\SnapshotSource\SystemDataFactory;
use App\Config\Feature;
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

        // Without publishing a snapshot only has a private part. Keeping the
        // rule here rather than in the individual templates puts it in one
        // place and makes the null explicit: a null public_markdown is what
        // OrganisationPublishableRecordsService already reads as "not
        // publishable", so an empty rendered string would not do.
        $publishingEnabled = Feature::publishingEnabled();

        return SnapshotData::create([
            'snapshot_id' => $snapshot->id,
            'private_markdown' => $snapshotDataFactory->generatePrivateMarkdown($snapshot),
            'public_frontmatter' => $publishingEnabled ? $snapshotDataFactory->generatePublicFrontmatter($snapshot) : [],
            'public_markdown' => $publishingEnabled ? $snapshotDataFactory->generatePublicMarkdown($snapshot) : null,
        ]);
    }

    private function getSnapshotDataFactory(Snapshot $snapshot): SnapshotSourceDataFactory
    {
        $snapshotSource = $snapshot->snapshotSource;
        Assert::isInstanceOf($snapshotSource, Model::class);
        $className = $snapshotSource::class;

        $snapshotSourceDataFactory = match ($className) {
            AlgorithmRecord::class => AlgorithmRecordDataFactory::class,
            AvgProcessorProcessingRecord::class => AvgProcessorProcessingRecordDataFactory::class,
            AvgResponsibleProcessingRecord::class => AvgResponsibleProcessingRecordDataFactory::class,
            DpiaRecord::class => DpiaRecordDataFactory::class,
            WpgProcessingRecord::class => WpgProcessingRecordDataFactory::class,

            ContactPerson::class => ContactPersonDataFactory::class,
            Processor::class => ProcessorDataFactory::class,
            Receiver::class => ReceiverDataFactory::class,
            Responsible::class => ResponsibleDataFactory::class,
            System::class => SystemDataFactory::class,

            default => throw new InvalidArgumentException('missing snapshot-data factory for model'),
        };

        return new $snapshotSourceDataFactory();
    }
}
