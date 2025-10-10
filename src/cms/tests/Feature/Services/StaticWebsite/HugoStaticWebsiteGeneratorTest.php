<?php

declare(strict_types=1);

use App\Events\StaticWebsite\AfterBuildEvent;
use App\Facades\AdminLog;
use App\Services\StaticWebsite\BuildException;
use App\Services\StaticWebsite\HugoStaticWebsiteGenerator;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command;
use Tests\Helpers\ConfigTestHelper;

it('can run the build script', function (): void {
    $filesystemDiskStaticWebsiteRoot = fake()->slug(1);
    $sourceFolder = fake()->slug(1);
    $baseUrl = fake()->url();
    $buildScriptPath = base_path('static-website/build.sh');

    ConfigTestHelper::set('filesystems.disks.static-website.root', $filesystemDiskStaticWebsiteRoot);
    ConfigTestHelper::set('static-website.hugo_content_folder', $sourceFolder);
    ConfigTestHelper::set('static-website.hugo_filesystem_disk', 'static-website');
    ConfigTestHelper::set('static-website.base_url', $baseUrl);
    ConfigTestHelper::set('static-website.build_script_path', $buildScriptPath);

    $process = Process::fake();

    $hugoWebsiteGenerator = $this->app->get(HugoStaticWebsiteGenerator::class);
    $hugoWebsiteGenerator->generate();
    $process->assertRan(
        static function (PendingProcess $pendingProcess) use (
            $sourceFolder,
            $baseUrl,
            $buildScriptPath,
        ): bool {
            $expectedCommand = sprintf(
                '%s %s %s',
                escapeshellarg($buildScriptPath),
                escapeshellarg(Storage::disk('static-website')->path($sourceFolder)),
                escapeshellarg($baseUrl),
            );
            return $pendingProcess->command === $expectedCommand;
        },
    );
});

it('will throw a buildException when process fails', function (): void {
    Process::fake([
        '*' => Process::result(fake()->sentence(), fake()->sentence(), Command::FAILURE),
    ]);

    /** @var HugoStaticWebsiteGenerator $hugoWebsiteGenerator */
    $hugoWebsiteGenerator = $this->app->get(HugoStaticWebsiteGenerator::class);

    $hugoWebsiteGenerator->generate();
})->throws(BuildException::class);

it('throws BuildException when build script does not exist', function (): void {
    $nonExistentScript = '/path/to/nonexistent/script.sh';

    ConfigTestHelper::set('static-website.build_script_path', $nonExistentScript);

    /** @var HugoStaticWebsiteGenerator $hugoWebsiteGenerator */
    $hugoWebsiteGenerator = $this->app->get(HugoStaticWebsiteGenerator::class);

    $hugoWebsiteGenerator->generate();
})->throws(BuildException::class, 'Build script not found');

it('throws BuildException when build script is not executable', function (): void {
    // Create a temporary non-executable file
    $tempScript = tempnam(sys_get_temp_dir(), 'test_script_');
    file_put_contents($tempScript, '#!/bin/bash');
    chmod($tempScript, 0644); // Make it not executable

    ConfigTestHelper::set('static-website.build_script_path', $tempScript);

    try {
        /** @var HugoStaticWebsiteGenerator $hugoWebsiteGenerator */
        $hugoWebsiteGenerator = $this->app->get(HugoStaticWebsiteGenerator::class);
        $hugoWebsiteGenerator->generate();
    } finally {
        unlink($tempScript);
    }
})->throws(BuildException::class, 'Build script is not executable');

it('logs admin log and debug messages during build', function (): void {
    $filesystemDiskStaticWebsiteRoot = fake()->slug(1);
    $sourceFolder = fake()->slug(1);
    $baseUrl = fake()->url();
    $buildScriptPath = base_path('static-website/build.sh');

    ConfigTestHelper::set('filesystems.disks.static-website.root', $filesystemDiskStaticWebsiteRoot);
    ConfigTestHelper::set('static-website.hugo_content_folder', $sourceFolder);
    ConfigTestHelper::set('static-website.hugo_filesystem_disk', 'static-website');
    ConfigTestHelper::set('static-website.base_url', $baseUrl);
    ConfigTestHelper::set('static-website.build_script_path', $buildScriptPath);

    Process::fake();
    AdminLog::shouldReceive('log')
        ->once()
        ->with('Generating static website files', Mockery::type('array'));

    Log::shouldReceive('debug')->atLeast()->zeroOrMoreTimes();

    /** @var HugoStaticWebsiteGenerator $hugoWebsiteGenerator */
    $hugoWebsiteGenerator = $this->app->get(HugoStaticWebsiteGenerator::class);
    $hugoWebsiteGenerator->generate();
});

it('dispatches AfterBuildEvent after successful build', function (): void {
    Event::fake();
    Process::fake();

    /** @var HugoStaticWebsiteGenerator $hugoWebsiteGenerator */
    $hugoWebsiteGenerator = $this->app->get(HugoStaticWebsiteGenerator::class);
    $hugoWebsiteGenerator->generate();

    Event::assertDispatched(AfterBuildEvent::class);
});

it('logs error when build process fails', function (): void {
    $errorOutput = 'Build failed with errors';
    Process::fake([
        '*' => Process::result('Some output', $errorOutput, Command::FAILURE),
    ]);

    Log::shouldReceive('error')
        ->atLeast()
        ->once()
        ->withArgs(function ($message, $context) {
            return str_contains($message, 'build process failed') &&
                    isset($context['output']);
        });

    Log::shouldReceive('debug')->atLeast()->zeroOrMoreTimes();

    /** @var HugoStaticWebsiteGenerator $hugoWebsiteGenerator */
    $hugoWebsiteGenerator = $this->app->get(HugoStaticWebsiteGenerator::class);

    try {
        $hugoWebsiteGenerator->generate();
    } catch (BuildException $e) {
        // Expected exception
        expect($e->getMessage())->toContain('build process failed');
    }
});
