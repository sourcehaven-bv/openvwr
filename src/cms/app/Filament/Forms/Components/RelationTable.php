<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Components\Uuid\UuidInterface;
use App\Facades\Authentication;
use App\Filament\TenantScoped;
use App\Rules\CurrentOrganisation;
use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrManyThrough;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

use function array_merge;
use function array_values;
use function is_iterable;
use function is_string;

/**
 * A many-to-many relation field that renders the linked records as a compact
 * table (one configurable column per property) with per-row remove buttons,
 * instead of a flat chip list.
 *
 * It extends Select so the proven relationship-sync, searchable "add" control
 * and inline "create option" form are reused as-is; only the display of the
 * already-linked records is replaced by the accompanying blade view.
 */
class RelationTable extends Select
{
    public const REMOVE_ACTION = 'remove';

    protected string $view = 'filament.forms.components.relation-table';

    /** @var class-string<Model> */
    protected string $relatedModel;

    /** @var array<int, array{label: string, get: Closure(Model): (string|null), href?: Closure(Model): (string|null), download?: Closure(Model): (string|null)}> */
    protected array $tableColumns = [];

    /**
     * @param class-string<Model> $model
     * @param array<int, array{label: string, get: Closure(Model): (string|null), href?: Closure(Model): (string|null), download?: Closure(Model): (string|null)}> $columns
     * @param array<Component> $createForm the schema for the inline "create new" modal
     */
    public static function makeForRelationship(
        string $name,
        string $relationshipName,
        string $model,
        string $titleAttribute,
        array $columns,
        array $createForm = [],
    ): static {
        $component = parent::make($name);

        $component
            ->relationship($relationshipName, $titleAttribute, TenantScoped::getAsClosure())
            ->multiple()
            ->searchable([$titleAttribute])
            // Commit selections to the server so the table re-renders when a
            // record is linked (the choices.js control is otherwise client-only).
            ->live()
            ->rules([CurrentOrganisation::forModel($model)]);

        $component->relatedModel = $model;
        $component->tableColumns = $columns;

        $component->registerActions([
            Action::make(self::REMOVE_ACTION)
                ->action(static function (Action $action, array $arguments): void {
                    $id = $arguments['id'] ?? null;

                    if (!is_string($id)) {
                        return;
                    }

                    $component = $action->getComponent();

                    // @codeCoverageIgnoreStart
                    if (!$component instanceof self) {
                        return;
                    }
                    // @codeCoverageIgnoreEnd

                    /** @var array<int, string> $state */
                    $state = [];
                    foreach ((array) $component->getState() as $value) {
                        if (is_string($value) && $value !== '' && $value !== $id) {
                            $state[] = $value;
                        }
                    }

                    $component->state(array_values($state));
                    $component->callAfterStateUpdated();
                }),
        ]);

        if ($createForm !== []) {
            $component
                ->createOptionForm(array_merge($createForm, [
                    Hidden::make('organisation_id')
                        ->default(Authentication::organisation()->id->toString()),
                ]))
                ->createOptionUsing(static function (Select $component, array $data, Form $form): string {
                    Assert::isMap($data);

                    $record = self::getEloquentRelationship($component)->getRelated();
                    $record->fill($data);
                    $record->save();

                    $form->model($record)->saveRelationships();

                    $key = $record->getKey();
                    Assert::isInstanceOf($key, UuidInterface::class);

                    return $key->toString();
                });
        }

        return $component;
    }

    /**
     * The Select::getRelationship() signature includes an unresolvable
     * "BelongsToThrough" class; this narrows it to the Eloquent relations we
     * actually support.
     *
     * @return BelongsTo<Model, Model>|BelongsToMany<Model, Model>|HasOneOrMany<Model, Model, Collection<array-key, Model>>|HasOneOrManyThrough<Model, Model, Model, Collection<array-key, Model>>
     *
     * @codeCoverageIgnore
     */
    private static function getEloquentRelationship(Select $component): BelongsTo|BelongsToMany|HasOneOrMany|HasOneOrManyThrough
    {
        $relationship = $component->getRelationship();

        if (
            $relationship instanceof BelongsTo
            || $relationship instanceof BelongsToMany
            || $relationship instanceof HasOneOrMany
            || $relationship instanceof HasOneOrManyThrough
        ) {
            return $relationship;
        }

        throw new InvalidArgumentException('unsupported relation type');
    }

    /**
     * The records currently linked, resolved from the field state so the table
     * reflects unsaved add/remove edits made in this form session.
     *
     * @return Collection<int, Model>
     */
    public function getLinkedRecords(): Collection
    {
        $state = $this->getState();

        /** @var array<int, string> $ids */
        $ids = [];
        if (is_iterable($state)) {
            foreach ($state as $value) {
                if (is_string($value) && $value !== '') {
                    $ids[] = $value;
                }
            }
        }

        /** @var Model $model */
        $model = new $this->relatedModel();

        if ($ids === []) {
            /** @var Collection<int, Model> $empty */
            $empty = $model->newCollection();

            return $empty;
        }

        // The ids come from live, client-influenced form state, so this render
        // path must be tenant-scoped just like the search query above:
        // without it a crafted id would render another organisation's record.
        /** @var Collection<int, Model> $records */
        $records = $model->newQuery()
            ->tap(TenantScoped::getAsClosure())
            ->whereIn($model->getKeyName(), $ids)
            ->get();

        return $records;
    }

    /**
     * @return array<int, array{label: string, get: Closure(Model): (string|null), href?: Closure(Model): (string|null), download?: Closure(Model): (string|null)}>
     */
    public function getTableColumns(): array
    {
        return $this->tableColumns;
    }
}
