<?php

declare(strict_types=1);

namespace App\Providers;

use App\Config\Config;
use App\Services\StaticWebsite\HugoFilesystem;
use App\Services\StaticWebsite\HugoStaticWebsiteGenerator;
use App\Services\StaticWebsite\StaticWebsiteCheckService;
use App\Services\StaticWebsite\StaticWebsiteFilesystem;
use App\Services\StaticWebsite\StaticWebsiteFilesystemManager;
use App\Services\StaticWebsite\StaticWebsiteGenerator;
use App\Services\StaticWebsite\StaticWebsiteGeneratorManager;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Webmozart\Assert\Assert;

class StaticWebsiteServiceProvider extends ServiceProvider
{
    private const STATIC_WEBSITE_FILESYSTEM = 'static_website_filesystem';

    public function register(): void
    {
        $this->app->bind(self::STATIC_WEBSITE_FILESYSTEM, static function (): Filesystem {
            return Storage::disk(Config::string('static-website.hugo_filesystem_disk'));
        });

        $this->app->bind(StaticWebsiteFilesystem::class, static function (Application $application): StaticWebsiteFilesystem {
            /** @var StaticWebsiteFilesystemManager $staticWebsiteFilesystemManager */
            $staticWebsiteFilesystemManager = $application->get(StaticWebsiteFilesystemManager::class);
            $staticWebsiteFilesystem = $staticWebsiteFilesystemManager->driver(Config::string('static-website.filesystem'));
            Assert::isInstanceOf($staticWebsiteFilesystem, StaticWebsiteFilesystem::class);

            return $staticWebsiteFilesystem;
        });
        $this->app->bind(StaticWebsiteGenerator::class, static function (Application $application): StaticWebsiteGenerator {
            /** @var StaticWebsiteGeneratorManager $staticWebsiteGeneratorManager */
            $staticWebsiteGeneratorManager = $application->get(StaticWebsiteGeneratorManager::class);
            $staticWebsiteGenerator = $staticWebsiteGeneratorManager->driver(Config::string('static-website.static_website_generator'));
            Assert::isInstanceOf($staticWebsiteGenerator, StaticWebsiteGenerator::class);

            return $staticWebsiteGenerator;
        });
        $this->app->when(HugoFilesystem::class)
            ->needs(Filesystem::class)
            ->give(self::STATIC_WEBSITE_FILESYSTEM);
        $this->app->when(HugoFilesystem::class)
            ->needs('$hugoContentFolder')
            ->giveConfig('static-website.hugo_content_folder');

        $this->app->when(HugoStaticWebsiteGenerator::class)
            ->needs(Filesystem::class)
            ->give(self::STATIC_WEBSITE_FILESYSTEM);
        $this->app->when(HugoStaticWebsiteGenerator::class)
            ->needs('$baseUrl')
            ->giveConfig('static-website.base_url');
        $this->app->when(HugoStaticWebsiteGenerator::class)
            ->needs('$hugoContentFolder')
            ->giveConfig('static-website.hugo_content_folder');
        $this->app->when(HugoStaticWebsiteGenerator::class)
            ->needs('$buildScriptPath')
            ->giveConfig('static-website.build_script_path');

        $this->app->when([StaticWebsiteCheckService::class])
            ->needs('$baseUrl')
            ->giveConfig('static-website.check_base_url');
        $this->app->when([StaticWebsiteCheckService::class])
            ->needs('$proxy')
            ->giveConfig('static-website.check_proxy');
    }
}
