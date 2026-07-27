<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\Wpg\WpgProcessingRecord;
use Carbon\CarbonImmutable;
use Tests\Helpers\Dashboard\AttentionCountServiceTestHelper;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

// The dashboard reads across five tables without going through a Filament
// resource, so it does not inherit the panel's tenant scoping. Every reader is
// therefore checked here against a second organisation holding nothing but
// matching rows: anything that leaks shows up as a non-zero count.

it('reports nothing from another organisation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $otherOrganisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $yesterday = CarbonImmutable::yesterday();

    // Everything below belongs to the other organisation and would qualify for
    // one of the dashboard lists if the scoping were missing.
    AvgResponsibleProcessingRecord::factory()
        ->recycle($otherOrganisation)
        ->create(['review_at' => $yesterday->toDateString()]);
    AvgProcessorProcessingRecord::factory()
        ->recycle($otherOrganisation)
        ->create(['review_at' => $yesterday->toDateString()]);
    WpgProcessingRecord::factory()
        ->recycle($otherOrganisation)
        ->create(['review_at' => $yesterday->toDateString()]);
    Document::factory()
        ->recycle($otherOrganisation)
        ->create(['expires_at' => $yesterday]);
    DataBreachRecord::factory()
        ->recycle($otherOrganisation)
        ->create(['completed_at' => null, 'discovered_at' => $yesterday]);

    // An approval assigned to *this* user, but on the other organisation's
    // snapshot: the assignment alone must not be enough to surface it.
    $otherSnapshot = Snapshot::factory()
        ->recycle($otherOrganisation)
        ->for(
            AvgResponsibleProcessingRecord::factory()->recycle($otherOrganisation)->create(),
            'snapshotSource',
        )
        ->create();
    SnapshotApproval::factory()
        ->recycle($otherSnapshot)
        ->create(['assigned_to' => $user->id, 'status' => SnapshotApprovalStatus::UNKNOWN]);

    $this->withPermissions($user, Permission::cases())
        ->withFilamentSession($user, $organisation);

    $attentionCountService = AttentionCountServiceTestHelper::create();

    expect($attentionCountService->reviewsOverdue($organisation))->toBe(0)
        ->and($attentionCountService->reviewsSoon($organisation))->toBe(0)
        ->and($attentionCountService->documentsExpired($organisation))->toBe(0)
        ->and($attentionCountService->documentsExpiringSoon($organisation))->toBe(0)
        ->and($attentionCountService->openDataBreaches($organisation))->toBe(0)
        ->and($attentionCountService->unsignedApprovals($organisation, $user))->toBe(0)
        ->and($attentionCountService->overdueItems($organisation, 10))->toBe([])
        ->and($attentionCountService->openDataBreachRecords($organisation, 10)->all())->toBe([])
        ->and($attentionCountService->unsignedApprovalsFor($organisation, $user, 10)->all())->toBe([]);
});

it('reports its own organisation\'s rows, so the isolation check is meaningful', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $yesterday = CarbonImmutable::yesterday();

    AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => $yesterday->toDateString()]);
    Document::factory()
        ->recycle($organisation)
        ->create(['expires_at' => $yesterday]);
    DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['completed_at' => null, 'discovered_at' => $yesterday]);

    // The other two review registers get an explicit future date: their factory
    // assigns review_at randomly, which would otherwise make the counts below
    // depend on chance.
    AvgProcessorProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::today()->addYear()->toDateString()]);
    WpgProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::today()->addYear()->toDateString()]);

    // Point the snapshot at a record whose review date is pinned: the factory
    // otherwise invents a source record with a random review_at, which lands in
    // the past often enough to make the counts below flaky.
    $snapshotSource = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::today()->addYear()->toDateString()]);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($snapshotSource, 'snapshotSource')
        ->create();
    SnapshotApproval::factory()
        ->recycle($snapshot)
        ->create(['assigned_to' => $user->id, 'status' => SnapshotApprovalStatus::UNKNOWN]);

    // overdueItems() builds links to Filament resources, which resolve the
    // tenant from the panel, so it needs a session like the widget has.
    $this->withPermissions($user, Permission::cases())
        ->withFilamentSession($user, $organisation);

    $attentionCountService = AttentionCountServiceTestHelper::create();

    expect($attentionCountService->reviewsOverdue($organisation))->toBe(1)
        ->and($attentionCountService->documentsExpired($organisation))->toBe(1)
        ->and($attentionCountService->openDataBreaches($organisation))->toBe(1)
        ->and($attentionCountService->unsignedApprovals($organisation, $user))->toBe(1)
        ->and($attentionCountService->overdueItems($organisation, 10))->toHaveCount(2)
        ->and($attentionCountService->openDataBreachRecords($organisation, 10))->toHaveCount(1)
        ->and($attentionCountService->unsignedApprovalsFor($organisation, $user, 10))->toHaveCount(1);
});
