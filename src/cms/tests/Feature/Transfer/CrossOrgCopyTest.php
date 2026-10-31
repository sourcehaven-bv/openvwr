<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Enums\Authorization\Role;
use App\Enums\Media\MediaGroup;
use App\Facades\AuditLog;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Document;
use App\Models\Organisation;
use App\Models\System;
use App\Models\Tag;
use App\Models\User;
use App\Services\AuditLog\Transfer\TransferImportEvent;
use App\Services\CrossOrgAuthorization;
use App\Transfer\CrossOrgCopier;
use App\Transfer\Export\BundleBuilder;
use App\Transfer\Import\PreviewBuilder;
use App\Transfer\TransferEntityType;
use App\Transfer\TransferException;
use Illuminate\Support\Facades\Storage;
use Tests\Helpers\ConfigTestHelper;

it('copies a record and its related graph into another organisation', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableUser($source, $destination);
    [$record, $processor] = seedCopyableRecord($source);

    $selectedRelated = [
        'processors' => [$processor->id->toString()],
        'systems' => System::query()->pluck('id')->map->toString()->all(),
        'tags' => Tag::query()->pluck('id')->map->toString()->all(),
        'avgGoals' => AvgGoal::query()->pluck('id')->map->toString()->all(),
    ];

    // Build the plan the way the copy page does: from the bundle preview.
    $bundle = app(BundleBuilder::class)->build(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        $selectedRelated,
        $source,
    );
    $preview = app(PreviewBuilder::class)->build($bundle, $destination);
    $plan = [];
    foreach ($preview as $id => $item) {
        $plan[$id] = ['selected' => true, 'strategy' => $item['strategy'] ?? null];
    }

    $result = app(CrossOrgCopier::class)->copy(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        $selectedRelated,
        $plan,
        $source,
        $destination,
        $user,
    );

    expect($result->created)->toBeGreaterThan(0);

    $copied = AvgResponsibleProcessingRecord::query()
        ->whereBelongsTo($destination)
        ->where('name', 'Bijstandsuitkeringen')
        ->firstOrFail();

    expect($copied->getAttribute('origin_id'))->toBe($record->id->toString())
        ->and($copied->getAttribute('organisation_id')->toString())->toBe($destination->id->toString())
        ->and($copied->getAttribute('last_synced_at'))->not->toBeNull()
        ->and($copied->processors()->count())->toBe(1)
        ->and($copied->systems()->count())->toBe(1)
        ->and($copied->tags()->count())->toBe(1)
        ->and($copied->avgGoals()->count())->toBe(1);

    // dependencies re-homed to the destination organisation, not shared across orgs
    expect($copied->processors()->first()->getAttribute('organisation_id')->toString())
        ->toBe($destination->id->toString());
});

it('rejects a copy into an organisation where the user lacks import rights', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    // user belongs to both but has no import-capable role in the destination
    $user = User::factory()->hasAttached(collect([$source, $destination]))->create();
    $user->organisationRoles()->create(['organisation_id' => $source->id, 'role' => Role::CHIEF_PRIVACY_OFFICER]);
    $user->organisationRoles()->create(['organisation_id' => $destination->id, 'role' => Role::MANDATE_HOLDER]);
    [$record] = seedCopyableRecord($source);

    app(CrossOrgCopier::class)->copy(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        [],
        [$record->id->toString() => ['selected' => true, 'strategy' => null]],
        $source,
        $destination,
        $user,
    );
})->throws(TransferException::class);

it('rejects a copy when the user may not export from the source organisation', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    // Import rights in the destination, but only a view-level role in the source (no export).
    $user = User::factory()->hasAttached(collect([$source, $destination]))->create();
    $user->organisationRoles()->create(['organisation_id' => $source->id, 'role' => Role::MANDATE_HOLDER]);
    $user->organisationRoles()->create(['organisation_id' => $destination->id, 'role' => Role::CHIEF_PRIVACY_OFFICER]);
    [$record] = seedCopyableRecord($source);

    app(CrossOrgCopier::class)->copy(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        [],
        [$record->id->toString() => ['selected' => true, 'strategy' => null]],
        $source,
        $destination,
        $user,
    );
})->throws(TransferException::class);

