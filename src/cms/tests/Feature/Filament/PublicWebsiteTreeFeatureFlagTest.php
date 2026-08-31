<?php

declare(strict_types=1);

use App\Filament\Resources\PublicWebsiteTreeResource;
use App\Filament\Resources\PublicWebsiteTreeResource\Pages\ListPublicWebsiteTrees;
use App\Models\PublicWebsiteTree;
use Tests\Helpers\Model\OrganisationTestHelper;

beforeEach(function (): void {
    config()->set('features.publishing', true);
});

it('registers the public website tree navigation when publishing is enabled', function (): void {
    $this->asFilamentUser();

    expect(PublicWebsiteTreeResource::shouldRegisterNavigation())->toBeTrue();
});

it('hides the public website tree navigation when publishing is disabled', function (): void {
    $this->asFilamentUser();

    config()->set('features.publishing', false);

    expect(PublicWebsiteTreeResource::shouldRegisterNavigation())->toBeFalse();
});

it('allows viewing the public website tree when publishing is enabled', function (): void {
    $this->asFilamentUser();

    expect(PublicWebsiteTreeResource::canViewAny())->toBeTrue();
});

it('refuses viewing the public website tree when publishing is disabled', function (): void {
    $this->asFilamentUser();

    config()->set('features.publishing', false);

    expect(PublicWebsiteTreeResource::canViewAny())->toBeFalse()
        ->and(PublicWebsiteTreeResource::canCreate())->toBeFalse();
});

it('refuses editing a public website tree record when publishing is disabled', function (): void {
    $this->asFilamentUser();

    $publicWebsiteTree = PublicWebsiteTree::factory()->create();

    config()->set('features.publishing', false);

    expect(PublicWebsiteTreeResource::canView($publicWebsiteTree))->toBeFalse()
        ->and(PublicWebsiteTreeResource::canEdit($publicWebsiteTree))->toBeFalse();
});

/**
 * The finding behind this test: hiding the menu entry is not enough, an authorised
 * user could still reach the page by typing its url.
 */
it('forbids the public website tree page when publishing is disabled', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);

    config()->set('features.publishing', false);

    $this->createLivewireTestable(ListPublicWebsiteTrees::class)
        ->assertForbidden();
});

it('allows the public website tree page when publishing is enabled', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(ListPublicWebsiteTrees::class)
        ->assertSuccessful();
});
