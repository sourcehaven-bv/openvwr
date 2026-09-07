<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Components\Uuid\UuidInterface;
use App\Enums\Import\MissingEntityPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function count;
use function in_array;
use function is_string;
use function preg_replace;
use function sprintf;
use function trim;

/**
 * Turns a plain string from a source file into a shared entity.
 *
 * A spreadsheet holds "Firma A" where the register holds a Processor record, and
 * every row naming that supplier has to end up pointing at the *same* record.
 * Getting this wrong is not hypothetical: app/Console/Commands/SystemUndouble.php
 * exists to clean up duplicates after the fact.
 *
 * Matching is deliberately conservative -- exact, then a small set of spelling
 * normalisations. No edit-distance matching: "Zorggroep Noord" and "Zorggroep
 * Oost" are one character apart and are not the same organisation.
 */
class EntityResolver
{
    /**
     * Resolved records for this run, so a name occurring in a hundred rows
     * produces one record rather than a hundred.
     *
     * @var array<string, Model>
     */
    private array $resolved = [];

    /**
     * The organisation's existing records per model, keyed by normalised name
     * and in order of creation. Loaded once per model so a sheet with many
     * unknown names does not scan the table for each of them.
     *
     * @var array<string, array<string, array<int, Model>>>
     */
    private array $index = [];

    /**
     * Names that were newly created, per model class, so the import can report
     * what it added instead of silently growing the register.
     *
     * @var array<string, array<int, string>>
     */
    private array $created = [];

    /**
     * Names matched only after normalisation, so a human can confirm them.
     *
     * @var array<string, array<int, array{source: string, matched: string}>>
     */
    private array $fuzzy = [];

    /**
     * Names that matched more than one existing record, with how many.
     *
     * @var array<string, array<string, int>>
     */
    private array $ambiguous = [];

    /**
     * Names that were not found, for models that may not be created.
     *
     * @var array<string, array<int, string>>
     */
    private array $unresolved = [];

    /**
     * @param class-string<Model> $modelClass
     * @param array<string, string> $extra further attributes to fill on a newly
     *        created record, e.g. an e-mail address from a separate column
     */
    public function resolve(
        string $modelClass,
        string $nameAttribute,
        string $value,
        UuidInterface $organisationId,
        MissingEntityPolicy $missing = MissingEntityPolicy::Create,
        array $extra = [],
    ): ?Model {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $cacheKey = $modelClass . '|' . Str::lower($value);

        if (array_key_exists($cacheKey, $this->resolved)) {
            return $this->resolved[$cacheKey];
        }

        $existing = $this->findExisting($modelClass, $nameAttribute, $value, $organisationId);

        if ($existing !== null) {
            return $this->resolved[$cacheKey] = $existing;
        }

        if ($missing === MissingEntityPolicy::Report) {
            $this->unresolved[$modelClass][] = $value;

            return null;
        }

        $model = $this->create($modelClass, $nameAttribute, $value, $organisationId, $extra);

        $this->created[$modelClass][] = $value;
        $this->index($modelClass, $nameAttribute, $organisationId);
        $this->index[$this->indexKey($modelClass, $organisationId)][$this->normalise($value)][] = $model;

        return $this->resolved[$cacheKey] = $model;
    }