it('records an audit event for a successful copy', function (): void {
    AuditLog::spy();

    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableUser($source, $destination);
    [$record] = seedCopyableRecord($source);

    app(CrossOrgCopier::class)->copy(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        [],
        [$record->id->toString() => ['selected' => true, 'strategy' => null]],
        $source,
        $destination,
        $user,
    );

    $copied = AvgResponsibleProcessingRecord::query()->whereBelongsTo($destination)->firstOrFail();

    AuditLog::shouldHaveReceived('register')
        ->withArgs(fn (mixed $event): bool => $event instanceof TransferImportEvent
            && $event->method === TransferImportEvent::METHOD_COPY
            && $event->user?->id->equals($user->id) === true
            && $event->sourceOrganisationId === $source->id->toString()
            && $event->destinationOrganisationId === $destination->id->toString()
            && $event->failedReason === null
            && $event->result?->records === [
                [
                    'type' => TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->value,
                    'id' => $copied->id->toString(),
                    'action' => 'created',
                ]])
        ->once();
});

it('records a failed audit event when a copy is rejected', function (): void {
    AuditLog::spy();

    $organisation = Organisation::factory()->create();
    $user = copyableUser($organisation, Organisation::factory()->create());

    expect(fn () => app(CrossOrgCopier::class)->copy(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [],
        [],
        [],
        $organisation,
        $organisation,
        $user,
    ))->toThrow(TransferException::class);

    AuditLog::shouldHaveReceived('register')
        ->withArgs(fn (mixed $event): bool => $event instanceof TransferImportEvent
            && $event->method === TransferImportEvent::METHOD_COPY
            && $event->result === null
            && $event->failedReason === 'cannot copy an organisation into itself')
        ->once();
});

it('records the exception class, not its message, when a copy fails after authorization', function (): void {
    AuditLog::spy();

    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableUser($source, $destination);

    $this->mock(BundleBuilder::class)
        ->shouldReceive('build')
        ->andThrow(new RuntimeException('details that must not reach the audit log'));

    expect(fn () => app(CrossOrgCopier::class)->copy(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [],
        [],
        [],
        $source,
        $destination,
        $user,
    ))->toThrow(RuntimeException::class);

    AuditLog::shouldHaveReceived('register')
        ->withArgs(fn (mixed $event): bool => $event instanceof TransferImportEvent
            && $event->result === null
            && $event->failedReason === 'RuntimeException')
        ->once();
});

it('copies a document with its media into another organisation', function (): void {
    Storage::fake('media-library');

    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableUser($source, $destination);

    $record = AvgResponsibleProcessingRecord::factory()->for($source)->create([
        'name' => 'Verwerking met document',
        'has_processors' => true,
        'has_systems' => true,
    ]);
    $document = Document::factory()->for($source)->create(['name' => 'Beleid']);
    $document->addMediaFromString('text bytes')
        ->usingFileName('beleid.txt')
        ->usingName('Beleidsdocument')
        ->toMediaCollection(MediaGroup::ATTACHMENTS->value);
    $record->documents()->attach($document);

    app(CrossOrgCopier::class)->copy(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        ['documents' => [$document->id->toString()]],
        [
            $record->id->toString() => ['selected' => true, 'strategy' => null],
            $document->id->toString() => ['selected' => true, 'strategy' => null],
        ],
        $source,
        $destination,
        $user,
    );

    $copiedDocument = Document::query()->whereBelongsTo($destination)->where('name', 'Beleid')->firstOrFail();

    expect($copiedDocument->getFirstMedia(MediaGroup::ATTACHMENTS->value))->not->toBeNull()
        ->and($copiedDocument->getFirstMedia(MediaGroup::ATTACHMENTS->value)->file_name)->toBe('beleid.txt');
});

it('rejects copying an organisation into itself', function (): void {
    $organisation = Organisation::factory()->create();
    $user = copyableUser($organisation);
    [$record] = seedCopyableRecord($organisation);

    app(CrossOrgCopier::class)->copy(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        [],
        [$record->id->toString() => ['selected' => true, 'strategy' => null]],
        $organisation,
        $organisation,
        $user,
    );
})->throws(TransferException::class);

