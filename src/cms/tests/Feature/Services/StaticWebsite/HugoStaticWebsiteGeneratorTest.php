<?php

declare(strict_types=1);

use App\Services\StaticWebsite\BuildException;
use App\Services\StaticWebsite\HugoStaticWebsiteGenerator;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command;
use Tests\Helpers\ConfigTestHelper;

it('can run the build script', function (): void {
    $filesystemDiskStaticWebsiteRoot = fake()->slug(1);
    $sourceFolder = fake()->slug(1);
    $baseUrl = fake()->url();
    $buildScriptPath = base_path('static-website/build.sh');
    $theme = 'rijkshuisstijl';

    ConfigTestHelper::set('filesystems.disks.static-website.root', $filesystemDiskStaticWebsiteRoot);
    ConfigTestHelper::set('static-website.hugo_content_folder', $sourceFolder);
    ConfigTestHelper::set('static-website.hugo_filesystem_disk', 'static-website');
    ConfigTestHelper::set('static-website.base_url', $baseUrl);
    ConfigTestHelper::set('static-website.build_script_path', $buildScriptPath);
    ConfigTestHelper::set('static-website.theme', $theme);

    $process = Process::fake();

    $hugoWebsiteGenerator = $this->app->get(HugoStaticWebsiteGenerator::class);
    $hugoWebsiteGenerator->generate();
    $process->assertRan(
        static function (PendingProcess $pendingProcess) use (
            $sourceFolder,
            $baseUrl,
            $buildScriptPath,
            $theme,
        ): bool {
            $expectedCommand = sprintf(
                '%s %s %s %s',
                escapeshellarg($buildScriptPath),
                escapeshellarg(Storage::disk('static-website')->path($sourceFolder)),
                escapeshellarg($baseUrl),
                escapeshellarg($theme)
            );
            return $pendingProcess->command === $expectedCommand;
        },
    );
});

it('will throw a buildException', function (): void {
    Process::fake([
        '*' => Process::result(fake()->sentence(), fake()->sentence(), Command::FAILURE),
    ]);

    /** @var HugoStaticWebsiteGenerator $hugoWebsiteGenerator */
    $hugoWebsiteGenerator = $this->app->get(HugoStaticWebsiteGenerator::class);

    $hugoWebsiteGenerator->generate();
})->throws(BuildException::class);

it('will run the build-after-hook', function (): void {
    $buildAfterHook = fake()->word();
    ConfigTestHelper::set('static-website.build_after_hook', $buildAfterHook);

    $process = Process::fake();

    /** @var HugoStaticWebsiteGenerator $hugoWebsiteGenerator */
    $hugoWebsiteGenerator = $this->app->get(HugoStaticWebsiteGenerator::class);
    $hugoWebsiteGenerator->generate();

    $process->assertRan($buildAfterHook);
});

it('can handle a failed build-after-hook', function (): void {
    $buildAfterHook = fake()->word();
    ConfigTestHelper::set('static-website.build_after_hook', $buildAfterHook);

    $process = Process::fake([
        'hugo *' => Process::result(fake()->sentence(), fake()->sentence(), Command::SUCCESS),
        $buildAfterHook => Process::result(fake()->sentence(), fake()->sentence(), Command::FAILURE),
    ]);

    /** @var HugoStaticWebsiteGenerator $hugoWebsiteGenerator */
    $hugoWebsiteGenerator = $this->app->get(HugoStaticWebsiteGenerator::class);
    $hugoWebsiteGenerator->generate();

    $process->assertRan($buildAfterHook);
});
