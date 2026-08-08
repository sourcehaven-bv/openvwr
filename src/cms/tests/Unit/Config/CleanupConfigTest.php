<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use App\Config\Config;
use Tests\TestCase;

use function it;
use function uses;

uses(TestCase::class);

it('defaults to a retention period of ninety days', function (): void {
    $this->assertSame(90, Config::integer('cleanup.retention_days'));
});

it('defaults to a batch size that limits a single run', function (): void {
    $this->assertGreaterThan(0, Config::integer('cleanup.batch_size'));
});
