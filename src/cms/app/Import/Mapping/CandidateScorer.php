<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Enums\Import\MappingTransform;
use Illuminate\Support\Str;

use function array_intersect;
use function array_unique;
use function array_values;
use function count;
use function explode;
use function in_array;
use function is_numeric;
use function levenshtein;
use function max;
use function min;
use function preg_match;
use function similar_text;
use function strlen;
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

    /**
     * Words too common in Dutch field labels to distinguish anything.
     */
    private const STOP_WORDS = ['de', 'het', 'een', 'van', 'aan', 'op', 'in', 'is', 'bij', 'voor', 'of', 'en'];

    /**
     * Shared by so many fields that matching on them proves little.
     */
    /**
     * The score for a heading that is wholly contained in a label that says
     * considerably more: a suggestion, never a confident one.
     */
    private const PARTIAL_LABEL = 0.75;

    private const WEAK_TOKENS = ['datum', 'gemeld', 'nummer', 'categorie', 'categorieen', 'categorieën', 'van', 'de', 'het', 'een', 'en', 'of'];

    public function __construct(
        private readonly FieldSynonyms $fieldSynonyms,
    ) {
    }

    /**
     * @param array<int, string> $samples
     */
    public function score(string $header, string $fieldLabel, string $attribute, MappingTransform $transform, array $samples): float
    {
        // A heading that *is* the field name or its label leaves nothing to
        // interpret, whatever the values happen to look like.
        if ($this->isExactName($header, $fieldLabel, $attribute)) {
            return 1.0;
        }

        $name = $this->nameScore($header, $fieldLabel, $attribute);
        $content = $this->contentScore($transform, $samples);

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

    private function isExactName(string $header, string $fieldLabel, string $attribute): bool
    {
        $canonical = $this->fieldSynonyms->canonicalise($header);

        if ($canonical === '') {
            return false;
        }

        return $canonical === $this->fieldSynonyms->canonicalise($attribute)
            || $canonical === $this->fieldSynonyms->canonicalise($fieldLabel);
    }

    private function nameScore(string $header, string $fieldLabel, string $attribute): float
    {
        $best = 0.0;

        foreach ([$fieldLabel, $attribute] as $candidate) {
            $best = max($best, $this->similarity($header, $candidate));
        }

        $tokenScore = $this->tokenScore($header, $fieldLabel);

        // A heading whose words all occur in the label is a better signal than
        // raw string overlap, which favours whichever label is shortest.
        if ($tokenScore >= 0.999) {
            return 1.0;
        }

        // "Omschrijving" is every word of the heading but only half of the
        // label "Omschrijving beveiligingsmaatregelen": worth suggesting, not
        // worth filling in unseen, however similar the strings look.
        if ($tokenScore === self::PARTIAL_LABEL) {
            return self::PARTIAL_LABEL;
        }

        // Without a single shared word, character similarity is coincidence:
        // "Melder" and "Maatregelen" look alike but mean nothing to each other.
        if ($tokenScore === 0.0) {
            $best *= 0.5;
        }

        return max($best, $tokenScore);
    }

    /**
     * Rewards shared distinctive words, which plain edit distance loses in long
     * labels: "Melding AP" and "Gemeld aan de autoriteit persoonsgegevens (AP)"
     * share the abbreviation that actually identifies the field.
     */
    private function tokenScore(string $header, string $fieldLabel): float
    {
        $headerTokens = $this->tokens($header);
        $labelTokens = $this->tokens($fieldLabel);

        if ($headerTokens === [] || $labelTokens === []) {
            return 0.0;
        }

        $shared = array_intersect($headerTokens, $labelTokens);

        if ($shared === []) {
            return 0.0;
        }

        // Words shared by many fields of the same type ("datum" across every
        // date field) say nothing about *which* one, so they count for less.
        $weight = 0.0;
        foreach ($shared as $token) {
            $weight += in_array($token, self::WEAK_TOKENS, true) ? 0.25 : 1.0;
        }

        $total = 0.0;
        foreach ($headerTokens as $token) {
            $total += in_array($token, self::WEAK_TOKENS, true) ? 0.25 : 1.0;
        }

        $labelTotal = 0.0;
        foreach ($labelTokens as $token) {
            $labelTotal += in_array($token, self::WEAK_TOKENS, true) ? 0.25 : 1.0;
        }

        $coverage = $total <= 0.0 ? 0.0 : $weight / $total;

        if ($coverage >= 0.999 && $labelTotal > 0.0 && $weight / $labelTotal <= 0.5) {
            return self::PARTIAL_LABEL;
        }

        return $coverage;
    }

    /**
     * @return array<int, string>
     */
    private function tokens(string $value): array
    {
        $canonical = $this->fieldSynonyms->canonicalise($value);

        if ($canonical === '') {
            return [];
        }

        $tokens = [];
        foreach (explode(' ', $canonical) as $token) {
            // Skip filler words that appear in almost every Dutch label.
            if ($token === '' || in_array($token, self::STOP_WORDS, true)) {
                continue;
            }

            $tokens[] = $token;
        }

        return array_values(array_unique($tokens));
    }

    private function similarity(string $a, string $b): float
    {
        $left = $this->fieldSynonyms->canonicalise($a);
        $right = $this->fieldSynonyms->canonicalise($b);

        if ($left === '' || $right === '') {
            return 0.0;
        }

        // Both sides are non-empty by the checks above.
        $longest = max(strlen($left), strlen($right));
        $edit = 1.0 - (levenshtein($left, $right) / $longest);

        // Levenshtein punishes differing lengths harshly, so a common-substring
        // measure keeps "Datum melding" close to "Datum melding AP".
        similar_text($left, $right, $percent);

        return max(0.0, max($edit, $percent / 100));
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
        return in_array(Str::lower($value), ['ja', 'nee', 'neen', 'true', 'false', 'waar', 'onwaar', 'j', 'n', '1', '0'], true);
    }

    private function isDateish(string $value): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}([T ]\d{2}:\d{2}|$)/', $value) === 1
            || preg_match('#^\d{1,2}[-/]\d{1,2}[-/]\d{4}$#', $value) === 1;
    }
}
