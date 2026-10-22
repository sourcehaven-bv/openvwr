<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\AuditLog\AuditLogEventFactory;
use App\Services\AuditLog\Register\LinksDetachedEvent;
use App\Services\AuditLog\Register\LinksDetachedLogEvent;
use App\Services\AuditLog\Transfer\TransferExportEvent;
use App\Services\AuditLog\Transfer\TransferExportLogEvent;
use App\Services\AuditLog\Transfer\TransferImportEvent;
use App\Services\AuditLog\Transfer\TransferImportLogEvent;
use App\Services\AuditLog\User\UserCreatedEvent;
use App\Transfer\Import\TransferImportResult;
use App\Transfer\TransferEntityType;
use MinVWS\AuditLogger\Loggers\LogEventInterface;

it('can create an logEvent from an event', function (): void {
    $user = User::factory()->create();

    $auditLogEventFactory = new AuditLogEventFactory();
    $logEvent = $auditLogEventFactory->fromApplicationEvent(new UserCreatedEvent($user));

    expect($logEvent)
        ->toBeInstanceOf(LogEventInterface::class);
});

it('creates a transfer export log event with the exported records', function (): void {
    $user = User::factory()->create();

    $logEvent = (new AuditLogEventFactory())->fromApplicationEvent(new TransferExportEvent(
        $user,
        'org-1',
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        ['record-1'],
        ['processors' => ['processor-1']],
    ));

    expect($logEvent)->toBeInstanceOf(TransferExportLogEvent::class)
        ->and($logEvent->getLogData())->toMatchArray([
            'user_id' => $user->id->toString(),
            'event_code' => TransferExportLogEvent::EVENT_CODE,
            'action_code' => 'R',
            'failed' => false,
            'request' => [
                'source_organisation_id' => 'org-1',
                'record_type' => 'avg_responsible_processing_record',
                'record_ids' => ['record-1'],
                'related_ids' => ['processors' => ['processor-1']],
            ],
        ]);
});

it('creates a transfer import log event with the result', function (): void {
    $user = User::factory()->create();
    $result = new TransferImportResult(created: 1, skipped: 2);
    $result->records = [['type' => 'tag', 'id' => 'tag-1', 'action' => 'created']];

    $logEvent = (new AuditLogEventFactory())->fromApplicationEvent(new TransferImportEvent(
        $user,
        TransferImportEvent::METHOD_COPY,
        'org-2',
        'org-1',
        $result,
    ));

    expect($logEvent)->toBeInstanceOf(TransferImportLogEvent::class)
        ->and($logEvent->getLogData())->toMatchArray([
            'user_id' => $user->id->toString(),
            'event_code' => TransferImportLogEvent::EVENT_CODE,
            'action_code' => 'C',
            'failed' => false,
            'request' => [
                'method' => 'copy',
                'source_organisation_id' => 'org-1',
                'destination_organisation_id' => 'org-2',
                'created' => 1,
                'overwritten' => 0,
                'skipped' => 2,
                'records' => [['type' => 'tag', 'id' => 'tag-1', 'action' => 'created']],
            ],
        ]);
});

it('marks failed transfer events as failed', function (): void {
    $factory = new AuditLogEventFactory();

    $export = $factory->fromApplicationEvent(new TransferExportEvent(
        null,
        'org-1',
        TransferEntityType::TAG,
        [],
        [],
        'boom',
    ));
    $import = $factory->fromApplicationEvent(new TransferImportEvent(
        null,
        TransferImportEvent::METHOD_BUNDLE,
        'org-2',
        null,
        null,
        'bad zip',
    ));

    expect($export->getLogData())->toMatchArray(['user_id' => null, 'failed' => true, 'failed_reason' => 'boom'])
        ->and($import->getLogData())->toMatchArray(['user_id' => null, 'failed' => true, 'failed_reason' => 'bad zip']);
});

it('marks the source organisation of a bundle import as claimed', function (): void {
    $logEvent = (new AuditLogEventFactory())->fromApplicationEvent(new TransferImportEvent(
        null,
        TransferImportEvent::METHOD_BUNDLE,
        'org-2',
        'org-1',
        new TransferImportResult(),
    ));

    expect($logEvent->getLogData()['request'])
        ->toHaveKey('claimed_source_organisation_id', 'org-1')
        ->not->toHaveKey('source_organisation_id');
});

it('creates a links detached log event with the removed links', function (): void {
    $user = User::factory()->create();

    $logEvent = (new AuditLogEventFactory())->fromApplicationEvent(new LinksDetachedEvent(
        $user,
        'org-1',
        'avg_responsible_processing_record',
        'record-1',
        'processors',
        ['processor-1'],
    ));

    expect($logEvent)->toBeInstanceOf(LinksDetachedLogEvent::class)
        ->and($logEvent->getLogData())->toMatchArray([
            'user_id' => $user->id->toString(),
            'event_code' => LinksDetachedLogEvent::EVENT_CODE,
            'action_code' => 'U',
            'request' => [
                'organisation_id' => 'org-1',
                'record_type' => 'avg_responsible_processing_record',
                'record_id' => 'record-1',
                'relation' => 'processors',
                'related_ids' => ['processor-1'],
            ],
        ]);
});

it('creates a links detached log event without an actor', function (): void {
    $logEvent = (new AuditLogEventFactory())->fromApplicationEvent(new LinksDetachedEvent(
        null,
        'org-1',
        'wpg_processing_record',
        'record-1',
        'systems',
        ['system-1'],
    ));

    expect($logEvent->getActor())->toBeNull();
});
