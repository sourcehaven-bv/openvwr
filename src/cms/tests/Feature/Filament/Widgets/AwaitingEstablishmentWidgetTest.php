<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Filament\Widgets\AllClearWidget;
use App\Filament\Widgets\AwaitingEstablishmentWidget;
use App\Filament\Widgets\MyApprovalsWidget;
use App\Models\Organisation;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

// Creates a snapshot in one state with one approval per given status. The
// factories randomise both, so every test sets them explicitly.
$snapshotWithApprovals = static function (
    Organisation $organisation,
    string $state,
    array $statuses,
    string $name = 'Salarisadministratie',
): Snapshot {
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create(['name' => $name, 'state' => $state]);

    foreach ($statuses as $status) {
        SnapshotApproval::factory()
            ->recycle($snapshot)
            ->create(['status' => $status]);
    }

    return $snapshot;
};

it('lists a fully signed snapshot that is waiting to be established', function () use ($snapshotWithApprovals): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $snapshot = $snapshotWithApprovals($organisation, Approved::class, [SnapshotApprovalStatus::APPROVED]);

    $this->withPermissions($user, [Permission::SNAPSHOT_STATE_TO_ESTABLISHED])
        ->withFilamentSession($user, $organisation);

    expect(AwaitingEstablishmentWidget::canView())->toBeTrue();

    $this->createLivewireTestable(AwaitingEstablishmentWidget::class)
        ->assertSee($snapshot->name);
});

it('waits for the last signature before listing a snapshot', function () use ($snapshotWithApprovals): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    // One mandate holder has signed, the other has not: this is still their
    // work, not the privacy officer's.
    $snapshotWithApprovals($organisation, Approved::class, [
        SnapshotApprovalStatus::APPROVED,
        SnapshotApprovalStatus::UNKNOWN,
    ]);

    $this->withPermissions($user, [Permission::SNAPSHOT_STATE_TO_ESTABLISHED])
        ->withFilamentSession($user, $organisation);

    expect(AwaitingEstablishmentWidget::canView())->toBeFalse();
});

it('lists a snapshot whose signature was a refusal', function () use ($snapshotWithApprovals): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    // Declining is an answer too, and deciding what happens next is the privacy
    // officer's call. Left off the list, nobody would pick it up.
    $snapshotWithApprovals($organisation, Approved::class, [SnapshotApprovalStatus::DECLINED]);

    $this->withPermissions($user, [Permission::SNAPSHOT_STATE_TO_ESTABLISHED])
        ->withFilamentSession($user, $organisation);

    expect(AwaitingEstablishmentWidget::canView())->toBeTrue();
});

it('ignores a snapshot that was never submitted for approval', function () use ($snapshotWithApprovals): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $snapshotWithApprovals($organisation, InReview::class, [SnapshotApprovalStatus::APPROVED]);

    $this->withPermissions($user, [Permission::SNAPSHOT_STATE_TO_ESTABLISHED])
        ->withFilamentSession($user, $organisation);

    expect(AwaitingEstablishmentWidget::canView())->toBeFalse();
});

it('drops a snapshot from the list once it has been established', function () use ($snapshotWithApprovals): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $snapshotWithApprovals($organisation, Established::class, [SnapshotApprovalStatus::APPROVED]);

    $this->withPermissions($user, [Permission::SNAPSHOT_STATE_TO_ESTABLISHED])
        ->withFilamentSession($user, $organisation);

    expect(AwaitingEstablishmentWidget::canView())->toBeFalse();
});

it('ignores an approved snapshot that carries no approvals at all', function () use ($snapshotWithApprovals): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    // Nobody has signed, so "every approval is signed" must not hold here.
    $snapshotWithApprovals($organisation, Approved::class, []);

    $this->withPermissions($user, [Permission::SNAPSHOT_STATE_TO_ESTABLISHED])
        ->withFilamentSession($user, $organisation);

    expect(AwaitingEstablishmentWidget::canView())->toBeFalse();
});

it('hides the list from someone who may not establish', function () use ($snapshotWithApprovals): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $snapshotWithApprovals($organisation, Approved::class, [SnapshotApprovalStatus::APPROVED]);

    // A mandate holder signs but does not establish; the finished version is not
    // their work.
    $this->withPermissions($user, [Permission::SNAPSHOT_APPROVAL_UPDATE_PERSONAL])
        ->withFilamentSession($user, $organisation);

    expect(AwaitingEstablishmentWidget::canView())->toBeFalse();
});

it('does not list a fully signed snapshot from another organisation', function () use ($snapshotWithApprovals): void {
    $organisation = OrganisationTestHelper::create();
    $other = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $snapshotWithApprovals($other, Approved::class, [SnapshotApprovalStatus::APPROVED]);

    $this->withPermissions($user, [Permission::SNAPSHOT_STATE_TO_ESTABLISHED])
        ->withFilamentSession($user, $organisation);

    expect(AwaitingEstablishmentWidget::canView())->toBeFalse();
});

it(
    'stays quiet about being all clear while a snapshot waits to be established',
    function () use ($snapshotWithApprovals): void {
        $organisation = OrganisationTestHelper::create();
        $user = UserTestHelper::createForOrganisation($organisation);

        $snapshotWithApprovals($organisation, Approved::class, [SnapshotApprovalStatus::APPROVED]);

        $this->withPermissions($user, [
            Permission::SNAPSHOT_STATE_TO_ESTABLISHED,
            Permission::CORE_ENTITY_VIEW,
        ])->withFilamentSession($user, $organisation);

        expect(AllClearWidget::canView())->toBeFalse()
            ->and(MyApprovalsWidget::canView())->toBeFalse();
    },
);
