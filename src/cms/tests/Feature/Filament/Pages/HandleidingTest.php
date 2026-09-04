<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Filament\Pages\Manual\Handleiding;
use App\Manual\Content\ReferenceContent;
use App\Manual\Manual;
use App\Manual\Task;
use App\Manual\Topic;
use Tests\Helpers\ConfigTestHelper;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\RoleTestHelper;

/**
 * The manual is a takeover of the panel: its own layout, its own menu, one page
 * per task and per topic. It stays a Filament page so that authentication, the
 * one time password gate, tenant resolution and authorisation are the panel's
 * own and not a second copy, and these tests hold that to account at url level.
 */
function manualUrl(string $slug, string $path = ''): string
{
    return sprintf('%s/handleiding%s', $slug, $path);
}

it('shows the task overview as the landing page', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug))
        ->assertOk()
        ->assertSee(__('manual.tasks_heading'))
        ->assertSee('Een verwerking vastleggen');
});

it('replaces the panel navigation with the manual menu', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug))
        ->assertOk()
        // the manual's own menu, and one clear way out
        ->assertSee('manual-nav', escape: false)
        ->assertSee(__('manual.exit'))
        // and not the panel's own sidebar
        ->assertDontSee('fi-main-sidebar', escape: false);
});

it('links every task and topic in the menu to its own page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $manual = new Manual();

    $response = $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug))
        ->assertOk();

    foreach ($manual->tasks() as $task) {
        $response->assertSee(manualUrl($organisation->slug, '/taken/' . $task->id), escape: false);
    }

    foreach ($manual->topics() as $topic) {
        $response->assertSee(manualUrl($organisation->slug, '/naslag/' . $topic->id), escape: false);
    }
});

it('gives every task a page of its own', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    foreach ((new Manual())->tasks() as $task) {
        $this->get(manualUrl($organisation->slug, '/taken/' . $task->id))
            ->assertOk()
            ->assertSee($task->title, escape: false);
    }
});

it('gives every topic a page of its own', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    foreach ((new Manual())->topics() as $topic) {
        $this->get(manualUrl($organisation->slug, '/naslag/' . $topic->id))
            ->assertOk()
            ->assertSee($topic->title, escape: false);
    }
});

it('links a task step to the pages of the topics it explains', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug, '/taken/verwerking-vastleggen'))
        ->assertOk()
        ->assertSee(__('manual.see_reference'))
        ->assertSee(manualUrl($organisation->slug, '/naslag/verwerkingsregisters'), escape: false);
});

it('links a topic back to the tasks that use it', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug, '/naslag/export'))
        ->assertOk()
        ->assertSee(__('manual.used_in_tasks'))
        ->assertSee(manualUrl($organisation->slug, '/taken/overzicht-opvragen'), escape: false);
});

it('leaves the backlink block out on a topic that no task uses', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug, '/naslag/over-openvwr'))
        ->assertOk()
        ->assertDontSee(__('manual.used_in_tasks'));
});

it('shows the availability of a topic that has one', function (): void {
    $organisation = OrganisationTestHelper::create();
    $topic = collect((new Manual())->topics())
        ->first(static fn (Topic $topic): bool => $topic->availability !== null);

    expect($topic)->not->toBeNull();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug, '/naslag/' . $topic->id))
        ->assertOk()
        ->assertSee(__('manual.available_for'));
});

it('shows the closing note of a task that has one', function (): void {
    $organisation = OrganisationTestHelper::create();
    $task = collect((new Manual())->tasks())
        ->first(static fn (Task $task): bool => $task->done !== null);

    expect($task)->not->toBeNull();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug, '/taken/' . $task->id))
        ->assertOk()
        ->assertSee(__('manual.done'));
});

it('shows the images from the public directory', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug, '/naslag/inloggen'))
        ->assertOk()
        ->assertSee('/handleiding/01_welkom/01_login.png', escape: false);
});

it('renders the callouts of the source text', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug, '/naslag/versie-indienen'))
        ->assertOk()
        ->assertSee('manual-callout', escape: false);
});

it('turns a cross reference into a link to the topic it names', function (): void {
    // Topics are written with the anchor spelling the pdf used. Every topic is
    // its own page here, so a bare "#gebruikers" would point at an anchor that
    // is not on this page and do nothing at all when clicked.
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug, '/naslag/rollen'))
        ->assertOk()
        ->assertSee(manualUrl($organisation->slug, '/naslag/gebruikers'), escape: false)
        ->assertDontSee('href="#gebruikers"', escape: false);
});

it('leaves an anchor alone when no topic answers to it', function (): void {
    $topic = new Topic(id: 'los', title: 'Los', body: 'Zie [ergens](#geen-onderwerp).');

    expect($topic->html(static fn (): ?string => null))
        ->toContain('href="#geen-onderwerp"');
});

it('404s on a task that does not exist', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug, '/taken/bestaat-niet'))
        ->assertNotFound();
});

it('404s on a topic that does not exist', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug, '/naslag/bestaat-niet'))
        ->assertNotFound();
});

/**
 * The flags have to bite on the url, not merely in the menu. Content that is
 * only hidden from the navigation is still readable by anyone who guesses the
 * address, which is exactly the class of bug this repository has had before.
 */
it('404s on a task whose feature flag is off, rather than only hiding it', function (): void {
    ConfigTestHelper::set('features.publishing', false);

    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug, '/taken/verwerking-publiceren'))
        ->assertNotFound();
});

