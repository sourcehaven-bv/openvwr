<?php

declare(strict_types=1);

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

it('counts overdue reviews across all three processing registers', function (): void {
    $organisation = OrganisationTestHelper::create();
    $yesterday = CarbonImmutable::yesterday();

    AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => $yesterday->toDateString()]);
    AvgProcessorProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => $yesterday->toDateString()]);
    WpgProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => $yesterday->toDateString()]);

    expect(AttentionCountServiceTestHelper::create()->reviewsOverdue($organisation))
        ->toBe(3);
});

it('does not count a review that is not yet due as overdue', function (): void {
    $organisation = OrganisationTestHelper::create();

    AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::tomorrow()->toDateString()]);

    expect(AttentionCountServiceTestHelper::create()->reviewsOverdue($organisation))
        ->toBe(0);
});

it('treats a review due today as upcoming rather than overdue', function (): void {
    $organisation = OrganisationTestHelper::create();

    AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::today()->toDateString()]);

    $attentionCountService = AttentionCountServiceTestHelper::create();

    expect($attentionCountService->reviewsOverdue($organisation))->toBe(0)
        ->and($attentionCountService->reviewsSoon($organisation))->toBe(1);
});

it('counts a review inside the horizon as upcoming but not one beyond it', function (): void {
    $organisation = OrganisationTestHelper::create();
    $today = CarbonImmutable::today();

    AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => $today->addMonths(3)->toDateString()]);
    AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => $today->addMonths(3)->addDay()->toDateString()]);

    expect(AttentionCountServiceTestHelper::create()->reviewsSoon($organisation))
        ->toBe(1);
});

it('ignores records without a review date', function (): void {
    $organisation = OrganisationTestHelper::create();

    AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => null]);

    $attentionCountService = AttentionCountServiceTestHelper::create();

    expect($attentionCountService->reviewsOverdue($organisation))->toBe(0)
        ->and($attentionCountService->reviewsSoon($organisation))->toBe(0);
});

it('does not count another organisation\'s overdue reviews', function (): void {
    $organisation = OrganisationTestHelper::create();
    $otherOrganisation = OrganisationTestHelper::create();

    AvgResponsibleProcessingRecord::factory()
        ->recycle($otherOrganisation)
        ->create(['review_at' => CarbonImmutable::yesterday()->toDateString()]);

    expect(AttentionCountServiceTestHelper::create()->reviewsOverdue($organisation))
        ->toBe(0);
});

it('counts expired and soon-expiring documents separately', function (): void {
    $organisation = OrganisationTestHelper::create();
    $today = CarbonImmutable::today();

    Document::factory()
        ->recycle($organisation)
        ->create(['expires_at' => $today->subDay()]);
    Document::factory()
        ->recycle($organisation)
        ->create(['expires_at' => $today->addMonth()]);
    Document::factory()
        ->recycle($organisation)
        ->create(['expires_at' => $today->addYear()]);

    $attentionCountService = AttentionCountServiceTestHelper::create();

    expect($attentionCountService->documentsExpired($organisation))->toBe(1)
        ->and($attentionCountService->documentsExpiringSoon($organisation))->toBe(1);
});

it('does not count another organisation\'s documents', function (): void {
    $organisation = OrganisationTestHelper::create();
    $otherOrganisation = OrganisationTestHelper::create();

    Document::factory()
        ->recycle($otherOrganisation)
        ->create(['expires_at' => CarbonImmutable::yesterday()]);

    expect(AttentionCountServiceTestHelper::create()->documentsExpired($organisation))
        ->toBe(0);
});

it('counts data breaches that have not been completed', function (): void {
    $organisation = OrganisationTestHelper::create();

    DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['completed_at' => null]);
    DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['completed_at' => CarbonImmutable::yesterday()]);

    expect(AttentionCountServiceTestHelper::create()->openDataBreaches($organisation))
        ->toBe(1);
});

it('does not count another organisation\'s open data breaches', function (): void {
    $organisation = OrganisationTestHelper::create();
    $otherOrganisation = OrganisationTestHelper::create();

    DataBreachRecord::factory()
        ->recycle($otherOrganisation)
        ->create(['completed_at' => null]);

    expect(AttentionCountServiceTestHelper::create()->openDataBreaches($organisation))
        ->toBe(0);
});

it('counts only approvals assigned to this user and still unsigned', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $otherUser = UserTestHelper::createForOrganisation($organisation);

    // One approval per user per snapshot is a unique constraint, so the signed
    // and unsigned cases need snapshots of their own.
    $unsignedSnapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();
    $signedSnapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();

    SnapshotApproval::factory()
        ->recycle($unsignedSnapshot)
        ->create(['assigned_to' => $user->id, 'status' => SnapshotApprovalStatus::UNKNOWN]);
    SnapshotApproval::factory()
        ->recycle($signedSnapshot)
        ->create(['assigned_to' => $user->id, 'status' => SnapshotApprovalStatus::APPROVED]);
    SnapshotApproval::factory()
        ->recycle($unsignedSnapshot)
        ->create(['assigned_to' => $otherUser->id, 'status' => SnapshotApprovalStatus::UNKNOWN]);

    expect(AttentionCountServiceTestHelper::create()->unsignedApprovals($organisation, $user))
        ->toBe(1);
});

it('does not count approvals on another organisation\'s snapshots', function (): void {
    $organisation = OrganisationTestHelper::create();
    $otherOrganisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $otherSnapshot = Snapshot::factory()
        ->recycle($otherOrganisation)
        ->create();

    SnapshotApproval::factory()
        ->recycle($otherSnapshot)
        ->create(['assigned_to' => $user->id, 'status' => SnapshotApprovalStatus::UNKNOWN]);

    expect(AttentionCountServiceTestHelper::create()->unsignedApprovals($organisation, $user))
        ->toBe(0);
});

it('honours a custom horizon', function (): void {
    $organisation = OrganisationTestHelper::create();

    AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::today()->addMonths(6)->toDateString()]);

    expect(AttentionCountServiceTestHelper::create()->reviewsSoon($organisation))->toBe(0)
        ->and(AttentionCountServiceTestHelper::create(12)->reviewsSoon($organisation))->toBe(1);
});
