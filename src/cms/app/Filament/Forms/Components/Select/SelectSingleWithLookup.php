<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Select;

use App\Components\Uuid\UuidInterface;
use App\Enums\Authorization\Permission;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\TenantScoped;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Webmozart\Assert\Assert;

use function array_keys;

class SelectSingleWithLookup extends Select
{
    /**
     * @param class-string<Model> $relatedModel
     */
    public static function makeWithDisabledOptions(
        string $name,
        string $relationshipName,
        string $relatedModel,
        string $relatedField,
    ): static {
        /** @var Collection<int, string> $disabled */
        $disabled = $relatedModel::select('id')
            ->where('enabled', false)
            ->whereBelongsTo(Authentication::organisation())
            ->get()
            ->pluck('id');

        return parent::make($name)
            ->required()
            ->relationship($relationshipName, $relatedField, TenantScoped::getAsClosure())
            // These are bounded lookup lists, so load the options up front:
            // otherwise the dropdown stays empty until the user starts typing.
            ->preload()
            ->in(static function (SelectSingleWithLookup $select): array {
                return array_keys($select->getEnabledOptions());
            })
            ->visible(Authorization::hasPermission(Permission::LOOKUP_LIST_VIEW))
            ->createOptionForm([
                TextInput::make($relatedField)
                    ->required(),
                Hidden::make('enabled')
                    ->default(true),
                Hidden::make('organisation_id')
                    ->default(Authentication::organisation()->id->toString()),
            ])
            ->formatStateUsing(static function (string|UuidInterface|null $state): ?string {
                if ($state === null) {
                    return null;
                }

                if ($state instanceof UuidInterface) {
                    return $state->toString();
                }

                return $state;
            })
            ->createOptionUsing(static function (array $data) use ($relatedModel): string {
                Assert::isMap($data);

                $key = $relatedModel::create($data)->getKey();
                Assert::isInstanceOf($key, UuidInterface::class);

                return $key->toString();
            })
            ->disableOptionWhen(static function (string $value) use ($disabled): bool {
                return $disabled->contains($value);
            });
    }
}
