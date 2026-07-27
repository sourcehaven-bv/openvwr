<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Filament\Widgets\AllClearWidget;
use App\Filament\Widgets\MyApprovalsWidget;
use App\Filament\Widgets\OpenDataBreachesWidget;
use App\Filament\Widgets\OverdueItemsWidget;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use Carbon\CarbonImmutable;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('lists an overdue review', function (): void {
    $organisation = OrganisationTestHelper::create();

    $record = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'name' => 'Salarisadministratie',
            'review_at' => CarbonImmutable::yesterday()->toDateString(),
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(OverdueItemsWidget::class)
        ->assertSee($record->name);
});

it('lists an expired document alongside overdue reviews, most overdue first', function (): void {
    $organisation = OrganisationTestHelper::create();

    $document = Document::factory()
        ->recycle($organisation)
        ->create(['name' => 'Oudste', 'expires_at' => CarbonImmutable::today()->subYear()]);
    $record = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['name' => 'Nieuwste', 'review_at' => CarbonImmutable::yesterday()->toDateString()]);

    $this->asFilamentOrganisationUser($organisation);

    $names = array_column((new OverdueItemsWidget())->getRows(), 'name');

    expect($names)
        ->toBe([$document->name, $record->name]);
});

it('hides the overdue list when nothing has passed its date', function (): void {
    $organisation = OrganisationTestHelper::create();

    AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::today()->addYear()->toDateString()]);

    $this->asFilamentOrganisationUser($organisation);

    expect(OverdueItemsWidget::canView())
        ->toBeFalse();
});

it('says so when there is nothing at all to do', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);

    expect(AllClearWidget::canView())->toBeTrue()
        ->and(OpenDataBreachesWidget::canView())->toBeFalse()
        ->and(OverdueItemsWidget::canView())->toBeFalse()
        ->and(MyApprovalsWidget::canView())->toBeFalse();

    $this->createLivewireTestable(AllClearWidget::class)
        ->assertSee(__('dashboard.all_clear.heading'));
});

it('does not serve one user\'s approvals to another in the same organisation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $assignee = UserTestHelper::createForOrganisation($organisation);
    $colleague = UserTestHelper::createForOrganisation($organisation);

    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();

    SnapshotApproval::factory()
        ->recycle($snapshot)
        ->create(['assigned_to' => $assignee->id, 'status' => SnapshotApprovalStatus::UNKNOWN]);

    $this->withPermissions($assignee, [Permission::SNAPSHOT_APPROVAL_UPDATE_PERSONAL])
        ->withFilamentSession($assignee, $organisation);

    expect(MyApprovalsWidget::canView())
        ->toBeTrue();

    // Same organisation, same PHP process, different person: the colleague has
    // nothing to sign and must not inherit the assignee's rows from a cache.
    $this->withPermissions($colleague, [Permission::SNAPSHOT_APPROVAL_UPDATE_PERSONAL])
        ->withFilamentSession($colleague, $organisation);

    expect(MyApprovalsWidget::canView())
        ->toBeFalse();
});

it('does not claim all clear to someone who cannot see the register', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    // A functional manager holds no register permissions at all. Telling them
    // nothing needs attention would state something they cannot know.
    $this->withPermissions($user, [Permission::USER_VIEW])
        ->withFilamentSession($user, $organisation);

    expect(AllClearWidget::canView())
        ->toBeFalse();
});

it('stays quiet about being all clear while any list has rows', function (): void {
    $organisation = OrganisationTestHelper::create();

    DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['completed_at' => null, 'ap_reported' => false]);

    $this->asFilamentOrganisationUser($organisation);

    expect(AllClearWidget::canView())
        ->toBeFalse();
});
