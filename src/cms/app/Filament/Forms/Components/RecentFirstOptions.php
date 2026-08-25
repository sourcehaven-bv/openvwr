<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use function __;
use function is_string;

/**
 * The options a relation picker offers before the user types: the most
 * recently edited records, under a heading that says so.
 *
 * The registers these pickers draw from are usually small, so the dropdown is
 * preloaded rather than left empty until the user starts typing. Alphabetical
 * order would be the obvious choice but is the wrong one here: any list long
 * enough to need a limit would then show the same handful of A-names forever,
 * and the rest could never be reached by scrolling. The record someone wants
 * to link is far more often one they were just working on.
 *
 * That ordering looks arbitrary without an explanation, so the options are
 * wrapped in a single named group. Filament turns a nested options array into
 * a choices.js option group, which renders the heading above the list.
 * Anything not in the list is reached by typing; search is unaffected.
 */
class RecentFirstOptions
{
    /**
     * How many records the closed-then-opened dropdown offers. A dropdown is
     * there to be skimmed, not paged through.
     */
    public const RECENT_COUNT = 10;

    /**
     * Wraps the options in the "recently edited" group.
     *
     * @param array<string, string> $recent options keyed by id, most recently edited first
     *
     * @return array<string, array<string, string>> the group keyed by its translated heading
     */
    public static function group(array $recent): array
    {
        return [
            __('general.picker_recent') => $recent,
        ];
    }

    /**
     * Narrows plucked id/label pairs to the plain strings an option list needs.
     * The ids are uuids, which PHP stringifies as soon as they are used as an
     * array key; the option values have to be those same strings, because that
     * is what the client sends back.
     *
     * @param array<mixed, mixed> $options
     *
     * @return array<string, string>
     */
    public static function fromPlucked(array $options): array
    {
        $result = [];
        foreach ($options as $id => $label) {
            if (!is_string($label)) {
                continue;
            }

            $result[(string) $id] = $label;
        }

        return $result;
    }
}
