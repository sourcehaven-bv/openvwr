<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\AuditLog\AuditLogger;
use App\Services\AuditLog\Transfer\TransferExportEvent;
use App\Services\AuditLog\Transfer\TransferExportLogEvent;
use App\Services\AuditLog\User\UserTwoFactorResetEvent;
use App\Services\AuditLog\User\UserUpdatedEvent;
use App\Transfer\TransferEntityType;
use MinVWS\AuditLogger\Events\Logging\AccountChangeLogEvent;
use Tests\Helpers\AuditLogTestHelper;

beforeEach(function (): void {
    $this->auditLog = AuditLogTestHelper::capture();
});

it('writes one JSON line with only the minimal fields', function (): void {
    $admin = User::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($admin);

    app(AuditLogger::class)->register(new UserTwoFactorResetEvent($user));

    $records = $this->auditLog->records(AccountChangeLogEvent::EVENTCODE_RESET);
    expect($records)->toHaveCount(1)
        ->and($records[0])->toHaveKeys(['created_at', 'action_code', 'failed', 'failed_reason'])
        ->and($records[0])->toHaveCount(8)
        ->and($records[0]['data'])->toBe([])
        ->and($records[0]['user_id'])->toBe($admin->id->toString())
        ->and($records[0]['target_id'])->toBe($user->id->toString())
        ->and($this->auditLog->lines()[0])
        ->not->toContain($user->email)
        ->not->toContain($admin->email);
});

it('records the logged-in user as actor of an account change', function (): void {
    $admin = User::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($admin);

    $user->update(['name' => 'Changed Name']);

    $records = $this->auditLog->records((new AccountChangeLogEvent())->getEventCode());
    expect($records)->toHaveCount(1)
        ->and($records[0]['user_id'])->toBe($admin->id->toString())
        ->and($records[0]['target_id'])->toBe($user->id->toString())
        ->and($this->auditLog->lines()[0])->not->toContain('Changed Name');
});

it('records no actor for an account change without a logged-in user', function (): void {
    $user = User::factory()->create();

    app(AuditLogger::class)->register(new UserUpdatedEvent($user));

    $records = $this->auditLog->records((new AccountChangeLogEvent())->getEventCode());
    expect($records)->toHaveCount(1)
        ->and($records[0]['user_id'])->toBeNull()
        ->and($records[0]['target_id'])->toBe($user->id->toString());
});

it('records the event data of a transfer', function (): void {
    $user = User::factory()->create();
    $event = new TransferExportEvent($user, 'org-1', TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD, ['rec-1'], []);

    app(AuditLogger::class)->register($event);

    $records = $this->auditLog->records(TransferExportLogEvent::EVENT_CODE);
    expect($records)->toHaveCount(1)
        ->and($records[0]['user_id'])->toBe($user->id->toString())
        ->and($records[0]['data'])->toBe([
            'source_organisation_id' => 'org-1',
            'record_type' => TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->value,
            'record_ids' => ['rec-1'],
            'related_ids' => [],
        ]);
});
