<?php

declare(strict_types=1);

namespace App\Services\AuditLog;

use Illuminate\Contracts\Auth\Guard;
use MinVWS\AuditLogger\Contracts\LoggableUser;
use MinVWS\AuditLogger\Events\Logging\GeneralLogEvent;
use Psr\Log\LoggerInterface;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * Writes audit events as one JSON object per line to the audit channel.
 *
 * Only the minimum is recorded: what happened, whether it failed, the ids of
 * the acting and affected users, and the event's own data (record and
 * organisation ids, counts). No email addresses, names or request contents.
 */
class AuditLogger
{
    public function __construct(
        private readonly AuditLogEventFactory $auditLogEventFactory,
        private readonly LoggerInterface $auditChannel,
        private readonly Guard $guard,
    ) {
    }

    public function register(AuditEvent $auditEvent): void
    {
        $logEvent = $this->auditLogEventFactory->fromApplicationEvent($auditEvent, $this->currentUser());

        $this->auditChannel->info(json_encode(self::record($logEvent), JSON_THROW_ON_ERROR));
    }

    /**
     * @return array<string, mixed>
     */
    private static function record(GeneralLogEvent $logEvent): array
    {
        $data = $logEvent->getLogData();

        return [
            'created_at' => $data['created_at']->toIso8601String(),
            'event_code' => $data['event_code'],
            'action_code' => $data['action_code'],
            'failed' => $data['failed'],
            'failed_reason' => $data['failed_reason'],
            'user_id' => $data['user_id'],
            'target_id' => $logEvent->getTargetUser()?->getAuditId(),
            'data' => $data['request'],
        ];
    }

    private function currentUser(): ?LoggableUser
    {
        $user = $this->guard->user();

        return $user instanceof LoggableUser ? $user : null;
    }
}
