<?php

declare(strict_types=1);

namespace App\Manual\Content;

use App\Manual\Chapter;
use App\Manual\Content\Chapters\Beheer;
use App\Manual\Content\Chapters\Dpia;
use App\Manual\Content\Chapters\Goedkeuringsproces;
use App\Manual\Content\Chapters\Labels;
use App\Manual\Content\Chapters\OverigeFuncties;
use App\Manual\Content\Chapters\Registers;
use App\Manual\Content\Chapters\RollenEnRechten;
use App\Manual\Content\Chapters\Welkom;

/**
 * The reference layer: the canonical text of the manual.
 *
 * This is the single source of truth. Every explanation is written exactly
 * once, in one of the chapter classes below, and the tasks in TaskContent link
 * to it. Migrated from the seven markdown chapters that used to be built into a
 * pdf; the LaTeX constructs of that build (\label, \ref, \textcolor, \newpage)
 * have become anchors, markdown links, styled status markers and topic
 * boundaries.
 *
 * One chapter per class, in reading order.
 */
final class ReferenceContent
{
    /**
     * @return array<Chapter>
     */
    public static function chapters(): array
    {
        return [
            Welkom::chapter(),
            Registers::chapter(),
            Dpia::chapter(),
            Goedkeuringsproces::chapter(),
            Beheer::chapter(),
            OverigeFuncties::chapter(),
            Labels::chapter(),
            RollenEnRechten::chapter(),
        ];
    }
}
