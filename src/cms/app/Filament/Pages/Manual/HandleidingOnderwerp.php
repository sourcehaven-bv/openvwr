<?php

declare(strict_types=1);

namespace App\Filament\Pages\Manual;

use App\Manual\Chapter;
use App\Manual\Task;
use App\Manual\Topic;
use Filament\Panel;

use function abort;

/**
 * One reference topic, on its own page.
 *
 * Same gating as the task page: the manual has already dropped every topic
 * behind a switched-off flag, so such a topic does not resolve and the url
 * gives a 404 rather than the topic being merely absent from the menu.
 */
class HandleidingOnderwerp extends ManualDetailPage
{
    protected static ?string $slug = 'handleiding/naslag';
    protected string $view = 'filament.manual.onderwerp';

    public string $onderwerp = '';

    private Topic $topic;

    public static function getRoutePath(Panel $panel): string
    {
        return '/' . static::getSlug() . '/{onderwerp}';
    }

    public function mount(string $onderwerp): void
    {
        $topic = $this->manual()->topic($onderwerp);

        if ($topic === null) {
            abort(404);
        }

        $this->onderwerp = $onderwerp;
        $this->topic = $topic;
    }

    public function topic(): Topic
    {
        return $this->topic;
    }

    public function chapter(): ?Chapter
    {
        return $this->manual()->chapterOf($this->topic);
    }

    /**
     * The tasks that link here, which is what makes the reference layer worth
     * reading on its own: a topic says which work it belongs to.
     *
     * @return array<Task>
     */
    public function usedIn(): array
    {
        return $this->manual()->tasksUsing($this->topic);
    }
}
