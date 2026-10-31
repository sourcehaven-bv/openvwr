<?php

declare(strict_types=1);

use App\Filament\Pages\PublicWebsite;
use App\Models\PublicWebsite as PublicWebsiteModel;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('cannot access the page without permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $this->withFilamentSession($user, $organisation)
        ->withPermissions($user, [])
        ->get(sprintf('%s/public-website', $organisation->slug))
        ->assertForbidden();
});

it('cannot access the page (with permission) for non-existing', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentUser()
        ->get(sprintf('%s/public-website', $organisation->slug))
        ->assertNotFound();
});

it('loads the page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation)
        ->get(sprintf('%s/public-website', $organisation->slug))
        ->assertOk()
        ->assertSee(__('public_website.public_website'))
        ->assertSee(__('public_website.home_content'))
        ->assertSee(__('public_website.overview.title'))
        ->assertSee(__('public_website.overview.never_checked'));
});

it('can edit the properties', function (): void {
    $organisation = OrganisationTestHelper::create();
    PublicWebsiteModel::factory()
        ->recycle($organisation)
        ->create();

    $newHomeContent = fake()->optional()->sentence();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(PublicWebsite::class)
        ->fillForm([
            'home_content' => $newHomeContent,
        ])
        ->call('save');

    $this->assertDatabaseHas(PublicWebsiteModel::class, [
        'home_content' => $newHomeContent,
    ]);
});
