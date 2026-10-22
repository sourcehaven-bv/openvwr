<?php

declare(strict_types=1);

namespace App\Services\AuditLog;

use App\Services\AuditLog\Authentication\AuthenticationFailedEvent;
use App\Services\AuditLog\Authentication\AuthenticationSuccessEvent;
use App\Services\AuditLog\Register\LinksDetachedEvent;
use App\Services\AuditLog\Register\LinksDetachedLogEvent;
use App\Services\AuditLog\Transfer\TransferExportEvent;
use App\Services\AuditLog\Transfer\TransferExportLogEvent;
use App\Services\AuditLog\Transfer\TransferImportEvent;
use App\Services\AuditLog\Transfer\TransferImportLogEvent;
use App\Services\AuditLog\User\UserCreatedEvent;
use App\Services\AuditLog\User\UserPermissionUpdatedEvent;
use App\Services\AuditLog\User\UserTwoFactorResetEvent;
use App\Services\AuditLog\User\UserUpdatedEvent;
use InvalidArgumentException;
use MinVWS\AuditLogger\Events\Logging\AccountChangeLogEvent;
use MinVWS\AuditLogger\Events\Logging\GeneralLogEvent;
use MinVWS\AuditLogger\Events\Logging\UserCreatedLogEvent;
use MinVWS\AuditLogger\Events\Logging\UserLoginLogEvent;
use MinVWS\AuditLogger\Loggers\LogEventInterface;

class AuditLogEventFactory
{
    public function fromApplicationEvent(AuditEvent $event): LogEventInterface
    {
        return match ($event::class) {
            AuthenticationFailedEvent::class => $this->fromAuthenticationFailedEvent($event),
            AuthenticationSuccessEvent::class => $this->fromAuthenticationSuccessEvent($event),
            LinksDetachedEvent::class => $this->fromLinksDetachedEvent($event),
            TransferExportEvent::class => $this->fromTransferExportEvent($event),
            TransferImportEvent::class => $this->fromTransferImportEvent($event),
            UserCreatedEvent::class => $this->fromUserCreatedEvent($event),
            UserPermissionUpdatedEvent::class => $this->fromUserPermissionUpdatedEvent($event),
            UserTwoFactorResetEvent::class => $this->fromUserTwoFactorResetEvent($event),
            UserUpdatedEvent::class => $this->fromUserUpdatedEvent($event),
            default => throw new InvalidArgumentException('Unknown audit event'),
        };
    }

    private function fromAuthenticationFailedEvent(AuthenticationFailedEvent $event): GeneralLogEvent
    {
        $code = match ($event->type) {
            AuthenticationFailedEvent::INVALID_TOKEN => '093334',
            AuthenticationFailedEvent::NO_TOKEN_FOUND => '093335',
            AuthenticationFailedEvent::NO_ORGANISATION_FOUND => '093336',
            // @codeCoverageIgnoreStart
            default => throw new InvalidArgumentException('Unknown type'),
            // @codeCoverageIgnoreEnd
        };

        $minvwsAuditLogEvent = new class extends GeneralLogEvent {
        };
        $minvwsAuditLogEvent->withEventCode($code);

        return $minvwsAuditLogEvent;
    }

    private function fromAuthenticationSuccessEvent(AuthenticationSuccessEvent $event): UserLoginLogEvent
    {
        $minvwsAuditLogEvent = new UserLoginLogEvent();
        $minvwsAuditLogEvent->withTarget($event->user);

        return $minvwsAuditLogEvent;
    }

    private function fromLinksDetachedEvent(LinksDetachedEvent $event): LinksDetachedLogEvent
    {
        $minvwsAuditLogEvent = new LinksDetachedLogEvent();
        $minvwsAuditLogEvent->asUpdate();
        $minvwsAuditLogEvent->withData([
            'organisation_id' => $event->organisationId,
            'record_type' => $event->recordType,
            'record_id' => $event->recordId,
            'relation' => $event->relation,
            'related_ids' => $event->relatedIds,
        ]);

        if ($event->user !== null) {
            $minvwsAuditLogEvent->withActor($event->user);
        }

        return $minvwsAuditLogEvent;
    }

    private function fromTransferExportEvent(TransferExportEvent $event): TransferExportLogEvent
    {
        $minvwsAuditLogEvent = new TransferExportLogEvent();
        $minvwsAuditLogEvent->asRead();
        $minvwsAuditLogEvent->withData([
            'source_organisation_id' => $event->sourceOrganisationId,
            'record_type' => $event->recordType->value,
            'record_ids' => $event->recordIds,
            'related_ids' => $event->selectedRelated,
        ]);

        if ($event->user !== null) {
            $minvwsAuditLogEvent->withActor($event->user);
        }

        if ($event->failedReason !== null) {
            $minvwsAuditLogEvent->withFailed(true, $event->failedReason);
        }

        return $minvwsAuditLogEvent;
    }

    private function fromTransferImportEvent(TransferImportEvent $event): TransferImportLogEvent
    {
        $minvwsAuditLogEvent = new TransferImportLogEvent();
        $minvwsAuditLogEvent->asCreate();
        $minvwsAuditLogEvent->withData([
            'method' => $event->method,
            // a bundle's source is only claimed by its unsigned manifest; a copy's is verified
            $event->method === TransferImportEvent::METHOD_BUNDLE
                ? 'claimed_source_organisation_id'
                : 'source_organisation_id' => $event->sourceOrganisationId,
            'destination_organisation_id' => $event->destinationOrganisationId,
            'created' => $event->result?->created,
            'overwritten' => $event->result?->overwritten,
            'skipped' => $event->result?->skipped,
            'records' => $event->result->records ?? [],
        ]);

        if ($event->user !== null) {
            $minvwsAuditLogEvent->withActor($event->user);
        }

        if ($event->failedReason !== null) {
            $minvwsAuditLogEvent->withFailed(true, $event->failedReason);
        }

        return $minvwsAuditLogEvent;
    }

    private function fromUserCreatedEvent(UserCreatedEvent $event): UserCreatedLogEvent
    {
        $minvwsAuditLogEvent = new UserCreatedLogEvent();
        $minvwsAuditLogEvent->withTarget($event->user);

        return $minvwsAuditLogEvent;
    }

    private function fromUserPermissionUpdatedEvent(UserPermissionUpdatedEvent $event): AccountChangeLogEvent
    {
        $minvwsAuditLogEvent = new AccountChangeLogEvent();
        $minvwsAuditLogEvent->withEventCode(AccountChangeLogEvent::EVENTCODE_ROLES);
        $minvwsAuditLogEvent->withTarget($event->user);

        return $minvwsAuditLogEvent;
    }

    private function fromUserTwoFactorResetEvent(UserTwoFactorResetEvent $event): AccountChangeLogEvent
    {
        $minvwsAuditLogEvent = new AccountChangeLogEvent();
        $minvwsAuditLogEvent->withEventCode(AccountChangeLogEvent::EVENTCODE_RESET);
        $minvwsAuditLogEvent->withTarget($event->user);

        return $minvwsAuditLogEvent;
    }

    private function fromUserUpdatedEvent(UserUpdatedEvent $event): AccountChangeLogEvent
    {
        $minvwsAuditLogEvent = new AccountChangeLogEvent();
        $minvwsAuditLogEvent->withTarget($event->user);

        return $minvwsAuditLogEvent;
    }
}
