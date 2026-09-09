<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Enums\Authorization\Permission;
use App\Enums\Import\ImportTarget;
use App\Facades\Authorization;
use Illuminate\Database\Eloquent\Model;
use Webmozart\Assert\Assert;

use function __;
use function array_intersect;
use function array_key_exists;
use function array_keys;
use function in_array;
use function method_exists;
use function sprintf;
use function str_ends_with;

/**
 * Everything a source column may be mapped onto for one register: its plain
 * fields, its lookup lists and its shared entities.
 *
 * The fields are the ones on the register's form, under the form's labels
 * (FormFields); a fillable the form does not have is not a field a sheet can
 * fill. The same list is used to validate what the browser sends back, so a
 * target that is not offered can also not be chosen.
 */
class TargetOptions
{
    /**
     * Filled in by the application, so never offered as an import target.
     * `state` is the workflow status and `public_from` the publication: a
     * source file may not bypass the review process by supplying them.
     */
    private const INTERNAL_ATTRIBUTES = [
        'entity_number_id',
        'organisation_id',
        'parent_id',
        'public_from',
        'state',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Foreign keys point at records, not at values a source file can supply;
     * mapping a name onto an id column would only produce broken references.
     * Only a uuid column is one, though: "meta_national_id" is a number a
     * person types.
     */
    private const INTERNAL_SUFFIX = '_id';

    /**
     * The source reference has no field on the form: it is what the import
     * remembers a row by, so a second run can find what the first made.
     */
    private const SOURCE_REFERENCE = 'import_id';

    /** @var array<string, array<string, string>>|null */
    private ?array $grouped = null;

    private readonly TableColumns $tableColumns;

    public function __construct(
        private readonly ImportTarget $target,
    ) {
        $this->tableColumns = new TableColumns();
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

        $grouped = [];
        $grouped[__('import_mapping.group_none')] = ['' => __('import_mapping.ignore')];

        $placed = [];
        foreach ($this->fieldGroups($model, $placed) as $group => $options) {
            $grouped[$group] = $options;
        }

        $remaining = $this->remainingFields($model, $placed);
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

        $notes = $this->notes($model);
        if ($notes !== []) {
            $grouped[__('import_mapping.group_notes')] = $notes;
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
    private function fieldGroups(Model $model, array &$placed): array
    {
        $fillable = $model->getFillable();
        $groups = [];

        foreach ($this->target->fieldGroups() as $groupKey => $attributes) {
            $options = [];

            foreach ($attributes as $attribute) {
                // The groups are configuration; a group naming a field the
                // model or the form does not have is a mistake to fix, not
                // to hide.
                Assert::inArray($attribute, $fillable);
                Assert::true($this->isColumn($model, $attribute));
                Assert::false($this->isInternal($attribute));
                Assert::true($this->isOnForm($attribute));

                $options[$attribute] = $this->attributeLabel($attribute);
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
    private function remainingFields(Model $model, array $placed): array
    {
        $remaining = [];
        foreach ($model->getFillable() as $attribute) {
            if (
                !$this->isColumn($model, $attribute)
                || $this->isInternal($attribute)
                || $this->isForeignKey($model, $attribute)
                || !$this->isOnForm($attribute)
                || in_array($attribute, $placed, true)
            ) {
                continue;
            }

            $remaining[$attribute] = $this->attributeLabel($attribute);
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
            $lookups[RelationKey::lookup($lookupTarget->key)]
                = FormFields::label($this->target, $lookupTarget->foreignKey) ?? __($lookupTarget->labelKey);
        }

        return $lookups;
    }

    /**
     * A link is called what the form calls it; a link the form has no field
     * for (the other register that refers to this one) keeps its own name.
     *
     * @return array<string, string>
     */
    private function relations(): array
    {
        $relations = [];
        foreach ($this->target->relations() as $relationTarget) {
            $label = FormFields::relationLabel($this->target, $relationTarget->key) ?? __($relationTarget->labelKey);
            $relations[$relationTarget->key] = $label;

            // A source may spread one related record over several columns: a
            // name in one, an e-mail address in the next.
            foreach ($relationTarget->extraAttributes as $attribute => $attributeLabelKey) {
                $relations[RelationKey::attribute($relationTarget->key, $attribute)]
                    = sprintf('%s — %s', $label, __($attributeLabelKey));
            }

            // A related record can carry a sub-record of its own, such as an
            // address, spread over further columns.
            $subRecord = ImportTarget::subRecords()[$relationTarget->key] ?? null;

            if ($subRecord === null) {
                continue;
            }

            foreach ($subRecord->attributes as $attribute => $attributeLabelKey) {
                $key = RelationKey::attribute($relationTarget->key, sprintf('%s.%s', $subRecord->key, $attribute));

                $relations[$key] = sprintf('%s — %s', $label, __($attributeLabelKey));
            }
        }

        return $relations;
    }

    /**
     * Text without a field of its own can still be kept, as a note on the
     * record. Only registers that have notes offer it; any number of columns
     * may go there, each becoming a note of its own. The FG's note is the
     * FG's alone: only someone who may read it may fill it.
     *
     * @return array<string, string>
     */
    private function notes(Model $model): array
    {
        $notes = [];

        if (method_exists($model, 'remarks')) {
            $notes[RelationKey::REMARKS] = __('import_mapping.field_remarks');
        }

        $mayReadFgNotes = Authorization::hasPermission(Permission::CORE_ENTITY_FG_REMARKS);
        if (method_exists($model, 'fgRemark') && $mayReadFgNotes) {
            $notes[RelationKey::FG_REMARK] = __('import_mapping.field_fg_remark');
        }

        // The keys stand for relations and must not shadow a column.
        Assert::isEmpty(array_intersect(array_keys($notes), $model->getFillable()));

        return $notes;
    }

    /**
     * A fillable name that has no column behind it (a leftover in the model)
     * would be accepted by the mapping and refused by the database.
     */
    private function isColumn(Model $model, string $attribute): bool
    {
        return $this->tableColumns->type($model, $attribute) !== null;
    }

    private function isForeignKey(Model $model, string $attribute): bool
    {
        return str_ends_with($attribute, self::INTERNAL_SUFFIX) && $this->tableColumns->type($model, $attribute) === 'uuid';
    }

    private function isInternal(string $attribute): bool
    {
        return in_array($attribute, self::INTERNAL_ATTRIBUTES, true);
    }

    /**
     * A fillable the form has no field for is a leftover of an earlier data
     * model, not something a sheet can fill.
     */
    private function isOnForm(string $attribute): bool
    {
        return $attribute === self::SOURCE_REFERENCE || FormFields::has($this->target, $attribute);
    }

    /**
     * The label the form shows for the field; the attribute name is an
     * implementation detail the user has no use for.
     */
    private function attributeLabel(string $attribute): string
    {
        if ($attribute === self::SOURCE_REFERENCE) {
            return __('import_mapping.field_import_id');
        }

        $label = FormFields::label($this->target, $attribute);
        Assert::notNull($label);

        return $label;
    }
}
