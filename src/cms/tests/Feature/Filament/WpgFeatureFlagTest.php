<?php

declare(strict_types=1);

use App\Filament\RelationManagers\WpgProcessingRecordRelationManager;
use App\Filament\Resources\DocumentResource\Pages\EditDocument;
use App\Filament\Resources\WpgProcessingRecordResource;
use App\Filament\Resources\WpgProcessingRecordResource\Pages\ListWpgProcessingRecords;
use App\Filament\Resources\WpgProcessingRecordServiceResource;
use App\Filament\Resources\WpgProcessingRecordServiceResource\Pages\ListWpgProcessingRecordServices;
use App\Models\Document;
use App\Models\Wpg\WpgProcessingRecord;
use App\Models\Wpg\WpgProcessingRecordService;
use App\Services\Dashboard\AttentionCountService;
use Carbon\CarbonImmutable;
use Tests\Helpers\Model\OrganisationTestHelper;

beforeEach(function (): void {
    config()->set('features.wpg', true);
});

it('registers the wpg navigation when the feature is enabled', function (): void {
    $this->asFilamentUser();

    expect(WpgProcessingRecordResource::shouldRegisterNavigation())->toBeTrue()
        ->and(WpgProcessingRecordServiceResource::shouldRegisterNavigation())->toBeTrue();
});

it('hides the wpg navigation when the feature is disabled', function (): void {
    $this->asFilamentUser();

    config()->set('features.wpg', false);

    expect(WpgProcessingRecordResource::shouldRegisterNavigation())->toBeFalse()
        ->and(WpgProcessingRecordServiceResource::shouldRegisterNavigation())->toBeFalse();
});

it('allows viewing the wpg register when the feature is enabled', function (): void {
    $this->asFilamentUser();

    expect(WpgProcessingRecordResource::canViewAny())->toBeTrue()
        ->and(WpgProcessingRecordServiceResource::canViewAny())->toBeTrue();
});

it('refuses viewing the wpg register when the feature is disabled', function (): void {
    $this->asFilamentUser();

    config()->set('features.wpg', false);

    expect(WpgProcessingRecordResource::canViewAny())->toBeFalse()
        ->and(WpgProcessingRecordServiceResource::canViewAny())->toBeFalse();
});

it('allows creating and editing wpg records when the feature is enabled', function (): void {
    $organisation = OrganisationTestHelper::create();
    $record = WpgProcessingRecord::factory()->recycle($organisation)->create();
    $service = WpgProcessingRecordService::factory()->recycle($organisation)->create();

    $this->asFilamentOrganisationUser($organisation);

    expect(WpgProcessingRecordResource::canCreate())->toBeTrue()
        ->and(WpgProcessingRecordResource::canView($record))->toBeTrue()
        ->and(WpgProcessingRecordResource::canEdit($record))->toBeTrue()
        ->and(WpgProcessingRecordServiceResource::canCreate())->toBeTrue()
        ->and(WpgProcessingRecordServiceResource::canView($service))->toBeTrue()
        ->and(WpgProcessingRecordServiceResource::canEdit($service))->toBeTrue();
});

it('refuses creating and editing wpg records when the feature is disabled', function (): void {
    $organisation = OrganisationTestHelper::create();
    $record = WpgProcessingRecord::factory()->recycle($organisation)->create();
    $service = WpgProcessingRecordService::factory()->recycle($organisation)->create();

    $this->asFilamentOrganisationUser($organisation);

    config()->set('features.wpg', false);

    expect(WpgProcessingRecordResource::canCreate())->toBeFalse()
        ->and(WpgProcessingRecordResource::canView($record))->toBeFalse()
        ->and(WpgProcessingRecordResource::canEdit($record))->toBeFalse()
        ->and(WpgProcessingRecordServiceResource::canCreate())->toBeFalse()
        ->and(WpgProcessingRecordServiceResource::canView($service))->toBeFalse()
        ->and(WpgProcessingRecordServiceResource::canEdit($service))->toBeFalse();
});

it('loads the wpg list page when the feature is enabled', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListWpgProcessingRecords::class)
        ->assertOk();
});

it('forbids the wpg list page when the feature is disabled', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);

    config()->set('features.wpg', false);

    $this->createLivewireTestable(ListWpgProcessingRecords::class)
        ->assertForbidden();
});

it('forbids the wpg lookup list page when the feature is disabled', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);

    config()->set('features.wpg', false);

    $this->createLivewireTestable(ListWpgProcessingRecordServices::class)
        ->assertForbidden();
});

it('shows the wpg relation manager on other records when the feature is enabled', function (): void {
    $organisation = OrganisationTestHelper::create();
    $document = Document::factory()->recycle($organisation)->create();

    $this->asFilamentOrganisationUser($organisation);

    expect(WpgProcessingRecordRelationManager::canViewForRecord($document, EditDocument::class))->toBeTrue();
});

it('hides the wpg relation manager on other records when the feature is disabled', function (): void {
    $organisation = OrganisationTestHelper::create();
    $document = Document::factory()->recycle($organisation)->create();

    $this->asFilamentOrganisationUser($organisation);

    config()->set('features.wpg', false);

    expect(WpgProcessingRecordRelationManager::canViewForRecord($document, EditDocument::class))->toBeFalse();
});

it('counts wpg records among the reviewable registers when the feature is enabled', function (): void {
    $organisation = OrganisationTestHelper::create();
    WpgProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::yesterday()->toDateString()]);

    expect((new AttentionCountService())->reviewsOverdue($organisation))->toBe(1);
});

it('leaves wpg records out of the reviewable registers when the feature is disabled', function (): void {
    $organisation = OrganisationTestHelper::create();
    WpgProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['review_at' => CarbonImmutable::yesterday()->toDateString()]);

    config()->set('features.wpg', false);

    expect((new AttentionCountService())->reviewsOverdue($organisation))->toBe(0);
});