it('404s on a topic whose feature flag is off, rather than only hiding it', function (): void {
    ConfigTestHelper::set('features.wpg', false);

    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug, '/naslag/wpg-register'))
        ->assertNotFound();
});

it('serves that same task once its flag is on', function (): void {
    ConfigTestHelper::set('features.publishing', true);

    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug, '/taken/verwerking-publiceren'))
        ->assertOk();
});

it('keeps gated content out of the menu as well', function (): void {
    ConfigTestHelper::set('features.wpg', false);
    ConfigTestHelper::set('features.publishing', false);

    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(manualUrl($organisation->slug))
        ->assertOk()
        ->assertDontSee('Een Wpg-verwerking vastleggen')
        ->assertDontSee('Een verwerking publiceren');
});

it('refuses an unauthenticated visitor on every page of the manual', function (): void {
    $organisation = OrganisationTestHelper::create();

    foreach (['', '/taken/verwerking-vastleggen', '/naslag/registers'] as $path) {
        $this->get(manualUrl($organisation->slug, $path))
            ->assertRedirect();
    }
});

/**
 * Tenant resolution is the panel's, so the manual inherits it: the url carries
 * an organisation the user does not belong to and the request never reaches the
 * page. A 404 rather than a 403, which is the panel's own answer and the better
 * one - it does not confirm that the other organisation exists.
 */
it('does not let a user read the manual of another organisation', function (): void {
    $own = OrganisationTestHelper::create();
    $other = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($own);

    foreach (['', '/taken/verwerking-vastleggen', '/naslag/registers'] as $path) {
        $this->get(manualUrl($other->slug, $path))
            ->assertNotFound();
    }
});

it('finds tasks and topics through search', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(Handleiding::class)
        ->set('search', 'datalek')
        ->assertSee('Een datalek melden')
        ->assertSee('Datalekken');
});

it('reports when search finds nothing', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(Handleiding::class)
        ->set('search', 'ditbestaatniet')
        ->assertSee(__('manual.search_empty'));
});

it('clears the search', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(Handleiding::class)
        ->set('search', 'datalek')
        ->call('clearSearch')
        ->assertSet('search', '')
        ->assertSee(__('manual.tasks_heading'));
});

it('does not find wpg through search when the flag is off', function (): void {
    ConfigTestHelper::set('features.wpg', false);

    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(Handleiding::class)
        ->set('search', 'Wpg')
        ->assertSee(__('manual.search_empty'));
});

it('tells a user with the role that the task can be performed', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);
    RoleTestHelper::actAs([Role::INPUT_PROCESSOR]);

    $this
        ->get(manualUrl($organisation->slug, '/taken/verwerking-vastleggen'))
        ->assertOk()
        ->assertSee(__('manual.role_can_perform', [
            'role' => __('role.' . Role::INPUT_PROCESSOR->value),
        ]));
});

it('tells a read only role that it can only follow along', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);
    RoleTestHelper::actAs([Role::COUNSELOR]);

    $this
        ->get(manualUrl($organisation->slug, '/taken/verwerking-vastleggen'))
        ->assertOk()
        ->assertSee(__('manual.role_can_read', [
            'role' => __('role.' . Role::COUNSELOR->value),
        ]));
});

it('points a role the task is not for at the roles topic', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);
    RoleTestHelper::actAs([Role::FUNCTIONAL_MANAGER]);

    $this
        ->get(manualUrl($organisation->slug, '/taken/verwerking-vastleggen'))
        ->assertOk()
        ->assertSee(__('manual.role_cannot'))
        ->assertSee(manualUrl($organisation->slug, '/naslag/rollen'), escape: false);
});

it('flags only the tasks the current role is limited on, on the overview', function (): void {
    // An Invoerder performs some of the tasks and not others. Only the latter
    // carry a badge: marking the ones that are simply available says nothing,
    // and on an account holding every role it marked every card alike.
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);
    RoleTestHelper::actAs([Role::INPUT_PROCESSOR]);

    $this
        ->get(manualUrl($organisation->slug))
        ->assertOk()
        ->assertSee(__('manual.capability_none'))
        ->assertSee(__('manual.capability_read'));
});

it('leaves the badge off a task the current role simply performs', function (): void {
    // A Raadpleger reads along on every task, so nothing on the overview is
    // marked "Niet voor uw rol"; a Chief Privacy Officer performs nearly all of
    // them and should see an overview that is quiet rather than decorated.
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);
    RoleTestHelper::actAs([Role::CHIEF_PRIVACY_OFFICER]);

    $this
        ->get(manualUrl($organisation->slug, '/taken/verwerking-vastleggen'))
        ->assertOk()
        ->assertDontSee(__('manual.capability_read'))
        ->assertDontSee(__('manual.capability_none'));
});

it('links every task step to a topic that exists', function (): void {
    $manual = new Manual();

    foreach ($manual->tasks() as $task) {
        foreach ($task->topicIds() as $topicId) {
            expect($manual->topic($topicId))
                ->not->toBeNull(sprintf('task %s links to unknown topic %s', $task->id, $topicId));
        }
    }
});

it('gives every topic a unique slug', function (): void {
    $seen = [];

    foreach (ReferenceContent::chapters() as $chapter) {
        foreach ($chapter->topics as $topic) {
            expect($seen)->not->toHaveKey($topic->id);
            $seen[$topic->id] = true;
        }
    }

    expect(count($seen))->toBeGreaterThan(0);
});
