<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Enums\Import\MappingConfidence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

use function array_filter;
use function array_key_exists;
use function array_values;
use function class_basename;
use function count;
use function in_array;
use function is_scalar;
use function is_string;
use function usort;

/**
 * Proposes a MappingProfile for a sheet.
 *
 * The strongest signal available is the application's own Dutch field labels:
 * a column headed "Datum melding" matches data_breach_record.reported_at
 * literally. Deliberately no fuzzy string distance -- an empty suggestion is
 * easier to correct than a wrong one.
 */
class MappingAnalyser
{
    /**
     * Filled in by the application, so never a suggestion target.
     */
    private const SKIP_ATTRIBUTES = ['organisation_id', 'entity_number_id'];

    /**
     * How far ahead the best candidate must be before it is offered at all.
     */
    private const AMBIGUITY_MARGIN = 0.10;

    public function __construct(
        private readonly TransformResolver $transformResolver,
        private readonly CandidateScorer $candidateScorer,
    ) {
    }

    /**
     * @param class-string<Model> $target
     * @param array<int, string> $headers
     *
     * @return MappingProfile<Model>
     */

    /**
     * @param class-string<Model> $target
     * @param array<int, string> $headers
     * @param array<int, array<string, mixed>> $rows sample rows, used to judge a
     *                                              column by its values as well
     *                                              as by its heading
     *
     * @return MappingProfile<Model>
     */
    public function analyse(string $target, array $headers, array $rows = []): MappingProfile
    {
        $model = new $target();
        /** @var array<int, string> $fillable */
        $fillable = $model->getFillable();
        $labels = $this->labelsFor($target);

        $scores = $this->scoreAll($model, $headers, $fillable, $labels, $rows);

        return $this->assign($target, $headers, $model, $scores);
    }

    /**
     * Every header against every field, so the assignment can be made globally
     * rather than first-come-first-served.
     *
     * @param array<int, string> $headers
     * @param array<int, string> $fillable
     * @param array<string, string> $labels
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<int, array{header: string, attribute: string, score: float}>
     */
    private function scoreAll(Model $model, array $headers, array $fillable, array $labels, array $rows): array
    {
        $scores = [];

        foreach ($headers as $header) {
            $samples = $this->samples($rows, $header);

            foreach ($fillable as $attribute) {
                if (in_array($attribute, self::SKIP_ATTRIBUTES, true)) {
                    continue;
                }

                $score = $this->candidateScorer->score(
                    $header,
                    $labels[$attribute] ?? $attribute,
                    $attribute,
                    $this->transformResolver->forAttribute($model, $attribute),
                    $samples,
                );

                // Near-misses are kept so ambiguity can be detected; they are
                // filtered out again once the field is known to be a clear win.
                if ($score >= CandidateScorer::CONSIDER) {
                    $scores[] = ['header' => $header, 'attribute' => $attribute, 'score' => $score];
                }
            }
        }

        usort($scores, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return $this->dropAmbiguous($scores);
    }

    /**
     * Drops a header's suggestions when its best two candidates are too close to
     * call. "Meldingsdatum" fits several date fields about equally well, and a
     * coin flip presented as a suggestion is worse than no suggestion: the user
     * has to notice it is wrong before they can correct it.
     *
     * @param array<int, array{header: string, attribute: string, score: float}> $scores
     *
     * @return array<int, array{header: string, attribute: string, score: float}>
     */
    private function dropAmbiguous(array $scores): array
    {
        $best = [];
        $runnerUp = [];

        foreach ($scores as $candidate) {
            $header = $candidate['header'];

            if (!array_key_exists($header, $best)) {
                $best[$header] = $candidate['score'];

                continue;
            }

            if (!array_key_exists($header, $runnerUp)) {
                $runnerUp[$header] = $candidate['score'];
            }
        }

        return array_values(array_filter($scores, static function (array $candidate) use ($best, $runnerUp): bool {
            $header = $candidate['header'];

            if (!array_key_exists($header, $runnerUp)) {
                return true;
            }

            if ($candidate['score'] < CandidateScorer::THRESHOLD) {
                return false;
            }

            if ($best[$header] >= CandidateScorer::CONFIDENT) {
                return true;
            }

            return $best[$header] - $runnerUp[$header] >= self::AMBIGUITY_MARGIN;
        }));
    }

    /**
     * Walks the candidates from most to least likely, taking each header and
     * field out of play once used, so a strong match cannot be stolen by a
     * weaker one later in the list.
     *
     * @param class-string<Model> $target
     * @param array<int, string> $headers
     * @param array<int, array{header: string, attribute: string, score: float}> $scores
     *
     * @return MappingProfile<Model>
     */
    private function assign(string $target, array $headers, Model $model, array $scores): MappingProfile
    {
        $fields = [];
        $usedHeaders = [];
        $usedAttributes = [];

        foreach ($scores as $candidate) {
            if (in_array($candidate['header'], $usedHeaders, true)) {
                continue;
            }

            if (in_array($candidate['attribute'], $usedAttributes, true)) {
                continue;
            }

            $usedHeaders[] = $candidate['header'];
            $usedAttributes[] = $candidate['attribute'];

            $fields[] = new MappingField(
                $candidate['header'],
                $candidate['attribute'],
                $this->transformResolver->forAttribute($model, $candidate['attribute']),
                $candidate['score'] >= CandidateScorer::CONFIDENT
                    ? MappingConfidence::Exact
                    : MappingConfidence::Label,
            );
        }

        $unmapped = [];
        foreach ($headers as $header) {
            if (!in_array($header, $usedHeaders, true)) {
                $unmapped[] = $header;
            }
        }

        return new MappingProfile($target, $fields, $unmapped);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<int, string>
     */
    private function samples(array $rows, string $header, int $limit = 5): array
    {
        $samples = [];

        foreach ($rows as $row) {
            $value = Arr::get($row, $header);

            if ($value === null || !is_scalar($value)) {
                continue;
            }

            $samples[] = (string) $value;

            if (count($samples) >= $limit) {
                break;
            }
        }

        return $samples;
    }

    /**
     * @param class-string<Model> $target
     *
     * @return array<string, string>
     */
    private function labelsFor(string $target): array
    {
        $key = Str::snake(class_basename($target));

        if (!Lang::has($key)) {
            return [];
        }

        $translations = Lang::get($key);
        Assert::isArray($translations);

        $labels = [];
        foreach ($translations as $attribute => $label) {
            if (is_string($attribute) && is_string($label)) {
                $labels[$attribute] = $label;
            }
        }

        return $labels;
    }
}
