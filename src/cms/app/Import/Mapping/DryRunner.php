<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Webmozart\Assert\Assert;

use function __;
use function implode;
use function in_array;
use function is_string;

/**
 * Runs a profile over the source rows without touching the database, so the
 * user can correct the mapping before anything is written.
 */
class DryRunner
{
    /**
     * Never treated as missing: filled in by the application, not the source.
     */
    private const GENERATED = ['id', 'number', 'organisation_id', 'import_id', 'created_at', 'updated_at', 'deleted_at'];

    public function __construct(
        private readonly MappingEngine $mappingEngine,
    ) {
    }

    /**
     * @param MappingProfile<Model> $profile
     * @param array<int, array<string, mixed>> $rows
     */
    public function run(MappingProfile $profile, array $rows): DryRunResult
    {
        $required = $this->requiredAttributes($profile->target);

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

    /**
     * Columns the database insists on, minus the ones the application fills in.
     *
     * @param class-string<Model> $target
     *
     * @return array<int, string>
     */
    private function requiredAttributes(string $target): array
    {
        $model = new $target();
        $table = $model->getTable();

        $required = [];
        foreach (Schema::getColumns($table) as $column) {
            Assert::isArray($column);
            $name = $column['name'] ?? null;
            if (!is_string($name) || in_array($name, self::GENERATED, true)) {
                continue;
            }

            if (($column['nullable'] ?? true) === true) {
                continue;
            }

            if (($column['default'] ?? null) !== null) {
                continue;
            }

            $required[] = $name;
        }

        return $required;
    }
}
