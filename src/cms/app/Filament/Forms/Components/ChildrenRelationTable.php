<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Components\Uuid\UuidInterface;
use App\Filament\TenantScoped;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Webmozart\Assert\Assert;

use function __;
use function array_diff;
use function in_array;
use function is_iterable;

/**
 * A RelationTable over the self-referencing `children` relation
 * (subverwerkingen). The Select relationship machinery loads the linked ids
 * but does not save HasMany relations, so linking and unlinking happens here
 * by (re)setting the child's parent_id — the same column a subverwerking sets
 * through its own ParentSelect.
 */
class ChildrenRelationTable extends RelationTable
{
    /**
     * @param class-string<Model> $model
     */
    public static function makeForChildren(string $model): static
    {
        $component = self::makeForRelationship(
            'children',
            'children',
            $model,
            'name',
            RelationTableColumns::for($model),
            scope: static function (Builder $query, ?Model $record = null): void {
                (TenantScoped::getAsClosure())($query);

                // The picker only offers records that can become a
                // subverwerking of this one: not itself or one of its
                // ancestors (no cycles) and not already a subverwerking of
                // another record. Its current children stay included so the
                // selected options keep resolving.
                if (!$record instanceof Model || !$record->exists) {
                    $query->whereNull('parent_id');

                    return;
                }

                $query
                    ->whereKeyNot([self::keyString($record), ...self::ancestorKeys($record)])
                    ->where(static function (Builder $query) use ($record): void {
                        $query
                            ->whereNull('parent_id')
                            ->orWhere('parent_id', self::keyString($record));
                    });
            },
        );

        // Rendering the linked children must not use the picker filter above:
        // the linked children have a parent (this record) and would disappear
        // from the table.
        $component->tenantScope = TenantScoped::getAsClosure();

        return $component
            // The default Select loader leaves the plucked keys as uuid
            // objects; the state must hold plain strings (as the client-side
            // control round-trips them).
            ->loadStateFromRelationshipsUsing(static function (self $component, ?Model $record): void {
                $ids = [];

                $children = $record?->getAttribute('children');
                if (is_iterable($children)) {
                    foreach ($children as $child) {
                        if ($child instanceof Model) {
                            $ids[] = self::keyString($child);
                        }
                    }
                }

                $component->state($ids);
            })
            ->label(__('general.children'))
            ->helperText(__('general.children_help'))
            ->saveRelationshipsUsing(static function (self $component, Model $record): void {
                $component->saveChildren($record);
            });
    }

    /**
     * Syncs the children to the field state by re-parenting: removed children
     * become standalone records again, added records get this record as their
     * parent. The adds are restricted exactly like the picker, so a tampered
     * state cannot steal another record's subverwerking or create a cycle.
     */
    private function saveChildren(Model $record): void
    {
        $ids = $this->getStateIds();

        /** @var Model $model */
        $model = new $this->relatedModel();

        /** @var array<int, string> $currentIds */
        $currentIds = [];
        $currentChildren = $model->newQuery()
            ->tap(TenantScoped::getAsClosure())
            ->where('parent_id', self::keyString($record))
            ->get();

        foreach ($currentChildren as $child) {
            $currentIds[] = self::keyString($child);

            if (in_array(self::keyString($child), $ids, true)) {
                continue;
            }

            $child->setAttribute('parent_id', null);
            $child->save();
        }

        $toAttach = array_diff($ids, $currentIds);

        if ($toAttach === []) {
            return;
        }

        $candidates = $model->newQuery()
            ->tap(TenantScoped::getAsClosure())
            ->whereIn($model->getKeyName(), $toAttach)
            ->whereKeyNot([self::keyString($record), ...self::ancestorKeys($record)])
            ->whereNull('parent_id')
            ->get();

        foreach ($candidates as $child) {
            $child->setAttribute('parent_id', self::keyString($record));
            $child->save();
        }
    }

    /**
     * The keys of the record's ancestors, walked up the parent chain. Guarded
     * against already-cyclic data to keep the walk finite.
     *
     * @return array<int, string>
     */
    private static function ancestorKeys(Model $record): array
    {
        $keys = [];
        $seen = [self::keyString($record)];
        $parent = $record->getAttribute('parent');

        while ($parent instanceof Model && !in_array(self::keyString($parent), $seen, true)) {
            $keys[] = self::keyString($parent);
            $seen[] = self::keyString($parent);
            $parent = $parent->getAttribute('parent');
        }

        return $keys;
    }

    private static function keyString(Model $record): string
    {
        $key = $record->getKey();
        Assert::isInstanceOf($key, UuidInterface::class);

        return $key->toString();
    }
}
