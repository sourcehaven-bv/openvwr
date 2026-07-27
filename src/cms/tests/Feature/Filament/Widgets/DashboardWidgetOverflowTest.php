<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Enums\Snapshot\SnapshotApprovalStatus;
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

// The capped lists and their "Toon alle" links. Each widget stops at LIMIT rows
// and then points at the register holding the rest; below the limit there is
// nothing more to show and no link.

it('caps the signing list and links to the full overview', function (): void {
    $organisation = OrganisationTestHelper::create();
    $assignee = UserTestHelper::createForOrganisation($organisation);

    foreach (range(1, MyApprovalsWidget::LIMIT + 1) as $number) {
        $snapshot = Snapshot::factory()
            ->recycle($organisation)
            ->create(['name' => sprintf('Versie %d', $number)]);

        SnapshotApproval::factory()
            ->recycle($snapshot)
            ->create(['assigned_to' => $assignee->id, 'status' => SnapshotApprovalStatus::UNKNOWN]);
    }

    $this->withPermissions($assignee, [Permission::SNAPSHOT_APPROVAL_UPDATE_PERSONAL])
        ->withFilamentSession($assignee, $organisation);

    $widget = new MyApprovalsWidget();

    expect($widget->getRows())->toHaveCount(MyApprovalsWidget::LIMIT)
        ->and($widget->hasMore())->toBeTrue()
        ->and($widget->getAllUrl())->toBeString();

    $this->createLivewireTestable(MyApprovalsWidget::class)
        ->assertSee(__('dashboard.show_all'));
});

it('does not offer a full overview while the signing list fits', function (): void {
    $organisation = OrganisationTestHelper::create();
    $assignee = UserTestHelper::createForOrganisation($organisation);

    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();

    SnapshotApproval::factory()
        ->recycle($snapshot)
        ->create(['assigned_to' => $assignee->id, 'status' => SnapshotApprovalStatus::UNKNOWN]);

    $this->withPermissions($assignee, [Permission::SNAPSHOT_APPROVAL_UPDATE_PERSONAL])
        ->withFilamentSession($assignee, $organisation);

    expect((new MyApprovalsWidget())->hasMore())
        ->toBeFalse();
});

it('caps the overdue list', function (): void {
    $organisation = OrganisationTestHelper::create();

    foreach (range(1, OverdueItemsWidget::LIMIT + 1) as $number) {
        AvgResponsibleProcessingRecord::factory()
            ->recycle($organisation)
            ->create([
                'name' => sprintf('Verwerking %d', $number),
                'review_at' => CarbonImmutable::yesterday()->toDateString(),
            ]);
    }

    $this->asFilamentOrganisationUser($organisation);

    $widget = new OverdueItemsWidget();

    expect($widget->getRows())->toHaveCount(OverdueItemsWidget::LIMIT)
        ->and($widget->hasMore())->toBeTrue();
});

it('caps the breach list and links to the open breaches', function (): void {
    $organisation = OrganisationTestHelper::create();

    foreach (range(1, OpenDataBreachesWidget::LIMIT + 1) as $number) {
        DataBreachRecord::factory()
            ->recycle($organisation)
            ->create([
                'completed_at' => null,
                'state' => null,
                'discovered_at' => CarbonImmutable::now()->subDays($number),
            ]);
    }

    $this->asFilamentOrganisationUser($organisation);

    $widget = new OpenDataBreachesWidget();

    expect($widget->getRows())->toHaveCount(OpenDataBreachesWidget::LIMIT)
        ->and($widget->hasMore())->toBeTrue()
        ->and($widget->getAllUrl())->toContain('tableFilters');

    $this->createLivewireTestable(OpenDataBreachesWidget::class)
        ->assertSee(__('dashboard.show_all'));
});

it('falls back to the generic label for a document without a type', function (): void {
    $organisation = OrganisationTestHelper::create();

    // document_type_id is nullable and the form does not require it, so an
    // expired document can reach the list with no type to name it.
    $document = Document::factory()
        ->recycle($organisation)
        ->create([
            'name' => 'Naamloos type',
            'document_type_id' => null,
            'expires_at' => CarbonImmutable::today()->subYear(),
        ]);

    $this->asFilamentOrganisationUser($organisation);

    $rows = (new OverdueItemsWidget())->getRows();
    $row = collect($rows)->firstWhere('name', $document->name);

    expect($row)->not->toBeNull()
        ->and($row['type'])->toBe(__('document.model_singular'));
});
