<?php

declare(strict_types=1);

use App\Services\StaticWebsite\HugoStaticWebsiteGenerator;
use App\Services\StaticWebsite\StaticWebsiteCheckService;

it('reports a missing base url as a config error when resolving the generator', function (): void {
    config()->set('static-website.base_url', null);

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('static-website.base_url');

    app()->make(HugoStaticWebsiteGenerator::class);
});

it('reports a missing check base url as a config error when resolving the check service', function (): void {
    config()->set('static-website.check_base_url', null);

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('static-website.check_base_url');

    app()->make(StaticWebsiteCheckService::class);
});

it('resolves the generator when the base url is configured', function (): void {
    config()->set('static-website.base_url', 'https://example.test');

    expect(app()->make(HugoStaticWebsiteGenerator::class))
        ->toBeInstanceOf(HugoStaticWebsiteGenerator::class);
});
