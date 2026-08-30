<?php

declare(strict_types=1);

namespace App\Services\Dpia;

use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

use function __;
use function e;
use function implode;
use function is_array;
use function is_string;
use function sprintf;
use function trim;

/**
 * Inline aandachtspunten shown inside a paragraph while it is being filled in.
 *
 * The same ground as {@see DpiaQualityChecker} covers, but read from the live
 * form state instead of the saved record, so the notice appears where the
 * problem can be fixed rather than only after saving. Nothing here blocks
 * anything: it is a remark next to the field, not a validation error.
 */
final class DpiaSectionNotice
{
    /**
     * Paragraaf 16: risks that no measure addresses yet.
     */
    public static function risks(Get $get): ?HtmlString
    {
        $risks = self::rows($get('risks'));
        $measures = self::rows($get('measures'));

        if ($risks === []) {
            return null;
        }

        $addressed = self::addressedRiskKeys($measures);
        $unaddressed = [];

        foreach ($risks as $key => $risk) {
            if (isset($addressed[$key])) {
                continue;
            }

            $title = self::titleOf($risk);

            if ($title === null) {
                continue;
            }

            $unaddressed[] = $title;
        }

        if ($unaddressed === []) {
            return null;
        }

        return self::warning(
            __('dpia_quality.section_risks_without_measure'),
            $unaddressed,
        );
    }

    /**
     * The risk keys that at least one measure points at.
     *
     * @param array<string, array<mixed>> $measures
     *
     * @return array<string, true>
     */
    private static function addressedRiskKeys(array $measures): array
    {
        $addressed = [];

        foreach ($measures as $measure) {
            $selected = $measure['risks'] ?? null;

            if (!is_array($selected)) {
                continue;
            }

            foreach ($selected as $riskKey) {
                if (is_string($riskKey)) {
                    $addressed[$riskKey] = true;
                }
            }
        }

        return $addressed;
    }

    /**
     * The trimmed description of a repeater row, or null when it has none.
     *
     * @param array<mixed> $row
     */
    private static function describedAs(array $row): ?string
    {
        $description = $row['description'] ?? null;

        if (!is_string($description) || trim($description) === '') {
            return null;
        }

        return trim($description);
    }

    /**
     * The trimmed title of a repeater row, or null when it has none.
     *
     * @param array<mixed> $row
     */
    private static function titleOf(array $row): ?string
    {
        $title = $row['title'] ?? null;

        if (!is_string($title) || trim($title) === '') {
            return null;
        }

        return trim($title);
    }

    /**
     * Paragraaf 17: a high residual risk means artikel 36 comes into play.
     */
    public static function measures(Get $get): ?HtmlString
    {
        $measures = self::rows($get('measures'));
        $unlinked = self::measuresWithoutRisk($measures);
        $hasHighResidualRisk = self::hasHighResidualRisk($measures);
        $blocks = [];

        if ($unlinked !== []) {
            $blocks[] = self::block(__('dpia_quality.section_measures_without_risk'), $unlinked);
        }

        if ($hasHighResidualRisk) {
            $blocks[] = sprintf(
                '<p class="text-sm text-warning-600 dark:text-warning-400">%s</p>',
                e(__('dpia_quality.section_high_residual_risk')),
            );
        }

        if ($blocks === []) {
            return null;
        }

        return new HtmlString('<div class="space-y-2">' . implode('', $blocks) . '</div>');
    }

    /**
     * The measures that do not point at any risk yet.
     *
     * @param array<string, array<mixed>> $measures
     *
     * @return array<int, string>
     */
    private static function measuresWithoutRisk(array $measures): array
    {
        $unlinked = [];

        foreach ($measures as $measure) {
            $selected = $measure['risks'] ?? null;
            $description = self::describedAs($measure);

            if ($description !== null && (!is_array($selected) || $selected === [])) {
                $unlinked[] = $description;
            }
        }

        return $unlinked;
    }

    /**
     * @param array<string, array<mixed>> $measures
     */
    private static function hasHighResidualRisk(array $measures): bool
    {
        foreach ($measures as $measure) {
            if (($measure['residual_level'] ?? null) === 'hoog') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, string> $items
     */
    private static function warning(string $message, array $items): HtmlString
    {
        return new HtmlString('<div class="space-y-2">' . self::block($message, $items) . '</div>');
    }

    /**
     * @param array<int, string> $items
     */
    private static function block(string $message, array $items): string
    {
        $list = '';

        foreach ($items as $item) {
            $list .= '<li>' . e($item) . '</li>';
        }

        return sprintf(
            '<p class="text-sm text-warning-600 dark:text-warning-400">%s</p>'
            . '<ul class="text-sm text-warning-600 dark:text-warning-400 list-disc ps-5">%s</ul>',
            e($message),
            $list,
        );
    }

    /**
     * @return array<string, array<mixed>>
     */
    private static function rows(mixed $state): array
    {
        if (!is_array($state)) {
            return [];
        }

        $rows = [];

        foreach ($state as $key => $row) {
            if (is_string($key) && is_array($row)) {
                $rows[$key] = $row;
            }
        }

        return $rows;
    }
}
