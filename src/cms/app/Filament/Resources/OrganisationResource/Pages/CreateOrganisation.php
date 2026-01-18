<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganisationResource\Pages;

use App\Components\Uuid\UuidInterface;
use App\Enums\EntityNumberType;
use App\Filament\Pages\CreateRecord;
use App\Filament\Resources\OrganisationResource;
use App\Models\EntityNumberCounter;
use App\Models\ResponsibleLegalEntity;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Validation\Rules\Unique;

use function __;

class CreateOrganisation extends CreateRecord
{
    use HasWizard;

    protected static string $resource = OrganisationResource::class;

    /**
     * @return array<Step>
     */
    protected function getSteps(): array
    {
        return [
            Step::make(__('general.name'))
                ->schema([
                    Grid::make()
                        ->schema([
                            TextInput::make('name')
                                ->label(__('general.name'))
                                ->required(),
                            TextInput::make('slug')
                                ->label(__('organisation.slug'))
                                ->required()
                                ->unique(),
                        ]),
                    Select::make('responsible_legal_entity_id')
                        ->label(__('responsible_legal_entity.model_singular'))
                        ->relationship('responsibleLegalEntity', 'name')
                        ->exists(ResponsibleLegalEntity::class, 'id')
                        ->required(),
                ]),
            Step::make(__('organisation.entity_number_prefix'))
                ->schema([
                    Grid::make()
                        ->schema([
                            TextInput::make('register_entity_number_counter_id')
                                ->label(__('organisation.register_entity_number_prefix'))
                                ->required()
                                ->rules(['alpha'])
                                ->dehydrateStateUsing(static function (string $state): UuidInterface {
                                    $entityNumberCounter = EntityNumberCounter::create([
                                        'type' => EntityNumberType::REGISTER,
                                        'prefix' => $state,
                                    ]);

                                    return $entityNumberCounter->id;
                                })
                                ->unique('entity_number_counters', 'prefix', modifyRuleUsing: static function (Unique $rule): void {
                                    $rule->where('type', 'register');
                                }),
                            TextInput::make('databreach_entity_number_counter_id')
                                ->label(__('organisation.databreach_entity_number_prefix'))
                                ->required()
                                ->rules(['alpha'])
                                ->dehydrateStateUsing(static function (string $state): UuidInterface {
                                    $entityNumberCounter = EntityNumberCounter::create([
                                        'type' => EntityNumberType::DATABREACH,
                                        'prefix' => $state,
                                    ]);

                                    return $entityNumberCounter->id;
                                })
                                ->unique('entity_number_counters', 'prefix', modifyRuleUsing: static function (Unique $rule): void {
                                    $rule->where('type', 'databreach');
                                }),
                        ]),
                ]),
        ];
    }
}
