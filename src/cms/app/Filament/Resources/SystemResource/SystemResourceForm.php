<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemResource;

use Filament\Schemas\Schema;
use App\Facades\Authentication;
use App\Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Illuminate\Validation\Rules\Unique;

use function __;

class SystemResourceForm
{
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(self::getSchema());
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    public static function getSchema(): array
    {
        return [
            TextInput::make('description')
                ->label(__('system.description'))
                ->helperText(__('system.help_description'))
                ->unique(ignoreRecord: true, modifyRuleUsing: static function (Unique $rule): void {
                    $rule->where('organisation_id', Authentication::organisation()->id->toString());
                })
                ->required()
                ->maxLength(255),
            TagsInput::make(),
        ];
    }
}