it('lists only other organisations where the user may import as copy targets', function (): void {
    $source = Organisation::factory()->create();
    $allowed = Organisation::factory()->create();
    $forbidden = Organisation::factory()->create();

    $user = User::factory()->hasAttached(collect([$source, $allowed, $forbidden]))->create();
    $user->organisationRoles()->create(['organisation_id' => $source->id, 'role' => Role::CHIEF_PRIVACY_OFFICER]);
    $user->organisationRoles()->create(['organisation_id' => $allowed->id, 'role' => Role::CHIEF_PRIVACY_OFFICER]);
    $user->organisationRoles()->create(['organisation_id' => $forbidden->id, 'role' => Role::MANDATE_HOLDER]);

    $targets = app(CrossOrgAuthorization::class)->copyTargetsFor($user, $source);
    $targetIds = collect($targets)->map(static fn (Organisation $o): string => $o->id->toString());

    expect($targetIds)->toContain($allowed->id->toString())
        ->and($targetIds)->not->toContain($forbidden->id->toString())
        ->and($targetIds)->not->toContain($source->id->toString());
});

it('takes the user global roles into account when resolving permissions in an organisation', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->hasAttached(collect([$organisation]))->create();
    // A global role plus the org role that grants import — exercises the global-role branch.
    $user->globalRoles()->create(['role' => Role::FUNCTIONAL_MANAGER]);
    $user->organisationRoles()->create(['organisation_id' => $organisation->id, 'role' => Role::CHIEF_PRIVACY_OFFICER]);

    $authorized = app(App\Services\CrossOrgAuthorization::class)->userHasPermissionInOrganisation(
        $user,
        $organisation,
        Permission::TRANSFER_IMPORT,
    );

    expect($authorized)->toBeTrue();
});

it('rejects a copy when the user has generic export and import rights but no transfer rights', function (
    array $sourcePermissions,
    array $destinationPermissions,
): void {
    ConfigTestHelper::set('permissions.roles_and_permissions', [
        Role::CHIEF_PRIVACY_OFFICER->value => array_map(static fn (Permission $p): string => $p->value, $sourcePermissions),
        Role::PRIVACY_OFFICER->value => array_map(static fn (Permission $p): string => $p->value, $destinationPermissions),
    ]);

    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = User::factory()->hasAttached(collect([$source, $destination]))->create();
    $user->organisationRoles()->create(['organisation_id' => $source->id, 'role' => Role::CHIEF_PRIVACY_OFFICER]);
    $user->organisationRoles()->create(['organisation_id' => $destination->id, 'role' => Role::PRIVACY_OFFICER]);
    [$record] = seedCopyableRecord($source);

    app(CrossOrgCopier::class)->copy(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        [],
        [$record->id->toString() => ['selected' => true, 'strategy' => null]],
        $source,
        $destination,
        $user,
    );
})->with([
    'no transfer export in source' => [
        [Permission::EXPORT, Permission::CORE_ENTITY_IMPORT],
        [Permission::EXPORT, Permission::CORE_ENTITY_IMPORT, Permission::TRANSFER_IMPORT],
    ],
    'no transfer import in destination' => [
        [Permission::EXPORT, Permission::TRANSFER_EXPORT],
        [Permission::EXPORT, Permission::CORE_ENTITY_IMPORT],
    ],
])->throws(TransferException::class);

it('does not offer organisations where the user only has generic import rights as copy targets', function (): void {
    ConfigTestHelper::set('permissions.roles_and_permissions', [
        Role::CHIEF_PRIVACY_OFFICER->value => [Permission::TRANSFER_EXPORT->value],
        Role::PRIVACY_OFFICER->value => [Permission::CORE_ENTITY_IMPORT->value],
    ]);

    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = User::factory()->hasAttached(collect([$source, $destination]))->create();
    $user->organisationRoles()->create(['organisation_id' => $source->id, 'role' => Role::CHIEF_PRIVACY_OFFICER]);
    $user->organisationRoles()->create(['organisation_id' => $destination->id, 'role' => Role::PRIVACY_OFFICER]);

    expect(app(CrossOrgAuthorization::class)->copyTargetsFor($user, $source))->toBe([]);
});
