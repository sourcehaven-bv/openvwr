<?php

declare(strict_types=1);

namespace Tests\Unit\Filament;

use App\Components\Uuid\Uuid;
use App\Filament\Actions\GoToPublicPageAction;
use App\Filament\Infolists\Tabs\Snapshot\ViewInfoTab;
use App\Filament\Pages\PublicWebsite;
use App\Filament\Resources\OrganisationResource\OrganisationResourceForm;
use App\Filament\Resources\OrganisationResource\OrganisationResourceInfolist;
use App\Filament\Resources\PublicWebsiteTreeResource\Pages\ListPublicWebsiteTrees;
use App\Models\Organisation;
use App\Models\PublicWebsiteTree;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Tests\Helpers\LivewireTestHelper;
use Tests\TestCase;
use Throwable;

use function __;
use function beforeEach;
use function config;
use function expect;
use function it;
use function uses;

uses(TestCase::class);

beforeEach(function (): void {
    config()->set('features.publishing', true);
});

/**
 * The headings of the visible sections of the snapshot info tab.
 *
 * @return array<int, string>
 */
function snapshotSectionHeadings(): array
{
    $infolist = Infolist::make(LivewireTestHelper::createTestFormComponent())
        ->schema([ViewInfoTab::make('info')]);

    $tab = $infolist->getComponents()[0];

    $headings = [];

    foreach ($tab->getChildComponents() as $component) {
        if (!$component instanceof Section) {
            continue;
        }

        // Sections whose visibility depends on the snapshot record cannot be
        // evaluated without one; the publishing sections do not need it.
        try {
            $hidden = $component->isHidden();
        } catch (Throwable) {
            continue;
        }

        if ($hidden) {
            continue;
        }

        $headings[] = (string) $component->getHeading();
    }

    return $headings;
}

/**
 * The headings of the top level sections of the organisation form.
 *
 * @return array<int, string>
 */
function organisationFormHeadings(): array
{
    // The poster field asks the authentication service for the current tenant.
    $organisation = new Organisation();
    $organisation->id = Uuid::generate();
    Filament::setTenant($organisation, isQuiet: true);

    $form = OrganisationResourceForm::form(
        Form::make(LivewireTestHelper::createTestFormComponent()),
    );

    $headings = [];

    foreach ($form->getComponents() as $component) {
        $headings[] = (string) $component->getHeading();
    }

    return $headings;
}

/**
 * The tree icon the website tree page renders for a node.
 */
function treeRecordIcon(PublicWebsiteTree $tree): string
{
    $page = new ListPublicWebsiteTrees();

    return $page->getTreeRecordIcon($tree);
}

/**
 * The top level section of the organisation infolist with the given heading.
 */
function organisationInfolistSection(string $heading): ?Section
{
    foreach (OrganisationResourceInfolist::getSchema() as $component) {
        if (!$component instanceof Section) {
            continue;
        }

        if ((string) $component->getHeading() === $heading) {
            return $component;
        }
    }

    return null;
}

it('shows the public data section on a snapshot when publishing is enabled', function (): void {
    expect(snapshotSectionHeadings())
        ->toContain(__('snapshot.public_data'))
        ->toContain(__('snapshot.private_data'));
});

it('hides the public data section on a snapshot when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    $headings = snapshotSectionHeadings();

    expect($headings)
        ->not->toContain(__('snapshot.public_data'))
        ->not->toContain(__('snapshot.private_data'))
        ->toContain(__('snapshot.data'));
});

it('registers the public website page navigation when publishing is enabled', function (): void {
    expect(PublicWebsite::shouldRegisterNavigation())->toBeTrue();
});

it('hides the public website page navigation when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    expect(PublicWebsite::shouldRegisterNavigation())->toBeFalse();
});

it('shows the go to public page action when publishing is enabled', function (): void {
    expect(GoToPublicPageAction::make()->isHidden())->toBeFalse();
});

it('hides the go to public page action when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    expect(GoToPublicPageAction::make()->isHidden())->toBeTrue();
});

it('shows the public from entry on the organisation infolist when publishing is enabled', function (): void {
    $section = organisationInfolistSection(__('organisation.section_public'));

    expect($section)->not->toBeNull();

    $labels = [];
    foreach ($section->getChildComponents() as $entry) {
        if ($entry->isHidden()) {
            continue;
        }

        $labels[] = (string) $entry->getLabel();
    }

    expect($labels)->toContain(__('general.public_from'));
});

it('hides the public from entry on the organisation infolist when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    $section = organisationInfolistSection(__('organisation.section_public'));

    expect($section)->not->toBeNull();

    $labels = [];
    foreach ($section->getChildComponents() as $entry) {
        if ($entry->isHidden()) {
            continue;
        }

        $labels[] = (string) $entry->getLabel();
    }

    expect($labels)
        ->not->toContain(__('general.public_from'))
        ->toContain(__('organisation.slug'));
});

it('labels the organisation form section as the public website when publishing is enabled', function (): void {
    expect(organisationFormHeadings())
        ->toContain(__('organisation.section_public'))
        ->not->toContain(__('organisation.section_access'));
});

it('labels the organisation form section as access when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    expect(organisationFormHeadings())
        ->toContain(__('organisation.section_access'))
        ->not->toContain(__('organisation.section_public'));
});

it('shows the eye icon on the website tree when publishing is enabled', function (): void {
    $tree = new PublicWebsiteTree();
    $tree->public_from = null;

    expect(treeRecordIcon($tree))->toBe('heroicon-o-eye-slash');
});

it('drops the eye icon on the website tree when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    $tree = new PublicWebsiteTree();
    $tree->public_from = null;

    expect(treeRecordIcon($tree))->toBe('heroicon-o-document-text');
});
