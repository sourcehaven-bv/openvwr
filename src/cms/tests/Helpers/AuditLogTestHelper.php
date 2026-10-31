<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Services\AuditLog\AuditLogger;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

use function app;
use function array_filter;
use function array_map;
use function array_values;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * Captures what the AuditLogger writes to the audit channel.
 */
class AuditLogTestHelper
{
    private readonly TestHandler $handler;

    private function __construct()
    {
        $this->handler = new TestHandler();
    }

    public static function capture(): self
    {
        $helper = new self();

        app()->when(AuditLogger::class)
            ->needs(LoggerInterface::class)
            ->give(static fn (): LoggerInterface => new Logger('audit', [$helper->handler]));

        return $helper;
    }

    /**
     * @return list<string>
     */
    public function lines(): array
    {
        return array_map(
            static fn ($record): string => $record->message,
            $this->handler->getRecords(),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function records(?string $eventCode = null): array
    {
        $records = array_map(
            static fn (string $line): array => json_decode($line, true, 512, JSON_THROW_ON_ERROR),
            $this->lines(),
        );

        return array_values(array_filter(
            $records,
            static fn (array $record): bool => $eventCode === null || $record['event_code'] === $eventCode,
        ));
    }
}
