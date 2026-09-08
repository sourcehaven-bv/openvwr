<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Components\Uuid\UuidInterface;
use App\Enums\Import\ImportTarget;
use App\Import\Factories\General\LookupListFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;
use Webmozart\Assert\Assert;

use function array_filter;
use function array_keys;
use function is_string;
use function sprintf;
use function trim;

/**
 * Writes the rows a dry-run accepted.
 *
 * Each row is its own transaction: a row that fails is rolled back completely
 * and reported by number, and the rows around it are unaffected. A row whose
 * source reference was imported before is skipped, so running the same sheet
 * twice adds nothing.
 */
class MappedRecordWriter
{
    public function __construct(
        private readonly EntityResolver $entityResolver,
        private readonly LookupListFactory $lookupFactory,
        private readonly DatabaseManager $databaseManager,
        private readonly FormDefaults $formDefaults,
    ) {
    }

    /**
     * @param MappingProfile<Model> $profile
     */
    public function write(
        ImportTarget $target,
        MappingProfile $profile,
        DryRunResult $result,
        UuidInterface $organisationId,
    ): WriteResult {
        $imported = 0;
        $skipped = 0;
        $failures = [];

        foreach ($result->fits as $fit) {
            try {
                $written = $this->databaseManager->transaction(
                    fn (): bool => $this->writeRow($target, $profile, $fit, $organisationId),
                );

                $imported += $written ? 1 : 0;
                $skipped += $written ? 0 : 1;
            } catch (Throwable $throwable) {
                $failures[] = ['row' => $fit['number'], 'reason' => WriteFailureReason::describe($throwable)];

                // Rows hold personal data, so the exception message (which for a
                // query exception includes the bound values) stays out of the log.
                Log::error('mapped import failed for a row', [
                    'row' => $fit['number'],
                    'exception' => $throwable::class,
                    'code' => $throwable->getCode(),
                ]);
            }
        }

        return new WriteResult(
            $imported,
            $skipped,
            $failures,
            $this->entityResolver->created(),
            $this->entityResolver->fuzzyMatches(),
            $this->entityResolver->ambiguousMatches(),
            $this->entityResolver->unresolved(),
        );
    }

    /**
     * @param MappingProfile<Model> $profile
     * @param array{number: int, attributes: array<string, mixed>, row: array<string, mixed>} $fit
     *
     * @return bool false when the row was imported before
     */
    private function writeRow(ImportTarget $target, MappingProfile $profile, array $fit, UuidInterface $organisationId): bool
    {
        $modelClass = $target->modelClass();

        if ($this->importedBefore($modelClass, $fit['attributes'], $organisationId)) {
            return false;
        }

        $model = new $modelClass();
        // An empty cell says nothing; the field starts out as it does on the
        // form, rather than being emptied on purpose.
        $supplied = array_filter($fit['attributes'], static fn (mixed $value): bool => $value !== null);
        $model->fill($supplied + $this->formDefaults->defaults($modelClass));
        $model->setAttribute('organisation_id', $organisationId);

        $this->attachLookups($model, $target, $profile, $fit['row'], $organisationId);
        $model->save();

        $this->attachRelations($model, $target, $profile, $fit['row'], $organisationId);
        $this->attachRemarks($model, $profile, $fit['row']);

        return true;
    }

    /**
     * A source reference makes a row recognisable; without one there is nothing
     * to recognise it by and it is written again.
     *
     * @param class-string<Model> $modelClass
     * @param array<string, mixed> $attributes
     */
    private function importedBefore(string $modelClass, array $attributes, UuidInterface $organisationId): bool
    {
        $importId = $attributes['import_id'] ?? null;

        if (!is_string($importId) || trim($importId) === '') {
            return false;
        }

        return $modelClass::query()
            ->where('organisation_id', $organisationId)
            ->where('import_id', $importId)
            ->exists();
    }

    /**
     * Fills lookup-list references, adding the value to the list when it is new.
     *
     * @param MappingProfile<Model> $profile
     * @param array<string, mixed> $row
     */
    private function attachLookups(
        Model $model,
        ImportTarget $target,
        MappingProfile $profile,
        array $row,
        UuidInterface $organisationId,
    ): void {
        foreach ($target->lookups() as $lookup) {
            $key = RelationKey::lookup($lookup->key);

            foreach ($profile->fields as $field) {
                if ($field->relation !== $key) {
                    continue;
                }

                $value = Arr::get($row, $field->source);

                if (!is_string($value) || trim($value) === '') {
                    continue;
                }

                $record = $this->lookupFactory->create($lookup->modelClass, $organisationId, trim($value));

                if ($record !== null) {
                    $model->setAttribute($lookup->foreignKey, $record->id);
                }
            }
        }
    }

