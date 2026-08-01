<?php

declare(strict_types=1);

namespace App\Services\Dpia;

use App\Enums\Dpia\PersonalDataType;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;

use function __;
use function e;
use function implode;
use function is_array;
use function is_string;
use function sprintf;
use function trim;

/**
 * Shows, in paragraaf 12, which gegevens from paragraaf 2 need a ground and
 * whether that ground has been given.
 *
 * The question itself is not repeated here. The classification already happened
 * in paragraaf 2 and the ground is recorded next to the gegeven it justifies,
 * so this paragraph reports rather than asks -- which is what keeps the two
 * answers from drifting apart.
 */
final class SpecialCategoriesSummary
{
    public static function render(Get $get): HtmlString
    {
        $personalData = $get('personalData');

        if (!is_array($personalData) || $personalData === []) {
            return self::notice('special_categories_no_personal_data', 'gray');
        }

        ['withGround' => $withGround, 'withoutGround' => $withoutGround] = self::classify($personalData);

        if ($withGround === [] && $withoutGround === []) {
            return self::notice('special_categories_none', 'gray');
        }

        $blocks = [];

        if ($withoutGround !== []) {
            $blocks[] = sprintf(
                '<p class="text-sm text-warning-600 dark:text-warning-400">%s</p><ul class="text-sm text-warning-600 dark:text-warning-400 list-disc ps-5">%s</ul>',
                e(__('dpia_record.special_categories_missing_ground')),
                self::listItems($withoutGround),
            );
        }

        if ($withGround !== []) {
            $blocks[] = sprintf(
                '<p class="text-sm text-gray-500">%s</p><ul class="text-sm text-gray-500 list-disc ps-5">%s</ul>',
                e(__('dpia_record.special_categories_with_ground')),
                self::listItems($withGround),
            );
        }

        return new HtmlString('<div class="space-y-3">' . implode('', $blocks) . '</div>');
    }

    /**
     * Splits the gegevens that need a ground into those that have one and
     * those that do not.
     *
     * @param array<mixed> $personalData
     *
     * @return array{withGround: array<int, string>, withoutGround: array<int, string>}
     */
    private static function classify(array $personalData): array
    {
        $withGround = [];
        $withoutGround = [];

        foreach ($personalData as $item) {
            if (!is_array($item)) {
                continue;
            }

            $type = self::typeFrom($item['type'] ?? null);

            if (!$type instanceof PersonalDataType || !$type->requiresExceptionGround()) {
                continue;
            }

            if (self::filled($item['exception_ground'] ?? null)) {
                $withGround[] = self::labelFor($item, $type);

                continue;
            }

            $withoutGround[] = self::labelFor($item, $type);
        }

        return ['withGround' => $withGround, 'withoutGround' => $withoutGround];
    }

    /**
     * @param array<mixed> $item
     */
    private static function labelFor(array $item, PersonalDataType $type): string
    {
        $description = $item['description'] ?? null;
        $description = is_string($description) && trim($description) !== ''
            ? trim($description)
            : __('dpia_quality.unnamed');

        return sprintf('%s (%s)', $description, $type->label());
    }

    /**
     * @param array<int, string> $labels
     */
    private static function listItems(array $labels): string
    {
        $items = '';

        foreach ($labels as $label) {
            $items .= '<li>' . e($label) . '</li>';
        }

        return $items;
    }

    private static function notice(string $key, string $tone): HtmlString
    {
        $class = $tone === 'gray' ? 'text-gray-500' : 'text-warning-600 dark:text-warning-400';

        return new HtmlString(
            sprintf('<p class="text-sm %s">%s</p>', $class, e(__('dpia_record.' . $key))),
        );
    }

    private static function typeFrom(mixed $type): ?PersonalDataType
    {
        if ($type instanceof PersonalDataType) {
            return $type;
        }

        if (!is_string($type) || $type === '') {
            return null;
        }

        return PersonalDataType::tryFrom($type);
    }

    private static function filled(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}
