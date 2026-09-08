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
use function implode;
use function in_array;
use function is_array;
use function is_scalar;
use function trim;

/**
 * The mapping while the user is editing it: a plain array keyed by source
 * column, as the review screen binds to it, with the logic to read it back
 * into a MappingProfile.
 *
 * Every target the browser sends is checked against the options the server
 * offered. A column can therefore only be mapped onto what the screen showed.
 */
class EditableMapping
{
    /**
     * How many distinct values are read from a column to judge it by.
     */
    private const SAMPLE_LIMIT = 5;

    /** @var array<string, ColumnReview> */
    private array $columns = [];

    /** @var array<string, MappingTransform>|null */
    private ?array $transforms = null;

    /**
     * @param array<int, string> $headers
     * @param array<string, array<string, string>> $mapping
     * @param array<int, array<string, mixed>> $rows
     */
    public function __construct(
        private readonly ImportTarget $target,
        private readonly array $headers,
        private readonly array $mapping,
        private readonly array $rows,
        private readonly bool $fromProfile,
        private readonly TargetOptions $options,
        private readonly TransformResolver $transformResolver,
        private readonly DateFormatDetector $dateFormatDetector,
        /**
         * The source column whose value marks the rows of one record; null
         * when every row is a record.
         */
        private readonly ?string $identity = null,
    ) {
    }

    /**
     * The editable shape of a profile: one entry per source column, unmapped
     * columns included so the screen can show them.
     *
     * @param array<int, string> $headers
     * @param MappingProfile<Model> $profile
     *
     * @return array<string, array<string, string>>
     */
    public static function fromProfile(array $headers, MappingProfile $profile): array
    {
        $editable = [];
        foreach ($headers as $header) {
            $editable[$header] = [
                'target' => '',
                'confidence' => MappingConfidence::Manual->value,
            ];
        }

        foreach ($profile->fields as $field) {
            $editable[$field->source] = [
                'target' => $field->target,
                'confidence' => $field->confidence->value,
                'date_format' => $field->dateFormat ?? '',
            ];
        }

        return $editable;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rows(): array
    {
        return $this->rows;
    }

    public function rowCount(): int
    {
        return count($this->rows);
    }

    /**
     * How many records the rows make once those of one record are folded.
     */
    public function recordCount(): int
    {
        return RecordGrouper::recordCount($this->rows, $this->identity);
    }

    public function identity(): ?string
    {
        return $this->identity;
    }

    public function options(): TargetOptions
    {
        return $this->options;
    }

    public function column(string $header): ColumnReview
    {
        if (array_key_exists($header, $this->columns)) {
            return $this->columns[$header];
        }

        $settings = $this->mapping[$header] ?? [];
        $target = $settings['target'] ?? '';
        $isRelation = $target !== '' && $this->isRelationTarget($target);

        return $this->columns[$header] = new ColumnReview(
            $header,
            $settings,
            $this->samplesFor($header),
            $this->fromProfile,
            $this->options,
            $isRelation ? null : ($this->transforms()[$target] ?? null),
            $isRelation,
            $this->dateFormatDetector,
        );
    }

    /**
     * Columns whose date format still has to be chosen. Until they are, the
     * mapping cannot be run: a guess would be exactly what a per-column format
     * is meant to prevent.
     *
     * @return array<int, string>
     */
    public function headersNeedingDateFormat(): array
    {
        return array_values(array_filter(
            $this->headers,
            fn (string $header): bool => $this->column($header)->needsDateFormat(),
        ));
    }

    /**
     * @return array<int, string>
     */
    public function unsettledHeaders(): array
    {
        return array_values(array_filter(
            $this->headers,
            fn (string $header): bool => !$this->column($header)->isSettled(),
        ));
    }

    /**
     * @return array<int, string>
     */
    public function settledHeaders(): array
    {
        return array_values(array_filter(
            $this->headers,
            fn (string $header): bool => $this->column($header)->isSettled(),
        ));
    }

    /**
     * @return MappingProfile<Model>
     *
     * @throws UnknownMappingTargetException when a target was not offered
     */
    public function toProfile(): MappingProfile
    {
        $fields = [];
        $unmapped = [];

        foreach ($this->mapping as $source => $settings) {
            $target = $settings['target'] ?? '';

            if ($target === '') {
                $unmapped[] = $source;

                continue;
            }

            if (!$this->options->allows($target)) {
                throw new UnknownMappingTargetException($target);
            }

            $column = $this->column($source);
            $transform = $column->profileTransform();
            $relation = $this->relationFor($target);
            $trueDate = $transform === MappingTransform::BooleanToDate ? $column->trueDate() : null;
            $dateFormat = $transform === MappingTransform::Date ? $column->dateFormat() : null;

            $fields[] = new MappingField(
                $source,
                $target,
                $transform,
                MappingConfidence::Manual,
                $trueDate,
                $relation,
                dateFormat: $dateFormat,
            );
        }

        return new MappingProfile($this->target->modelClass(), $fields, $unmapped, $this->identity);
    }

    /**
     * Targets chosen for more than one column, with those columns. A plain
     * field holds one value, so the second column would silently replace the
     * first; a link or a note takes as many columns as the source has.
     *
     * @return array<string, array<int, string>> target => source columns
     */
    public function duplicateTargets(): array
    {
        $columns = [];

        foreach ($this->mapping as $source => $settings) {
            $target = $settings['target'] ?? '';

            if ($target === '' || $this->acceptsSeveralColumns($target)) {
                continue;
            }

            $columns[$target][] = $source;
        }

        return array_filter($columns, static fn (array $sources): bool => count($sources) > 1);
    }

    /**
     * A shared entity collects every column that names one; a note is made per
     * column. Everything else, an attribute of a linked record included, is a
     * single value.
     */
    private function acceptsSeveralColumns(string $target): bool
    {
        [, $attribute] = RelationKey::split($target);

        return RelationKey::isRemarks($target) || ($attribute === null && $this->isRelationTarget($target));
    }

    /**
     * The key the writer resolves the column through, null for a plain field.
     */
    private function relationFor(string $target): ?string
    {
        if ($this->isRelationTarget($target) || RelationKey::isLookup($target) || RelationKey::isNote($target)) {
            return $target;
        }

        return null;
    }

    /**
     * A relation key, or an attribute of one ("processors::email").
     */
    private function isRelationTarget(string $target): bool
    {
        [$key] = RelationKey::split($target);

        foreach ($this->target->relations() as $relationTarget) {
            if ($relationTarget->key === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, MappingTransform>
     */
    private function transforms(): array
    {
        return $this->transforms ??= $this->transformResolver->forModel($this->target->modelClass());
    }

    /**
     * A few distinct values from the source, so the user can judge a mapping by
     * what is actually in the column instead of by its heading alone.
     *
     * @return array<int, string>
     */
    private function samplesFor(string $header): array
    {
        $samples = [];

        foreach ($this->rows as $row) {
            $value = $this->sampleValue(Arr::get($row, $header));

            if ($value === null || in_array($value, $samples, true)) {
                continue;
            }

            $samples[] = $value;

            if (count($samples) >= self::SAMPLE_LIMIT) {
                break;
            }
        }

        return $samples;
    }

    private function sampleValue(mixed $value): ?string
    {
        if (is_array($value)) {
            $parts = [];
            foreach ($value as $item) {
                if (is_scalar($item)) {
                    $parts[] = (string) $item;
                }
            }

            $value = implode(', ', $parts);
        }

        if (!is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : Str::limit($value, 80);
    }
}
