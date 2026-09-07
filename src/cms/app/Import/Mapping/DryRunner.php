<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

use function __;
use function implode;

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
     * @param MappingProfile<Model> $profile
     * @param array<int, array<string, mixed>> $rows
     */
    public function run(MappingProfile $profile, array $rows): DryRunResult
    {
        $required = $this->formDefaults->required($profile->target);

        $fits = [];
        $issues = [];

        foreach ($rows as $index => $row) {
            $mapped = $this->mappingEngine->apply($profile, $row);
            $reason = $this->reasonForIssue($mapped, $required, $profile, $row);

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
     * @param MappingProfile<Model> $profile
     * @param array<string, mixed> $row
     */
    private function reasonForIssue(array $mapped, array $required, MappingProfile $profile, array $row): ?string
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

        $missing = [];
        foreach ($required as $attribute) {
            if (Arr::get($mapped, $attribute) === null) {
                $missing[] = $attribute;
            }
        }

        if ($missing !== []) {
            return __('import_mapping.issue.missing_required', ['fields' => implode(', ', $missing)]);
        }

        return null;
    }
}
