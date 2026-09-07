<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Enums\Import\ImportTarget;
use App\Enums\Import\MappingConfidence;
use App\Enums\Import\MappingTransform;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use function array_filter;
use function array_key_exists;
use function array_values;
use function count;
use function in_array;
use function is_scalar;
use function str_contains;
use function usort;

/**
 * Proposes a MappingProfile for a sheet.
 *
 * The strongest signal available is the application's own Dutch field labels:
 * a column headed "Datum melding" matches data_breach_record.reported_at
 * literally. Deliberately no fuzzy string distance -- an empty suggestion is
 * easier to correct than a wrong one.
 *
 * The candidates are exactly the targets the review screen offers, so a
 * proposal can never point at something the user could not have chosen.
 */
class MappingAnalyser
{
    /**
     * How far ahead the best candidate must be before it is offered at all.
     */
    private const AMBIGUITY_MARGIN = 0.10;

    public function __construct(
        private readonly TransformResolver $transformResolver,
        private readonly CandidateScorer $candidateScorer,
        private readonly DateFormatDetector $dateFormatDetector,
        private readonly FieldOptions $fieldOptions,
    ) {
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, array<string, mixed>> $rows sample rows, used to judge a
     *                                              column by its values as well
     *                                              as by its heading
     *
     * @return MappingProfile<Model>
     */
    public function analyse(ImportTarget $target, array $headers, array $rows = []): MappingProfile
    {
        $modelClass = $target->modelClass();
        $model = new $modelClass();
        $candidates = $this->candidates($target, $model);

        $scores = $this->scoreAll($headers, $candidates, $rows);

        return $this->assign($target, $headers, $candidates, $scores, $rows);
    }

    /**
     * Everything a column may be mapped onto, with the conversion each implies.
     * Links and lookups are matched on their label alone and read as text.
     *
     * @return array<string, array{label: string, transform: MappingTransform, relation: bool, options: array<int, string>}>
     */
    private function candidates(ImportTarget $target, Model $model): array
    {
        $fillable = $model->getFillable();
        $candidates = [];

        foreach ((new TargetOptions($target))->flat() as $key => $label) {
            if ($key === '') {
                continue;
            }

            $isAttribute = in_array($key, $fillable, true);

            // "Verwerkers — E-mail" is matched on "E-mail" alone: the relation
            // part would otherwise make every extra column tie with the
            // relation itself, and neither would be offered.
            if (str_contains($key, RelationKey::ATTRIBUTE_SEPARATOR)) {
                $label = Str::afterLast($label, ' — ');
            }

            $candidates[$key] = [
                'label' => $label,
                'transform' => $isAttribute ? $this->transformResolver->forAttribute($model, $key) : MappingTransform::Text,
                'relation' => !$isAttribute,
                'options' => $isAttribute ? $this->fieldOptions->for($model, $key) : [],
            ];
        }

        return $candidates;
    }

    /**
     * Every header against every candidate, so the assignment can be made
     * globally rather than first-come-first-served.
     *
     * @param array<int, string> $headers
     * @param array<string, array{label: string, transform: MappingTransform, relation: bool, options: array<int, string>}> $candidates
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<int, array{header: string, attribute: string, score: float}>
     */
    private function scoreAll(array $headers, array $candidates, array $rows): array
    {
        $scores = [];

        foreach ($headers as $header) {
            $samples = $this->samples($rows, $header);

            foreach ($candidates as $key => $candidate) {
                $score = $this->candidateScorer->score(
                    $header,
                    $candidate['label'],
                    $key,
                    $candidate['transform'],
                    $samples,
                    $candidate['options'],
                );

                // Near-misses are kept so ambiguity can be detected; they are
                // filtered out again once the field is known to be a clear win.
                if ($score >= CandidateScorer::CONSIDER) {
                    $scores[] = ['header' => $header, 'attribute' => $key, 'score' => $score];
                }
            }
        }

        usort($scores, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return $this->dropAmbiguous($scores);
    }

    /**
     * Drops every candidate for a header whose best candidate is not a clear
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
     * Best matches first; each header and each target is used once.
     *
     * @param array<int, string> $headers
     * @param array<string, array{label: string, transform: MappingTransform, relation: bool, options: array<int, string>}> $candidates
     * @param array<int, array{header: string, attribute: string, score: float}> $scores
     * @param array<int, array<string, mixed>> $rows
     *
     * @return MappingProfile<Model>
     */
    private function assign(ImportTarget $target, array $headers, array $candidates, array $scores, array $rows): MappingProfile
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

            $key = $candidate['attribute'];
            $transform = $candidates[$key]['transform'];

            $fields[] = new MappingField(
                $candidate['header'],
                $key,
                $transform,
                $candidate['score'] >= CandidateScorer::CONFIDENT
                    ? MappingConfidence::Exact
                    : MappingConfidence::Label,
                relation: $candidates[$key]['relation'] ? $key : null,
                // A date column is read in one format; when the samples leave
                // no doubt it is decided here, otherwise the user is asked.
                dateFormat: $transform === MappingTransform::Date
                    ? $this->dateFormatDetector->detect($this->samples($rows, $candidate['header']))
                    : null,
            );
        }

        $unmapped = [];
        foreach ($headers as $header) {
            if (!in_array($header, $usedHeaders, true)) {
                $unmapped[] = $header;
            }
        }

        return new MappingProfile($target->modelClass(), $fields, $unmapped);
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
}
