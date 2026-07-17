<?php

declare(strict_types=1);

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Storage;
use Tests\Helpers\ConfigTestHelper;

it('can run the artisan sql-execute command', function (): void {
    $this->mock(DatabaseManager::class)
        ->shouldReceive('unprepared')
        ->times(169);

    $this->artisan('sql-execute')
        ->assertSuccessful();
});

it('can run the artisan sql-execute command with a version', function (): void {
    $version = fake()->slug(1);

    $storage = Storage::fake();
    Storage::shouldReceive('disk')
        ->with(ConfigTestHelper::get('sql-generator.filesystem_disk'))
        ->andReturn($storage);
    Storage::shouldReceive('allFiles')
        ->with($version)
        ->andReturn([]);

    $this->mock(DatabaseManager::class)
        ->shouldReceive('unprepared')
        ->times(0);

    $this->artisan(sprintf('sql-execute %s', $version))
        ->assertSuccessful();
});
