<?php

declare(strict_types=1);

namespace App\Filament\Pages\Manual;

use App\Manual\Manual;
use App\Manual\Task;
use App\Manual\TaskCapability;
use App\Manual\Topic;
use Filament\Panel;
use Webmozart\Assert\Assert;

use function abort;
use function array_map;

/**
 * One task, on its own page.
 *
 * The task id from the url is looked up in the manual, which has already
 * dropped everything behind a switched-off feature flag. A task the tenant does
 * not have therefore does not resolve and the page is a 404: the flag is
 * enforced on the url, not merely in the menu.
 */
class HandleidingTaak extends ManualDetailPage
{
    protected static ?string $slug = 'handleiding/taken';
    protected string $view = 'filament.manual.taak';

    public string $taak = '';

    private Task $task;

    public static function getRoutePath(Panel $panel): string
    {
        return '/' . static::getSlug() . '/{taak}';
    }

    public function mount(string $taak): void
    {
        $task = $this->findTask($this->manual(), $taak);

        if ($task === null) {
            abort(404);
        }

        $this->taak = $taak;
        $this->task = $task;
    }

    public function task(): Task
    {
        return $this->task;
    }

    public function capability(): TaskCapability
    {
        return $this->capabilityFor($this->task);
    }

    /**
     * The reference topics a step links to.
     *
     * There is no filtering here, and deliberately no branch for a topic the
     * manual does not return. A visible task never links to a hidden topic: the
     * gates line up, because a task and the topics that explain it depend on
     * the same feature. ManualTest holds that invariant down, so a null here
     * would be a content bug and the assertion is the right way to hear about
     * it - quietly dropping the link would leave a step pointing at nothing.
     *
     * @param array<non-empty-string> $topicIds
     *
     * @return array<Topic>
     */
    public function topicsOf(array $topicIds): array
    {
        return array_map(
            function (string $topicId): Topic {
                $topic = $this->manual()->topic($topicId);
                Assert::isInstanceOf($topic, Topic::class);

                return $topic;
            },
            $topicIds,
        );
    }

    private function findTask(Manual $manual, string $id): ?Task
    {
        foreach ($manual->tasks() as $task) {
            if ($task->id === $id) {
                return $task;
            }
        }

        return null;
    }
}
