<?php

declare(strict_types=1);

namespace App\Filament\Pages\Manual;

use App\Manual\Task;
use App\Manual\Topic;

/**
 * The urls of the manual, in one place.
 *
 * A slug is the id the content model already gives a task or a topic, so the
 * address bar shows the same name the content is written under and there is no
 * second list of slugs to keep in step. The pages resolve the reverse direction
 * with the same id, which is why a link can never point at a page that does not
 * exist.
 */
final class ManualUrls
{
    public static function home(): string
    {
        return Handleiding::getUrl();
    }

    public static function task(Task $task): string
    {
        return HandleidingTaak::getUrl(['taak' => $task->id]);
    }

    public static function topic(Topic $topic): string
    {
        return HandleidingOnderwerp::getUrl(['onderwerp' => $topic->id]);
    }
}
