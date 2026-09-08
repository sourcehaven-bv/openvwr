<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use function array_intersect;
use function array_unique;
use function array_values;
use function explode;
use function in_array;
use function levenshtein;
use function max;
use function similar_text;
use function strlen;

/**
 * How much a source column's heading looks like a field's name or label.
 *
 * Words count for more than characters: "Melding AP" and "Gemeld aan de
 * autoriteit persoonsgegevens (AP)" share the abbreviation that identifies the
 * field, which plain edit distance loses in the long label. Synonyms are
 * folded first, so "soort" and "type" are the same word.
 */
class HeadingSimilarity
{
    /**
     * The score for a heading that is wholly contained in a label that says
     * considerably more: a suggestion, never a confident one.
     */
    public const PARTIAL_LABEL = 0.75;

    /**
     * Words too common in Dutch field labels to distinguish anything.
     */
    private const STOP_WORDS = ['de', 'het', 'een', 'van', 'aan', 'op', 'in', 'is', 'bij', 'voor', 'of', 'en'];

    /**
     * Shared by so many fields that matching on them proves little.
     */
    private const WEAK_TOKENS = [
        'datum',
        'gemeld',
        'nummer',
        'categorie',
        'categorieen',
        'categorieën',
        'overig',
        'overige',
        'andere',
        'anders',
        'van',
        'de',
        'het',
        'een',
        'en',
        'of',
    ];

    public function __construct(
        private readonly FieldSynonyms $fieldSynonyms,
    ) {
    }

    /**
     * The heading *is* the field name or its label, synonyms folded.
     */
    public function isExactName(string $header, string $fieldLabel, string $attribute): bool
    {
        $canonical = $this->fieldSynonyms->canonicalise($header);

        if ($canonical === '') {
            return false;
        }

        return $canonical === $this->fieldSynonyms->canonicalise($attribute)
            || $canonical === $this->fieldSynonyms->canonicalise($fieldLabel);
    }

    /**
     * 0.0 (nothing in common) to 1.0 (every word of the heading is in the label).
     */
    public function nameScore(string $header, string $fieldLabel, string $attribute): float
    {
        $best = max($this->similarity($header, $fieldLabel), $this->similarity($header, $attribute));
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
     * The share of the heading's words that occur in the label, weighted so
     * that words shared by many fields ("datum" across every date field) count
     * for less.
     */
    private function tokenScore(string $header, string $fieldLabel): float
    {
        $headerTokens = $this->tokens($header);
        $labelTokens = $this->tokens($fieldLabel);
        $shared = array_intersect($headerTokens, $labelTokens);

        if ($shared === []) {
            return 0.0;
        }

        $weight = $this->weight($shared);
        $coverage = $weight / $this->weight($headerTokens);

        if ($coverage >= 0.999 && $weight / $this->weight($labelTokens) <= 0.5) {
            return self::PARTIAL_LABEL;
        }

        return $coverage;
    }

    /**
     * @param array<int, string> $tokens
     */
    private function weight(array $tokens): float
    {
        $weight = 0.0;

        foreach ($tokens as $token) {
            $weight += in_array($token, self::WEAK_TOKENS, true) ? 0.25 : 1.0;
        }

        return $weight;
    }

    /**
     * @return array<int, string>
     */
    private function tokens(string $value): array
    {
        $tokens = [];

        foreach (explode(' ', $this->fieldSynonyms->canonicalise($value)) as $token) {
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

        $edit = 1.0 - (levenshtein($left, $right) / max(strlen($left), strlen($right)));

        // Levenshtein punishes differing lengths harshly, so a common-substring
        // measure keeps "Datum melding" close to "Datum melding AP".
        similar_text($left, $right, $percent);

        return max(0.0, $edit, $percent / 100);
    }
}
