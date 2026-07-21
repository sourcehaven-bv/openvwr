<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Avg;

use App\Models\Algorithm\AlgorithmPublicationCategory;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Algorithm\AlgorithmStatus;
use App\Models\Algorithm\AlgorithmTheme;

use function expect;
use function it;

it('can have algorithm themes', function (): void {
    $algorithmRecord = AlgorithmRecord::factory()->create();

    expect($algorithmRecord->algorithmTheme)->toBeInstanceOf(AlgorithmTheme::class)
        ->and($algorithmRecord->algorithmStatus)->toBeInstanceOf(AlgorithmStatus::class)
        ->and($algorithmRecord->algorithmPublicationCategory)->toBeInstanceOf(AlgorithmPublicationCategory::class);
});
