<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Enums\Import\MappingTransform;
use Illuminate\Support\Str;

use function array_diff;
use function array_map;
use function count;
use function in_array;
use function is_numeric;
use function min;
use function preg_match;
use function trim;

/**
 * Scores how likely a source column is to belong to a target field.
 *
 * Two signals are combined. The name says what the column is *called*, the
 * values say what it *is* -- and the values win ties, because a column headed
 * "Melding AP" holding "ja"/"nee" belongs to the boolean field, not to the
 * similarly named date field.
 */
class CandidateScorer
{
    /** Below this a suggestion is not worth showing. */
    public const THRESHOLD = 0.55;

    /**
     * Scores this low are still tracked, purely to notice that the winner had
     * close competition and is therefore not a safe suggestion.
     */
    public const CONSIDER = 0.40;

    /** At or above this the suggestion is applied without asking. */
    public const CONFIDENT = 0.80;

    public function __construct(
        private readonly HeadingSimilarity $headingSimilarity,
    ) {
    }

    /**
     * @param array<int, string> $samples
     * @param array<int, string> $options the values the field accepts, when it
     *        is a fixed choice; the samples are then judged against those
     */
    public function score(
        string $header,
        string $fieldLabel,
        string $attribute,
        MappingTransform $transform,
        array $samples,
        array $options = [],
    ): float {
        $content = $options === []
            ? $this->contentScore($transform, $samples)
            : $this->optionScore($options, $samples);
        $contradicted = $this->contradicts($content, $transform, $samples, $options);

        // A heading that *is* the field name or its label leaves nothing to
        // interpret, unless the values say otherwise.
        if (!$contradicted && $this->headingSimilarity->isExactName($header, $fieldLabel, $attribute)) {
            return 1.0;
        }

        $name = $this->headingSimilarity->nameScore($header, $fieldLabel, $attribute) * ($contradicted ? 0.5 : 1.0);

        // An exact name match is decisive; otherwise the values get a real say,
        // so a mistyped column cannot win on its heading alone.
        if ($name >= 0.999) {
            return $content === 0.0 ? 0.95 : 1.0;
        }

        $score = min(1.0, ($name * 0.6) + ($content * 0.4));

        // Values confirm a name, they do not replace it: a column of yes/no
        // answers fits every yes/no field, so it cannot make a loose name
        // match confident enough to be filled in without a look.
        return $name < self::CONFIDENT ? min($score, self::CONFIDENT - 0.01) : $score;
    }

    /**
     * The heading *is* the field's name or label, synonyms folded.
     */
    public function namesExactly(string $header, string $fieldLabel, string $attribute): bool
    {
        return $this->headingSimilarity->isExactName($header, $fieldLabel, $attribute);
    }

    /**
     * A column of AP reference numbers is not a yes/no field, whatever its
     * heading says, and free text is not a choice from a fixed list: values
     * that fit nowhere count against the name, so the column is left for the
     * user to place.
     *
     * @param array<int, string> $samples
     * @param array<int, string> $options
     */
    private function contradicts(float $content, MappingTransform $transform, array $samples, array $options): bool
    {
        if ($samples === [] || $content > 0.0) {
            return false;
        }

        return $options !== [] || $transform !== MappingTransform::Text;
    }

    /**
     * How well the sample values fit the field's type. Returns 0.0 when there is
     * nothing to judge, so an empty column falls back to its name alone.
     *
     * @param array<int, string> $samples
     */
    private function contentScore(MappingTransform $transform, array $samples): float
    {
        if ($samples === []) {
            return 0.0;
        }

        $matches = 0;
        foreach ($samples as $sample) {
            if ($this->looksLike($transform, trim($sample))) {
                $matches++;
            }
        }

        return $matches / count($samples);
    }

    /**
     * The share of samples that are one of the field's fixed choices. A cell
     * may hold several, one per line.
     *
     * @param array<int, string> $options
     * @param array<int, string> $samples
     */
    private function optionScore(array $options, array $samples): float
    {
        if ($samples === []) {
            return 0.0;
        }

        $allowed = array_map(static fn (string $option): string => Str::lower(trim($option)), $options);
        $matches = 0;

        foreach ($samples as $sample) {
            $parts = MultiValue::split($sample, "\n", $options);

            if ($parts !== [] && array_diff(array_map(Str::lower(...), $parts), $allowed) === []) {
                $matches++;
            }
        }

        return $matches / count($samples);
    }

    private function looksLike(MappingTransform $transform, string $value): bool
    {
        if ($value === '') {
            return false;
        }

        return match ($transform) {
            MappingTransform::Boolean, MappingTransform::BooleanToDate => $this->isBooleanish($value),
            MappingTransform::Date => $this->isDateish($value),
            MappingTransform::Integer => is_numeric($value),
            MappingTransform::StringList => Str::contains($value, ["\n", ';', ',']),
            MappingTransform::Text => !$this->isBooleanish($value) && !$this->isDateish($value),
        };
    }

    private function isBooleanish(string $value): bool
    {
        return in_array(
            Str::lower($value),
            ['ja', 'nee', 'neen', 'yes', 'no', 'true', 'false', 'waar', 'onwaar', 'j', 'n', '1', '0'],
            true,
        );
    }

    private function isDateish(string $value): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}([T ]\d{2}:\d{2}|$)/', $value) === 1
            || preg_match('#^\d{1,2}[-/]\d{1,2}[-/]\d{4}( \d{2}:\d{2}(:\d{2})?)?$#', $value) === 1;
    }
}