    /**
     * @param class-string<Model> $modelClass
     * @param array<string, string> $extra
     */
    private function create(
        string $modelClass,
        string $nameAttribute,
        string $value,
        UuidInterface $organisationId,
        array $extra,
    ): Model {
        $model = new $modelClass();
        $model->setAttribute('organisation_id', $organisationId);
        $model->setAttribute($nameAttribute, $value);

        // These models were built for hand entry and insist on columns a source
        // file does not always supply, so the rest is filled in blank.
        foreach ($this->requiredBlanks($model, $nameAttribute) as $column) {
            $model->setAttribute($column, '');
        }

        foreach ($extra as $attribute => $extraValue) {
            if ($extraValue !== '') {
                $model->setAttribute($attribute, $extraValue);
            }
        }

        $model->save();

        return $model;
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function findExisting(string $modelClass, string $nameAttribute, string $value, UuidInterface $organisationId): ?Model
    {
        $exact = $modelClass::query()
            ->where('organisation_id', $organisationId)
            ->where($nameAttribute, $value)
            ->orderBy('created_at')
            ->get();

        if ($exact->count() > 0) {
            $this->recordDuplicates($modelClass, $value, $exact->count());

            /** @var Model $first */
            $first = $exact->first();

            return $first;
        }

        $matches = $this->index($modelClass, $nameAttribute, $organisationId)[$this->normalise($value)] ?? [];

        if ($matches === []) {
            return null;
        }

        $first = $matches[0];
        $firstName = $first->getAttribute($nameAttribute);
        Assert::string($firstName);

        $this->fuzzy[$modelClass][] = ['source' => $value, 'matched' => $firstName];
        $this->recordDuplicates($modelClass, $value, count($matches));

        return $first;
    }

    /**
     * @param class-string<Model> $modelClass
     *
     * @return array<string, array<int, Model>>
     */
    private function index(string $modelClass, string $nameAttribute, UuidInterface $organisationId): array
    {
        $key = $this->indexKey($modelClass, $organisationId);

        if (array_key_exists($key, $this->index)) {
            return $this->index[$key];
        }

        $index = [];

        /** @var Model $candidate */
        foreach ($modelClass::query()->where('organisation_id', $organisationId)->orderBy('created_at')->get() as $candidate) {
            $candidateName = $candidate->getAttribute($nameAttribute);

            if (!is_string($candidateName)) {
                continue;
            }

            $index[$this->normalise($candidateName)][] = $candidate;
        }

        return $this->index[$key] = $index;
    }

    private function indexKey(string $modelClass, UuidInterface $organisationId): string
    {
        return sprintf('%s|%s', $modelClass, $organisationId->toString());
    }

    /**
     * The register does not enforce unique names, and duplicates do occur --
     * app/Console/Commands/SystemUndouble.php exists to clean them up. Picking
     * one silently would attach rows to an arbitrary record, so the ambiguity is
     * reported instead: the oldest is used and the user is told to resolve it.
     */
    private function recordDuplicates(string $modelClass, string $value, int $count): void
    {
        if ($count < 2) {
            return;
        }

        $this->ambiguous[$modelClass][$value] = $count;
    }

    /**
     * Non-nullable columns the source cannot supply. In practice these are all
     * text columns; a required column of another type would fail the row, which
     * the writer reports.
     *
     * @return array<int, string>
     */
    private function requiredBlanks(Model $model, string $nameAttribute): array
    {
        $skip = [$model->getKeyName(), $nameAttribute, 'organisation_id', 'created_at', 'updated_at', 'deleted_at'];
        $blanks = [];

        foreach (Schema::getColumns($model->getTable()) as $column) {
            Assert::isArray($column);
            $name = $column['name'] ?? null;

            if (!is_string($name) || in_array($name, $skip, true)) {
                continue;
            }

            if (($column['nullable'] ?? true) === true || ($column['default'] ?? null) !== null) {
                continue;
            }

            $blanks[] = $name;
        }

        return $blanks;
    }

    /**
     * Differences that are spelling rather than identity: casing, spacing,
     * trailing punctuation and the way a legal form is written.
     */
    private function normalise(string $value): string
    {
        $value = Str::lower(trim($value));
        $value = preg_replace('/\b(b\.?v\.?|n\.?v\.?|v\.?o\.?f\.?)\b/u', '', $value) ?? $value;
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function created(): array
    {
        return $this->created;
    }

    /**
     * @return array<string, array<int, array{source: string, matched: string}>>
     */
    public function fuzzyMatches(): array
    {
        return $this->fuzzy;
    }

    /**
     * @return array<string, array<string, int>>
     */
    public function ambiguousMatches(): array
    {
        return $this->ambiguous;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function unresolved(): array
    {
        return $this->unresolved;
    }
}