    /**
     * Links the row to its shared entities, creating them where allowed.
     *
     * @param MappingProfile<Model> $profile
     * @param array<string, mixed> $row
     */
    private function attachRelations(
        Model $model,
        ImportTarget $target,
        MappingProfile $profile,
        array $row,
        UuidInterface $organisationId,
    ): void {
        foreach ($target->relations() as $relationTarget) {
            $names = $this->relationValues($profile, $row, $relationTarget->key, null);

            if ($names === []) {
                continue;
            }

            // Columns like "processors::email" describe the same record as the
            // name column, so they are read alongside it rather than separately.
            $extraColumns = [];
            foreach (array_keys($relationTarget->extraAttributes) as $attribute) {
                $extraColumns[$attribute] = $this->relationValues($profile, $row, $relationTarget->key, $attribute);
            }

            $relation = $relationTarget->relationFor($model);

            foreach ($names as $index => $name) {
                $extra = [];
                foreach ($extraColumns as $attribute => $values) {
                    $extra[$attribute] = $values[$index] ?? '';
                }

                $entity = $this->entityResolver->resolve(
                    $relationTarget->modelClass,
                    $relationTarget->nameAttribute,
                    $name,
                    $organisationId,
                    $relationTarget->missing,
                    $extra,
                );

                if ($entity === null) {
                    continue;
                }

                $this->fillSubRecord($entity, $relationTarget, $profile, $row, $index);

                // Keys are UUID objects, which cannot be used as array keys.
                $entityKey = $entity->getKey();
                Assert::isInstanceOf($entityKey, UuidInterface::class);
                $relation->syncWithoutDetaching([$entityKey->toString()]);
            }
        }
    }

    /**
     * Keeps columns without a field of their own as notes on the record, one
     * per column, headed with the column name so the origin stays visible.
     *
     * @param MappingProfile<Model> $profile
     * @param array<string, mixed> $row
     */
    private function attachRemarks(Model $model, MappingProfile $profile, array $row): void
    {
        foreach ($profile->fields as $field) {
            if ($field->relation !== RelationKey::REMARKS) {
                continue;
            }

            $value = Arr::get($row, $field->source);

            if (!is_string($value) || trim($value) === '') {
                continue;
            }

            // Only registers with notes offer the target; TargetOptions sees to that.
            $callable = [$model, 'remarks'];
            Assert::isCallable($callable);
            $relation = $callable();
            Assert::isInstanceOf($relation, MorphMany::class);

            $relation->create(['body' => sprintf('%s: %s', $field->source, trim($value))]);
        }
    }

    /**
     * Builds the record that belongs to a related record, such as an address,
     * from whichever columns the source supplies.
     *
     * @param MappingProfile<Model> $profile
     * @param array<string, mixed> $row
     */
    private function fillSubRecord(
        Model $entity,
        RelationTarget $target,
        MappingProfile $profile,
        array $row,
        int $index,
    ): void {
        $subRecord = ImportTarget::subRecords()[$target->key] ?? null;

        if ($subRecord === null) {
            return;
        }

        $attributes = [];
        foreach (array_keys($subRecord->attributes) as $attribute) {
            $values = $this->relationValues(
                $profile,
                $row,
                $target->key,
                sprintf('%s.%s', $subRecord->key, $attribute),
            );

            $value = $values[$index] ?? '';

            if ($value !== '') {
                $attributes[$attribute] = $value;
            }
        }

        if ($attributes === []) {
            return;
        }

        $relation = $subRecord->relationFor($entity);
        $existing = $relation->first();

        if ($existing instanceof Model) {
            $existing->fill($attributes);
            $existing->save();

            return;
        }

        $modelClass = $subRecord->modelClass;
        $new = new $modelClass();
        $new->fill($attributes);
        $relation->save($new);
    }

    /**
     * All values a row supplies for one relation, in column order, so a name in
     * column three lines up with an e-mail address in column four.
     *
     * @param MappingProfile<Model> $profile
     * @param array<string, mixed> $row
     *
     * @return array<int, string>
     */
    private function relationValues(MappingProfile $profile, array $row, string $key, ?string $attribute): array
    {
        $wanted = $attribute === null ? $key : RelationKey::attribute($key, $attribute);

        $values = [];

        foreach ($profile->fields as $field) {
            if ($field->relation !== $wanted) {
                continue;
            }

            $value = Arr::get($row, $field->source);

            if (!is_string($value)) {
                continue;
            }

            foreach (MultiValue::split($value, $field->separator) as $part) {
                $values[] = $part;
            }
        }

        return $values;
    }
}
