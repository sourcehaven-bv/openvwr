<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Components\Uuid\UuidInterface;
use App\Facades\Authentication;
use App\Filament\TenantScoped;
use App\Rules\CurrentOrganisation;
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

class SelectMultipleWithLookup extends Select
{
    public static function makeForRelationship(
        string $name,
        string $relationshipName,
        string $model,
        string $titleAttribute,
    ): static {
        return parent::make($name)
            ->relationship($relationshipName, $titleAttribute, TenantScoped::getAsClosure())
            ->multiple()
            ->rules([CurrentOrganisation::forModel($model)])
            ->searchable([$titleAttribute]);
    }

    /**
     * @param array<Component> $formSchema
     */
    public static function makeForRelationshipWithCreate(
        string $name,
        string $relationshipName,
        string $model,
        array $formSchema,
        string $titleAttribute,
    ): static {
        return parent::make($name)
            ->relationship($relationshipName, $titleAttribute, TenantScoped::getAsClosure())
            ->createOptionForm(array_merge($formSchema, [
                Hidden::make('organisation_id')
                    ->default(Authentication::organisation()->id->toString()),
            ]))
            ->createOptionUsing(static function (Select $component, array $data, Form $form): string {
                Assert::isMap($data);
                $relationship = self::getEloquentRelationship($component);

                $record = $relationship->getRelated();
                $record->fill($data);
                $record->save();

                $form->model($record)->saveRelationships();

                $key = $record->getKey();
                Assert::isInstanceOf($key, UuidInterface::class);

                return $key->toString();
            })
            ->rules([CurrentOrganisation::forModel($model)])
            ->multiple()
            ->searchable([$titleAttribute]);
    }

    /**
     * The Select::getRelationship()-method signature contains an "unknown"-class as possible return value. I created a PR to add it to
     * filament as a dependency (https://github.com/filamentphp/filament/pull/16174) but this was not accepted.
     * This method makes sure only Eloquent-relations can be returned.
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
}
