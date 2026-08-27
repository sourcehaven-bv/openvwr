<?php

declare(strict_types=1);

namespace App\Services\ApReport;

use App\Facades\DateFormat;
use Carbon\CarbonImmutable;

use function __;
use function array_filter;
use function array_values;
use function is_array;
use function sprintf;

/**
 * Shapes register values into the form the AP notification asks for: a date as
 * text, a yes/no, a checkbox list with its free text folded in.
 */
class ApAnswerValues
{
    /**
     * A checkbox list plus the free text behind its "Anders" option. The bare
     * option is replaced by the spelled-out one, so the AP form is not handed
     * both "Anders" and "Anders, namelijk: ..." for the same tick.
     *
     * @param array<string>|null $values
     *
     * @return array<int, string>
     */
    public function withOther(?array $values, ?string $other, string $option = 'Anders'): array
    {
        $answer = $values ?? [];
        if ($other === null) {
            return array_values($answer);
        }

        $answer = array_filter($answer, static function (string $value) use ($option): bool {
            return $value !== $option;
        });
        $answer[] = sprintf('%s, namelijk: %s', $option, $other);

        return array_values($answer);
    }

    /**
     * An answer plus the explanation the AP asks for alongside it.
     *
     * @param array<string>|string|null $value
     *
     * @return array<int, string>
     */
    public function withExplanation(array|string|null $value, ?string $explanation): array
    {
        $answer = is_array($value) ? $value : [$value];
        $answer[] = $explanation;

        $answer = array_filter($answer, static function (?string $item): bool {
            return $item !== null;
        });

        return array_values($answer);
    }

    /**
     * The AP asks for an exact number of data subjects, or a range if the exact
     * number is not known yet.
     */
    public function count(bool $known, ?int $exact, ?int $min, ?int $max): ?string
    {
        if ($known) {
            return $this->number($exact);
        }

        $from = $this->number($min);
        $to = $this->number($max);

        if ($from === null && $to === null) {
            return null;
        }

        return sprintf('%s - %s', $from ?? '?', $to ?? '?');
    }

    public function number(?int $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) $value;
    }

    public function date(?CarbonImmutable $date): ?string
    {
        if ($date === null) {
            return null;
        }

        return DateFormat::toDate($date);
    }

    public function boolean(bool $value): string
    {
        return $value ? __('general.yes') : __('general.no');
    }
}
