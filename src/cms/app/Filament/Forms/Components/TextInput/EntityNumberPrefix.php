<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\TextInput;

use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Set;
use App\Enums\EntityNumberType;
use App\Models\EntityNumberCounter;
use App\Models\Organisation;
use Filament\Forms\Components\TextInput;
use Webmozart\Assert\Assert;

use function __;
use function sprintf;

class EntityNumberPrefix extends TextInput
{
    public static function makeForField(string $field): static
    {
        $fieldName = sprintf('%s_entity_number_counter_id', $field);

        return parent::make($fieldName)
            ->label(__(sprintf('organisation.%s_entity_number_prefix', $field)))
            ->formatStateUsing(static function (Organisation $organisation) use ($field): string {
                $relation = sprintf('%sEntityNumberCounter', $field);
                $entityNumberCounter = $organisation->getAttribute($relation);
                Assert::isInstanceOf($entityNumberCounter, EntityNumberCounter::class);

                return $entityNumberCounter->prefix;
            })
            ->disabled()
            ->hintAction(
                // @codeCoverageIgnoreStart
                Action::make(sprintf('%s_entity_number_counter', $field))
                    ->label(__('organisation.entity_number_prefix_edit'))
                    ->action(static function (array $data, Organisation $organisation) use ($field): void {
                        $entityNumberCounter = EntityNumberCounter::create([
                            'type' => EntityNumberType::from($field),
                            'prefix' => $data['prefix'],
                        ]);
                        $property = sprintf('%s_entity_number_counter_id', $field);

                        $organisation->setAttribute($property, $entityNumberCounter->id);
                        $organisation->save();
                    })
                    ->requiresConfirmation()
                    ->schema([
                        TextInput::make('prefix')
                            ->model(EntityNumberCounter::class)
                            ->required()
                            ->rules(['alpha', 'uppercase'])
                            ->validationMessages(['unique' => __('organisation.entity_number_unique_validation_message')])
                            ->unique(),
                    ])
                    ->after(static function (array $data, Set $set) use ($fieldName): void {
                        $set($fieldName, $data['prefix']);
                    }),
                // @codeCoverageIgnoreEnd
            );
    }
}
