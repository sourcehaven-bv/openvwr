<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Enums\Import\ImportTarget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

use function __;
use function array_key_exists;
use function class_basename;
use function in_array;
use function is_string;
use function sprintf;
use function str_contains;
use function str_ends_with;

/**
 * Everything a source column may be mapped onto for one register: its plain
 * fields, its lookup lists and its shared entities.
 *
 * The list is built server-side from the model and the ImportTarget registry,
 * and the same list is used to validate what the browser sends back, so a
 * target that is not offered can also not be chosen.
 */
class TargetOptions
{
    /**
     * Filled in by the application, so never offered as an import target.
     * `state` is the workflow status: a source file may not bypass the review
     * process by supplying one.
     */
    private const INTERNAL_ATTRIBUTES = [
        'entity_number_id',
        'organisation_id',
        'parent_id',
        'public_from',
        'review_at',
        'state',
    ];

    /**
     * Foreign keys point at records, not at values a source file can supply;
     * mapping a name onto an id column would only produce broken references.
     */
    private const INTERNAL_SUFFIX = '_id';

    /** @var array<string, array<string, string>>|null */
    private ?array $grouped = null;

    public function __construct(
        private readonly ImportTarget $target,
    ) {
    }

    /**
     * The options grouped the way the register's form groups its fields, so a
     * long flat list does not have to be read top to bottom.
     *
     * @return array<string, array<string, string>>
     */
    public function grouped(): array
    {
        if ($this->grouped !== null) {
            return $this->grouped;
        }

        $modelClass = $this->target->modelClass();
        $model = new $modelClass();
        $labelKey = Str::snake(class_basename($modelClass));

        $grouped = [];
        $grouped[__('import_mapping.group_none')] = ['' => __('import_mapping.ignore')];

        $placed = [];
        foreach ($this->fieldGroups($model, $labelKey, $placed) as $group => $options) {
            $grouped[$group] = $options;
        }

        $remaining = $this->remainingFields($model, $labelKey, $placed);
        if ($remaining !== []) {
            $grouped[__('import_mapping.group_other')] = $remaining;
        }

        $lookups = $this->lookups();
        if ($lookups !== []) {
            $grouped[__('import_mapping.group_lookups')] = $lookups;
        }

        $relations = $this->relations();
        if ($relations !== []) {
            $grouped[__('import_mapping.group_relations')] = $relations;
        }

        return $this->grouped = $grouped;
    }

    /**
     * @return array<string, string>
     */
    public function flat(): array
    {
        $options = [];
        foreach ($this->grouped() as $group) {
            foreach ($group as $value => $label) {
                $options[$value] = $label;
            }
        }

        return $options;
    }

    /**
     * Whether the browser may map a column onto this target. The empty string
     * is "do not import" and always allowed.
     */
    public function allows(string $target): bool
    {
        return array_key_exists($target, $this->flat());
    }

    public function label(string $target): string
    {
        return $this->flat()[$target] ?? $target;
    }

    /**
     * @param array<int, string> $placed
     *
     * @return array<string, array<string, string>>
     */
    private function fieldGroups(Model $model, string $labelKey, array &$placed): array
    {
        $fillable = $model->getFillable();
        $groups = [];

        foreach ($this->target->fieldGroups() as $groupKey => $attributes) {
            $options = [];

            foreach ($attributes as $attribute) {
                // The groups are configuration; a group naming a field the
                // model does not expose is a mistake to fix, not to hide.
                Assert::inArray($attribute, $fillable);
                Assert::false($this->isInternal($attribute));

                $options[$attribute] = $this->attributeLabel($labelKey, $attribute);
                $placed[] = $attribute;
            }

            if ($options !== []) {
                $groups[__($groupKey)] = $options;
            }
        }

        return $groups;
    }

    /**
     * Anything the groups do not mention still has to be selectable.
     *
     * @param array<int, string> $placed
     *
     * @return array<string, string>
     */
    private function remainingFields(Model $model, string $labelKey, array $placed): array
    {
        $remaining = [];
        foreach ($model->getFillable() as $attribute) {
            if ($this->isInternal($attribute) || in_array($attribute, $placed, true)) {
                continue;
            }

            $remaining[$attribute] = $this->attributeLabel($labelKey, $attribute);
        }

        return $remaining;
    }

    /**
     * @return array<string, string>
     */
    private function lookups(): array
    {
        $lookups = [];
        foreach ($this->target->lookups() as $lookupTarget) {
            $lookups[RelationKey::lookup($lookupTarget->key)] = __($lookupTarget->labelKey);
        }

        return $lookups;
    }

    /**
     * @return array<string, string>
     */
    private function relations(): array
    {
        $relations = [];
        foreach ($this->target->relations() as $relationTarget) {
            $relations[$relationTarget->key] = __($relationTarget->labelKey);

            // A source may spread one related record over several columns: a
            // name in one, an e-mail address in the next.
            foreach ($relationTarget->extraAttributes as $attribute => $attributeLabelKey) {
                $relations[RelationKey::attribute($relationTarget->key, $attribute)]
                    = sprintf('%s — %s', __($relationTarget->labelKey), __($attributeLabelKey));
            }

            // A related record can carry a sub-record of its own, such as an
            // address, spread over further columns.
            $subRecord = ImportTarget::subRecords()[$relationTarget->key] ?? null;

            if ($subRecord === null) {
                continue;
            }

            foreach ($subRecord->attributes as $attribute => $attributeLabelKey) {
                $key = RelationKey::attribute($relationTarget->key, sprintf('%s.%s', $subRecord->key, $attribute));

                $relations[$key] = sprintf('%s — %s', __($relationTarget->labelKey), __($attributeLabelKey));
            }
        }

        return $relations;
    }

    private function isInternal(string $attribute): bool
    {
        if (in_array($attribute, self::INTERNAL_ATTRIBUTES, true)) {
            return true;
        }

        // import_id is the one id a source does supply: its own reference.
        return $attribute !== 'import_id' && str_ends_with($attribute, self::INTERNAL_SUFFIX);
    }

    /**
     * Shows the Dutch field label the register itself uses; the attribute name
     * is an implementation detail the user has no use for.
     */
    private function attributeLabel(string $labelKey, string $attribute): string
    {
        if ($attribute === 'import_id') {
            return __('import_mapping.field_import_id');
        }

        $label = $this->translate($labelKey, $attribute);

        if ($label === null) {
            // No translation anywhere: show the column name, but readably.
            return Str::of($attribute)
                ->replace('_', ' ')
                ->trim()
                ->ucfirst()
                ->toString();
        }

        // Several fields share the label "Namelijk"; prefix them with the field
        // they belong to so the options stay distinguishable.
        if (str_ends_with($attribute, '_other')) {
            $parent = $this->translate($labelKey, Str::beforeLast($attribute, '_other'));

            if ($parent !== null) {
                return sprintf('%s — %s', $parent, $label);
            }
        }

        return $label;
    }

    /**
     * The registers keep shared field names in processing_record.php and some
     * general ones in general.php, so a single lookup misses half of them.
     */
    private function translate(string $labelKey, string $attribute): ?string
    {
        foreach ([$labelKey, 'processing_record', 'general'] as $file) {
            $label = __(sprintf('%s.%s', $file, $attribute));

            if (is_string($label) && !str_contains($label, '.')) {
                return $label;
            }
        }

        return null;
    }
}
