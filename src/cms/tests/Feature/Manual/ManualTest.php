<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Manual\Chapter;
use App\Manual\FeatureGate;
use App\Manual\Manual;
use App\Manual\Step;
use App\Manual\Task;
use App\Manual\TaskCapability;
use App\Manual\TaskRoles;
use App\Manual\Topic;
use Tests\Helpers\ConfigTestHelper;

it('keeps every chapter when both flags are on', function (): void {
    ConfigTestHelper::set('features.wpg', true);
    ConfigTestHelper::set('features.publishing', true);

    expect((new Manual())->chapters())->toHaveCount(7);
});

it('drops a gated topic when its flag is off', function (): void {
    ConfigTestHelper::set('features.wpg', false);

    $manual = new Manual();

    expect($manual->topic('wpg-register'))->toBeNull()
        ->and($manual->topic('verwerkingsregisters'))->not->toBeNull();
});

it('drops a gated task when its flag is off', function (): void {
    ConfigTestHelper::set('features.publishing', false);

    $ids = array_map(
        static fn (Task $task): string => $task->id,
        (new Manual())->tasks(),
    );

    expect($ids)->not->toContain('verwerking-publiceren');
});

it('keeps at least one ungated task in every group, whatever the flags', function (): void {
    // Manual::taskGroups() renders a group unconditionally, which is only safe
    // while no group can be emptied by switching a flag off.
    ConfigTestHelper::set('features.wpg', false);
    ConfigTestHelper::set('features.publishing', false);

    foreach ((new Manual())->taskGroups() as $group) {
        expect($group['tasks'])->not->toBeEmpty($group['title']);
    }
});

it('keeps at least one ungated topic in every chapter, whatever the flags', function (): void {
    // Same invariant for the reference layer: Manual::chapters() renders every
    // chapter, so none of them may end up without topics.
    ConfigTestHelper::set('features.wpg', false);
    ConfigTestHelper::set('features.publishing', false);

    foreach ((new Manual())->chapters() as $chapter) {
        expect($chapter->topics)->not->toBeEmpty($chapter->title);
    }
});

it('finds the chapter a topic belongs to', function (): void {
    $manual = new Manual();
    $topic = $manual->topic('export');

    expect($topic)->not->toBeNull()
        ->and($manual->chapterOf($topic)?->id)->toBe('overige-functies');
});

it('returns no chapter for a topic that is not in the manual', function (): void {
    $stranger = new Topic(id: 'onbekend', title: 'Onbekend', body: 'x');

    expect((new Manual())->chapterOf($stranger))->toBeNull();
});

it('computes backlinks from the task definitions', function (): void {
    $manual = new Manual();
    $topic = $manual->topic('export');

    expect($topic)->not->toBeNull();

    $ids = array_map(
        static fn (Task $task): string => $task->id,
        $manual->tasksUsing($topic),
    );

    expect($ids)->toContain('overzicht-opvragen');
});

it('reports a topic that no task uses', function (): void {
    $manual = new Manual();
    $topic = $manual->topic('over-openvwr');

    expect($topic)->not->toBeNull()
        ->and($manual->tasksUsing($topic))->toBeEmpty();
});

it('lists the topics of a task once, in order of first use', function (): void {
    $task = new Task(
        id: 'x',
        group: 'g',
        title: 't',
        summary: 's',
        intro: 'i',
        steps: [
            new Step('a', 'b', ['export', 'export']),
            new Step('c', 'd', ['import']),
        ],
        roles: new TaskRoles(performers: []),
    );

    $ids = array_map(
        static fn (Topic $topic): string => $topic->id,
        (new Manual())->topicsFor($task),
    );

    expect($ids)->toBe(['export', 'import']);
});

it('skips a topic of a task that a flag has hidden', function (): void {
    ConfigTestHelper::set('features.wpg', false);

    $task = new Task(
        id: 'x',
        group: 'g',
        title: 't',
        summary: 's',
        intro: 'i',
        steps: [new Step('a', 'b', ['wpg-register', 'export'])],
        roles: new TaskRoles(performers: []),
    );

    $ids = array_map(
        static fn (Topic $topic): string => $topic->id,
        (new Manual())->topicsFor($task),
    );

    expect($ids)->toBe(['export']);
});

it('returns nothing for an empty search term', function (): void {
    $results = (new Manual())->search('   ');

    expect($results['tasks'])->toBeEmpty()
        ->and($results['topics'])->toBeEmpty();
});

it('searches both layers, case insensitively', function (): void {
    $results = (new Manual())->search('DATALEK');

    expect(count($results['tasks']))->toBeGreaterThan(0)
        ->and(count($results['topics']))->toBeGreaterThan(0);
});

it('does not search content that a flag has hidden', function (): void {
    ConfigTestHelper::set('features.publishing', false);

    $results = (new Manual())->search('openbare website');

    expect($results['tasks'])->toBeEmpty()
        ->and($results['topics'])->toBeEmpty();
});

