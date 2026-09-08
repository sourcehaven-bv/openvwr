<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Enums\Import\ImportTarget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

use function __;
use function array_map;
use function implode;
use function is_string;
use function mb_strlen;

/**
 * Runs a profile over the source rows without touching the database, so the
 * user can correct the mapping before anything is written.
 */
class DryRunner
{
    public function __construct(
        private readonly MappingEngine $mappingEngine,
        private readonly FormDefaults $formDefaults,
    ) {
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function labelFor(string $modelClass, string $attribute): string
    {
        return (new TargetOptions(ImportTarget::forModel($modelClass)))->label($attribute);
    }

    /**
     * @param MappingProfile<Model> $profile
     * @param array<int, array<string, mixed>> $rows
     */
    public function run(MappingProfile $profile, array $rows): DryRunResult
    {
        $required = $this->formDefaults->required($profile->target);
        $lengths = $this->formDefaults->lengths($profile->target);

        $fits = [];
        $issues = [];

        foreach ($rows as $index => $row) {
            $mapped = $this->mappingEngine->apply($profile, $row);
            $reason = $this->reasonForIssue($mapped, $required, $lengths, $profile, $row);

            if ($reason !== null) {
                $issues[] = new DryRunIssue($index + 1, $row, $reason);

                continue;
            }

            $fits[] = ['number' => $index + 1, 'attributes' => $mapped, 'row' => $row];
        }

        return new DryRunResult($fits, $issues);
    }

    /**
     * @param array<string, mixed> $mapped
     * @param array<int, string> $required
     * @param array<string, int> $lengths
     * @param MappingProfile<Model> $profile
     * @param array<string, mixed> $row
     */
    private function reasonForIssue(array $mapped, array $required, array $lengths, MappingProfile $profile, array $row): ?string
    {
        // A value that was present but could not be converted is the more
        // specific problem, so it is reported before a plain empty field.
        foreach ($profile->fields as $field) {
            $source = Arr::get($row, $field->source);
            if ($source !== null && Arr::get($mapped, $field->target) === null) {
                return __('import_mapping.issue.not_convertible', [
                    'column' => $field->source,
                    'transform' => $field->transform->label(),
                ]);
            }
        }

        $tooLong = $this->tooLong($mapped, $lengths, $profile->target);
        if ($tooLong !== null) {
            return $tooLong;
        }

        $missing = [];
        foreach ($required as $attribute) {
            if (Arr::get($mapped, $attribute) === null) {
                $missing[] = $attribute;
            }
        }

        if ($missing !== []) {
            // Named as on the screen, not by column: "Naam", not "name".
            $labels = array_map(fn (string $attribute): string => $this->labelFor($profile->target, $attribute), $missing);

            return __('import_mapping.issue.missing_required', ['fields' => implode(', ', $labels)]);
        }

        return null;
    }

    /**
     * The database cuts nothing off quietly: a value longer than its column
     * fails the whole row when it is written, which the dry-run exists to see
     * first.
     *
     * @param array<string, mixed> $mapped
     * @param array<string, int> $lengths
     * @param class-string<Model> $modelClass
     */
    private function tooLong(array $mapped, array $lengths, string $modelClass): ?string
    {
        foreach ($lengths as $attribute => $max) {
            $value = Arr::get($mapped, $attribute);

            if (is_string($value) && mb_strlen($value) > $max) {
                return __('import_mapping.issue.too_long', [
                    'field' => $this->labelFor($modelClass, $attribute),
                    'max' => $max,
                ]);
            }
        }

        return null;
    }
}
