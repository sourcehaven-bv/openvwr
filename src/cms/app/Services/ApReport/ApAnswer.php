<?php

declare(strict_types=1);

namespace App\Services\ApReport;

use function array_unique;
use function array_values;
use function count;
use function is_array;
use function trim;

/**
 * One answer in the AP preparation: the question it belongs to, the value (if
 * any) and where that value came from.
 */
class ApAnswer
{
    /**
     * @param array<int, string> $values
     * @param array<int, string> $origins human-readable sources, e.g. the name of
     *                                    the processing record a value was derived from
     * @param array<int, string> $hints what the linked content mentions, as a pointer
     *                                  for a question the officer has to answer themselves
     */
    private function __construct(
        public readonly string $number,
        public readonly string $label,
        public readonly array $values,
        public readonly AnswerSource $source,
        public readonly array $origins = [],
        public readonly array $hints = [],
    ) {
    }

    /**
     * @param array<int, string>|string|null $value
     */
    public static function recorded(string $number, string $label, array|string|null $value): self
    {
        $values = self::normalise($value);

        // An empty answer to a question the register does have a field for is
        // still a gap the officer must close before filing.
        if ($values === []) {
            return self::missing($number, $label);
        }

        return new self($number, $label, $values, AnswerSource::RECORDED);
    }

    /**
     * @param array<int, string>|string|null $value
     * @param array<int, string> $origins
     */
    public static function derived(string $number, string $label, array|string|null $value, array $origins): self
    {
        $values = self::normalise($value);

        if ($values === []) {
            return self::missing($number, $label);
        }

        return new self($number, $label, $values, AnswerSource::DERIVED, $origins);
    }

    /**
     * A question the register holds a field for, left empty. What the linked
     * processing records mention is passed along as a hint: it says what may be
     * relevant, so the officer can record on the breach what actually leaked
     * rather than have the preparation guess it for them.
     *
     * @param array<int, string>|string|null $value
     * @param array<int, string> $hints
     * @param array<int, string> $origins
     */
    public static function recordedWithHints(
        string $number,
        string $label,
        array|string|null $value,
        array $hints,
        array $origins,
    ): self {
        $values = self::normalise($value);

        if ($values !== []) {
            return new self($number, $label, $values, AnswerSource::RECORDED);
        }

        return new self($number, $label, [], AnswerSource::MISSING, $origins, self::normalise($hints));
    }

    public static function missing(string $number, string $label): self
    {
        return new self($number, $label, [], AnswerSource::MISSING);
    }

    public function isMissing(): bool
    {
        return $this->source === AnswerSource::MISSING;
    }

    public function needsConfirmation(): bool
    {
        return $this->source === AnswerSource::DERIVED;
    }

    public function isMultiValued(): bool
    {
        return count($this->values) > 1;
    }

    /**
     * @param array<int, string>|string|null $value
     *
     * @return array<int, string>
     */
    private static function normalise(array|string|null $value): array
    {
        $values = is_array($value) ? $value : [$value];

        $normalised = [];
        foreach ($values as $item) {
            if ($item === null) {
                continue;
            }

            $item = trim($item);
            if ($item === '') {
                continue;
            }

            $normalised[] = $item;
        }

        return array_values(array_unique($normalised));
    }
}
