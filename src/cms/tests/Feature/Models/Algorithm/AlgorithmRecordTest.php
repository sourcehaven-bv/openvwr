<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Avg;

use App\Models\Algorithm\AlgorithmPublicationCategory;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Algorithm\AlgorithmStatus;
use App\Models\Algorithm\AlgorithmTheme;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Wpg\WpgProcessingRecord;

use function expect;
use function it;

it('can have algorithm themes', function (): void {
    $algorithmRecord = AlgorithmRecord::factory()->create();

    expect($algorithmRecord->algorithmTheme)->toBeInstanceOf(AlgorithmTheme::class)
        ->and($algorithmRecord->algorithmStatus)->toBeInstanceOf(AlgorithmStatus::class)
        ->and($algorithmRecord->algorithmPublicationCategory)->toBeInstanceOf(AlgorithmPublicationCategory::class);
});

it('links to the processings of every register in both directions', function (): void {
    $algorithmRecord = AlgorithmRecord::factory()->create();

    $avgResponsible = AvgResponsibleProcessingRecord::factory()->create(['has_algorithms' => true]);
    $avgProcessor = AvgProcessorProcessingRecord::factory()->create(['has_algorithms' => true]);
    $wpg = WpgProcessingRecord::factory()->create(['has_algorithms' => true]);

    $avgResponsible->algorithmRecords()->attach($algorithmRecord);
    $avgProcessor->algorithmRecords()->attach($algorithmRecord);
    $wpg->algorithmRecords()->attach($algorithmRecord);

    expect($avgResponsible->refresh()->algorithmRecords)->toHaveCount(1)
        ->and($avgProcessor->refresh()->algorithmRecords)->toHaveCount(1)
        ->and($wpg->refresh()->algorithmRecords)->toHaveCount(1)
        ->and($algorithmRecord->avgResponsibleProcessingRecords)->toHaveCount(1)
        ->and($algorithmRecord->avgProcessorProcessingRecords)->toHaveCount(1)
        ->and($algorithmRecord->wpgProcessingRecords)->toHaveCount(1);
});

it('keeps one processing linked to several algorithms of the same system', function (): void {
    $avgResponsible = AvgResponsibleProcessingRecord::factory()
        ->withAlgorithmRecords(3)
        ->create(['has_algorithms' => true]);

    expect($avgResponsible->algorithmRecords)->toHaveCount(3);
});
