<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Tag;
use App\Support\LabelColorAssigner;

class TagObserver
{
    public function __construct(private readonly LabelColorAssigner $labelColorAssigner)
    {
    }

    /**
     * Give a new label a colour when none was chosen.
     *
     * On the model rather than in the form, because labels are created from
     * more than one place: the label screen, and the inline "add label" in the
     * tag picker on a processing record. An assignment in either form would
     * miss the other.
     */
    public function creating(Tag $tag): void
    {
        if ($tag->color !== null) {
            return;
        }

        $tag->color = $this->labelColorAssigner->assign($tag);
    }
}
