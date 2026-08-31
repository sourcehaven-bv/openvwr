<?php

declare(strict_types=1);

namespace App\Manual;

use App\Manual\Content\ReferenceContent;
use App\Manual\Content\TaskContent;

use function array_filter;
use function array_key_exists;
use function array_values;
use function in_array;
use function mb_strtolower;
use function str_contains;
use function trim;

/**
 * The manual: tasks on top, reference underneath, one source of truth.
 *
 * Everything the page, the navigation, the backlinks and the search show comes
 * from here, and everything here is already filtered on the feature flags. A
 * topic or task whose flag is off does not exist as far as the rest of the
 * application is concerned, so it cannot be reached by scrolling, by anchor or
 * by search.
 */
class Manual
{
    /** @var ?array<Chapter> */
    private ?array $chapters = null;

    /** @var ?array<Task> */
    private ?array $tasks = null;

    /**
     * The chapters whose feature flag is on, with their hidden topics removed.
     * A chapter that keeps no topics at all disappears with them.
     *
     * @return array<Chapter>
     */
    public function chapters(): array
    {
        if ($this->chapters !== null) {
            return $this->chapters;
        }

        $chapters = [];

        foreach (ReferenceContent::chapters() as $chapter) {
            $topics = $chapter->visibleTopics();

            if ($topics === []) {
                continue;
            }

            $chapters[] = new Chapter(id: $chapter->id, title: $chapter->title, summary: $chapter->summary, topics: $topics);
        }

        return $this->chapters = $chapters;
    }

    /**
     * @return array<Task>
     */
    public function tasks(): array
    {
        if ($this->tasks !== null) {
            return $this->tasks;
        }

        return $this->tasks = array_values(array_filter(
            TaskContent::tasks(),
            static fn (Task $task): bool => $task->isVisible(),
        ));
    }

    /**
     * The visible tasks by group, in the order the groups are defined. Groups
     * that keep no tasks are left out.
     *
     * @return array<array{title: string, summary: string, tasks: array<Task>}>
     */
    public function taskGroups(): array
    {
        $groups = [];

        foreach (TaskContent::groups() as $title => $summary) {
            $tasks = array_values(array_filter(
                $this->tasks(),
                static fn (Task $task): bool => $task->group === $title,
            ));

            if ($tasks === []) {
                continue;
            }

            $groups[] = ['title' => $title, 'summary' => $summary, 'tasks' => $tasks];
        }

        return $groups;
    }

    /**
     * @return array<Topic>
     */
    public function topics(): array
    {
        $topics = [];

        foreach ($this->chapters() as $chapter) {
            foreach ($chapter->topics as $topic) {
                $topics[] = $topic;
            }
        }

        return $topics;
    }

    public function topic(string $id): ?Topic
    {
        foreach ($this->topics() as $topic) {
            if ($topic->id === $id) {
                return $topic;
            }
        }

        return null;
    }

    public function chapterOf(Topic $topic): ?Chapter
    {
        foreach ($this->chapters() as $chapter) {
            if (in_array($topic, $chapter->topics, true)) {
                return $chapter;
            }
        }

        return null;
    }

    /**
     * The tasks that link to a topic. This is what proves the manual has a
     * single source of truth: the backlinks are computed from the task
     * definitions, so a topic can never claim to be used by a task that does
     * not in fact refer to it.
     *
     * @return array<Task>
     */
    public function tasksUsing(Topic $topic): array
    {
        return array_values(array_filter(
            $this->tasks(),
            static fn (Task $task): bool => in_array($topic->id, $task->topicIds(), true),
        ));
    }

    /**
     * The reference topics a task links to, in the order they are first used,
     * skipping any that a feature flag has hidden.
     *
     * @return array<Topic>
     */
    public function topicsFor(Task $task): array
    {
        $topics = [];
        $seen = [];

        foreach ($task->topicIds() as $id) {
            if (array_key_exists($id, $seen)) {
                continue;
            }

            $seen[$id] = true;
            $topic = $this->topic($id);

            if ($topic === null) {
                continue;
            }

            $topics[] = $topic;
        }

        return $topics;
    }

    /**
     * Search across both layers. Only visible content is searched, so a term
     * that appears solely in content behind a switched-off flag finds nothing.
     *
     * @return array{tasks: array<Task>, topics: array<Topic>}
     */
    public function search(string $term): array
    {
        $term = mb_strtolower(trim($term));

        if ($term === '') {
            return ['tasks' => [], 'topics' => []];
        }

        $tasks = array_values(array_filter(
            $this->tasks(),
            static fn (Task $task): bool => str_contains(mb_strtolower($task->searchText()), $term),
        ));

        $topics = array_values(array_filter(
            $this->topics(),
            static fn (Topic $topic): bool => str_contains(
                mb_strtolower($topic->searchText()),
                $term,
            ),
        ));

        return ['tasks' => $tasks, 'topics' => $topics];
    }
}
