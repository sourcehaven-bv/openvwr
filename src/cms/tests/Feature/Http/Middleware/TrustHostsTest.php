<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\TrustHosts;
use Tests\Helpers\ConfigTestHelper;

use function expect;
use function it;

it('allows access when the tenant is not set', function (): void {
    /** @var TrustHosts $trustHosts */
    $trustHosts = $this->app->get(TrustHosts::class);

    ConfigTestHelper::set('app.url', 'http://localhost');

    expect($trustHosts->hosts())
        ->toBe(['^(.+\.)?localhost$']);
});