it('tells which roles can perform, read along, or neither', function (): void {
    $task = new Task(
        id: 'x',
        group: 'g',
        title: 't',
        summary: 's',
        intro: 'i',
        steps: [],
        roles: new TaskRoles(
            performers: [Role::INPUT_PROCESSOR],
            readers: [Role::COUNSELOR],
        ),
    );

    expect($task->capabilityFor([Role::INPUT_PROCESSOR]))->toBe(TaskCapability::PERFORM)
        ->and($task->capabilityFor([Role::COUNSELOR]))->toBe(TaskCapability::READ)
        ->and($task->capabilityFor([Role::MANDATE_HOLDER]))->toBe(TaskCapability::NONE)
        ->and($task->capabilityFor([]))->toBe(TaskCapability::NONE);
});

it('prefers performing over reading when a user holds both roles', function (): void {
    $task = new Task(
        id: 'x',
        group: 'g',
        title: 't',
        summary: 's',
        intro: 'i',
        steps: [],
        roles: new TaskRoles(
            performers: [Role::INPUT_PROCESSOR],
            readers: [Role::COUNSELOR],
        ),
    );

    expect($task->capabilityFor([Role::COUNSELOR, Role::INPUT_PROCESSOR]))
        ->toBe(TaskCapability::PERFORM);
});

it('treats content without a gate as always visible', function (): void {
    $topic = new Topic(id: 'x', title: 't', body: 'b');

    expect($topic->isVisible())->toBeTrue();
});

it('follows the feature flags for a gated topic', function (): void {
    $topic = new Topic(id: 'x', title: 't', body: 'b', gate: FeatureGate::WPG);

    ConfigTestHelper::set('features.wpg', true);
    expect($topic->isVisible())->toBeTrue();

    ConfigTestHelper::set('features.wpg', false);
    expect($topic->isVisible())->toBeFalse();
});

it('follows the publishing flag for a gated task', function (): void {
    $task = new Task(
        id: 'x',
        group: 'g',
        title: 't',
        summary: 's',
        intro: 'i',
        steps: [],
        roles: new TaskRoles(performers: []),
        gate: FeatureGate::PUBLISHING,
    );

    ConfigTestHelper::set('features.publishing', true);
    expect($task->isVisible())->toBeTrue();

    ConfigTestHelper::set('features.publishing', false);
    expect($task->isVisible())->toBeFalse();
});

it('reports no visible topics when they are all hidden', function (): void {
    ConfigTestHelper::set('features.wpg', false);

    $chapter = new Chapter(
        id: 'c',
        title: 'C',
        summary: 's',
        topics: [new Topic(id: 'x', title: 't', body: 'b', gate: FeatureGate::WPG)],
    );

    expect($chapter->visibleTopics())->toBeEmpty();
});

it('renders a topic body to html', function (): void {
    $topic = new Topic(id: 'x', title: 't', body: '**vet**');

    expect($topic->html())->toContain('<strong>vet</strong>');
});

it('includes the title and body in the search text of a topic', function (): void {
    $topic = new Topic(id: 'x', title: 'Titel', body: 'Body');

    expect($topic->searchText())->toContain('Titel')->toContain('Body');
});

it('includes the steps in the search text of a task', function (): void {
    $task = new Task(
        id: 'x',
        group: 'g',
        title: 'Titel',
        summary: 'Samenvatting',
        intro: 'Inleiding',
        steps: [new Step('Stap', 'Uitleg')],
        roles: new TaskRoles(performers: []),
    );

    expect($task->searchText())
        ->toContain('Titel')
        ->toContain('Samenvatting')
        ->toContain('Inleiding')
        ->toContain('Stap')
        ->toContain('Uitleg');
});

it('defaults a task with no reader roles to an empty reader list', function (): void {
    $roles = new TaskRoles(performers: [Role::INPUT_PROCESSOR]);

    expect($roles->readers)->toBe([])
        ->and($roles->capabilityFor([Role::COUNSELOR]))->toBe(TaskCapability::NONE)
        ->and($roles->capabilityFor([Role::INPUT_PROCESSOR]))->toBe(TaskCapability::PERFORM);
});

/**
 * The gates of a task and of the topics explaining it have to line up.
 *
 * HandleidingTaak renders a step's reference links without a fallback for a
 * topic the manual does not return, which is only safe while a visible task
 * never points at hidden content. If a future task gains a step that links to a
 * topic behind a different flag, this fails rather than the page.
 */
it('never lets a visible task link to a topic a flag has hidden', function (): void {
    foreach ([[false, false], [true, false], [false, true], [true, true]] as [$wpg, $publishing]) {
        ConfigTestHelper::set('features.wpg', $wpg);
        ConfigTestHelper::set('features.publishing', $publishing);

        $manual = new Manual();

        foreach ($manual->tasks() as $task) {
            foreach ($task->topicIds() as $topicId) {
                expect($manual->topic($topicId))->not->toBeNull(sprintf(
                    'task %s links to %s, which is hidden with wpg=%d publishing=%d',
                    $task->id,
                    $topicId,
                    (int) $wpg,
                    (int) $publishing,
                ));
            }
        }
    }
});
