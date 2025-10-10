<?php

declare(strict_types=1);

namespace App\Services\StaticWebsite;

use App\Events\StaticWebsite\AfterBuildEvent;
use App\Facades\AdminLog;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Support\Facades\Process;
use Psr\Log\LoggerInterface;

use function dirname;
use function escapeshellarg;
use function file_exists;
use function is_executable;
use function sprintf;

class HugoStaticWebsiteGenerator implements StaticWebsiteGenerator
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly LoggerInterface $logger,
        private readonly string $baseUrl,
        private readonly string $hugoContentFolder,
        private readonly string $buildScriptPath,
    ) {
    }

    /**
     * @throws BuildException
     */
    public function generate(): void
    {
        $sourcePath = $this->filesystem->path($this->hugoContentFolder);

        AdminLog::log('Generating static website files', [
            'baseUrl' => $this->baseUrl,
            'buildScriptPath' => $this->buildScriptPath,
            'sourcePath' => $sourcePath,
        ]);

        try {
            $this->generateStaticWebsite($sourcePath);
        } catch (ProcessFailedException $processFailedException) {
            $message = sprintf('build process failed: %s', $processFailedException->getMessage());
            $this->logger->error($message, ['output' => $processFailedException->result->output()]);

            throw new BuildException($message, $processFailedException->getCode(), $processFailedException);
        }

        AfterBuildEvent::dispatch();
    }

    private function generateStaticWebsite(string $sourcePath): void
    {
        // Validate build script exists and is executable
        if (!file_exists($this->buildScriptPath)) {
            throw new BuildException(sprintf('Build script not found: %s', $this->buildScriptPath));
        }

        if (!is_executable($this->buildScriptPath)) {
            throw new BuildException(sprintf('Build script is not executable: %s', $this->buildScriptPath));
        }

        // Build command with properly escaped arguments
        // Note: destination path and theme are determined by the build script itself
        $command = sprintf(
            '%s %s %s',
            escapeshellarg($this->buildScriptPath),
            escapeshellarg($sourcePath),
            escapeshellarg($this->baseUrl),
        );

        $this->logger->debug('Executing build script', [
            'buildScript' => $this->buildScriptPath,
            'command' => $command,
        ]);

        $result = Process::path(dirname($this->buildScriptPath))
            ->run($command)
            ->throw();

        $this->logger->debug('Build script executed successfully', [
            'output' => $result->output(),
        ]);
    }
}
