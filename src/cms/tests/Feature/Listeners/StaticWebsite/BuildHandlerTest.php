<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners\Media;

use App\Events\StaticWebsite\BuildEvent;
use App\Jobs\StaticWebsite\ContentGeneratorJob;
use App\Jobs\StaticWebsite\HugoWebsiteGeneratorJob;
use App\Jobs\StaticWebsite\StaticWebsiteCheckJob;
use Illuminate\Support\Facades\Bus;
use Tests\Helpers\ConfigTestHelper;

use function it;

it('calls the BuildHandler that chains the jobs', function (): void {
    Bus::fake();
    BuildEvent::dispatch();

    Bus::assertChained([
        ContentGeneratorJob::class,
        HugoWebsiteGeneratorJob::class,
        StaticWebsiteCheckJob::class,
    ]);
});

it('does not dispatch build jobs when publishing is disabled', function (): void {
    ConfigTestHelper::set('features.publishing', false);
    Bus::fake();

    BuildEvent::dispatch();

    Bus::assertNothingDispatched();
});
