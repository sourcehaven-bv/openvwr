<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Filament\Pages\Handleiding;
use App\Manual\Content\ReferenceContent;
use App\Manual\Manual;
use App\Models\Organisation;
use App\Models\Principal;
use App\Models\User;
use App\Services\Authentication\AuthenticationStrategy;
use App\Services\AuthenticationService;
use App\Services\AuthorizationService;
use Tests\Helpers\ConfigTestHelper;
use Tests\Helpers\Model\OrganisationTestHelper;

use function count;

/**
 * Act as a user holding exactly these roles.
 *
 * AuthorizationService is readonly and therefore cannot be mocked, so this
 * swaps in a real one built on a strategy that reports fixed roles. That keeps
 * the real permission logic in the test rather than stubbing it out.
 *
 * @param array<Role> $roles
 */
function actingWithRoles(array $roles): void
{
    $strategy = new class ($roles) implements AuthenticationStrategy {
        /** @param array<Role> $roles */
        public function __construct(private array $roles)
        {
        }

        public function user(): User
        {
            throw new RuntimeException('not used');
        }

        public function organisation(): Organisation
        {
            throw new RuntimeException('not used');
        }

        public function principal(): Principal
        {
            return new Principal($this->roles);
        }
    };

    app()->instance(
        AuthorizationService::class,
        new AuthorizationService(new AuthenticationService($strategy), []),
    );
}

it('loads the page', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(sprintf('%s/handleiding', $organisation->slug))
        ->assertOk()
        ->assertSee(__('general.manual'))
        ->assertSee(__('manual.tasks_heading'))
        ->assertSee(__('manual.reference_heading'));
});

it('shows every chapter and topic', function (): void {
    $organisation = OrganisationTestHelper::create();

    $response = $this->asFilamentOrganisationUser($organisation)
        ->get(sprintf('%s/handleiding', $organisation->slug))
        ->assertOk();

    foreach ((new Manual())->chapters() as $chapter) {
        $response->assertSee($chapter->title, escape: false);

        foreach ($chapter->topics as $topic) {
            $response->assertSee($topic->title, escape: false);
            $response->assertSee(sprintf('id="%s"', $topic->id), escape: false);
        }
    }
});

it('shows every task with a link to the reference', function (): void {
    $organisation = OrganisationTestHelper::create();

    $response = $this->asFilamentOrganisationUser($organisation)
        ->get(sprintf('%s/handleiding', $organisation->slug))
        ->assertOk();

    foreach ((new Manual())->tasks() as $task) {
        $response->assertSee($task->title, escape: false);
        $response->assertSee(sprintf('id="taak-%s"', $task->id), escape: false);
    }
});

it('shows the images from the public directory', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(sprintf('%s/handleiding', $organisation->slug))
        ->assertOk()
        ->assertSee('/handleiding/01_welkom/01_login.png', escape: false);
});

it('renders hint and warning callouts', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(sprintf('%s/handleiding', $organisation->slug))
        ->assertOk()
        ->assertSee('manual-callout--hint', escape: false)
        ->assertSee('manual-callout--warning', escape: false);
});

it('hides wpg content when the flag is off', function (): void {
    ConfigTestHelper::set('features.wpg', false);

    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(sprintf('%s/handleiding', $organisation->slug))
        ->assertOk()
        ->assertDontSee('WPG Verantwoordelijke Verwerkingen')
        ->assertDontSee('Een Wpg-verwerking vastleggen');
});

it('hides publishing content when the flag is off', function (): void {
    ConfigTestHelper::set('features.publishing', false);

    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(sprintf('%s/handleiding', $organisation->slug))
        ->assertOk()
        ->assertDontSee('Een verwerking publiceren')
        ->assertDontSee('openbare website');
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
    actingWithRoles([Role::INPUT_PROCESSOR]);

    $this
        ->get(sprintf('%s/handleiding', $organisation->slug))
        ->assertOk()
        ->assertSee(__('manual.capability_perform'));
});

it('tells a read only role that it can only follow along', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);
    actingWithRoles([Role::COUNSELOR]);

    $this
        ->get(sprintf('%s/handleiding', $organisation->slug))
        ->assertOk()
        ->assertSee(__('manual.capability_read'));
});

it('tells a role without any of the tasks that they are not for it', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);
    actingWithRoles([Role::FUNCTIONAL_MANAGER]);

    $this
        ->get(sprintf('%s/handleiding', $organisation->slug))
        ->assertOk()
        ->assertSee(__('manual.capability_none'));
});

it('shows the tasks a topic is used in, and says so when there are none', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(sprintf('%s/handleiding', $organisation->slug))
        ->assertOk()
        ->assertSee(__('manual.used_in_tasks'))
        ->assertSee(__('manual.used_in_no_tasks'));
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

it('gives every topic a unique anchor', function (): void {
    $seen = [];

    foreach (ReferenceContent::chapters() as $chapter) {
        foreach ($chapter->topics as $topic) {
            expect($seen)->not->toHaveKey($topic->id);
            $seen[$topic->id] = true;
        }
    }

    expect(count($seen))->toBeGreaterThan(0);
});
