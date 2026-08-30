<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Repeater;

use Filament\Actions\Action;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use App\Enums\Dpia\PersonalDataType;
use App\Facades\Authentication;
use App\Filament\Forms\Components\RetentionPeriodInput;
use App\Filament\TenantScoped;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\HtmlString;

use function __;
use function e;
use function is_string;

/**
 * Paragraaf 2: the personal data, classified.
 *
 * The classification is not a formality. Bijzondere and strafrechtelijke
 * persoonsgegevens may in principle not be processed at all, and a wettelijk
 * identificatienummer only where the law allows it, so choosing one of those
 * types immediately asks for the ground that makes it lawful -- which is what
 * paragraaf 12 is about. Asking it here, next to the gegeven it belongs to,
 * keeps the two answers from drifting apart.
 */
class DpiaPersonalDataRepeater extends Repeater
{
    public static function make(?string $name = 'personalData'): static
    {
        return parent::make($name)
            ->label(__('dpia_record.personal_data'))
            ->relationship(modifyQueryUsing: TenantScoped::getAsClosure())
            ->schema(self::getPersonalDataSchema())
            ->defaultItems(0)
            ->collapsible()
            ->orderColumn('order_column')
            ->itemLabel(static function (array $state): string {
                $description = $state['description'] ?? null;

                return is_string($description) && $description !== ''
                    ? $description
                    : __('dpia_record.personal_data_item_label');
            })
            ->addActionLabel(__('dpia_record.add_personal_data'))
            ->deleteAction(static function (Action $action): Action {
                return $action->requiresConfirmation();
            });
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    private static function getPersonalDataSchema(): array
    {
        return [
            Textarea::make('description')
                ->label(__('dpia_record.personal_data_description_item'))
                ->helperText(__('dpia_record.help_personal_data_description_item'))
                ->required()
                ->rows(2)
                ->columnSpanFull(),
            Grid::make()
                ->schema([
                    Select::make('type')
                        ->label(__('dpia_record.personal_data_type'))
                        ->helperText(__('dpia_record.help_personal_data_type'))
                        ->options(PersonalDataType::options())
                        ->required()
                        ->live(),
                    TextInput::make('data_subject_category')
                        ->label(__('dpia_record.personal_data_subject_category'))
                        ->helperText(__('dpia_record.help_personal_data_subject_category')),
                    TextInput::make('source')
                        ->label(__('dpia_record.personal_data_source'))
                        ->helperText(__('dpia_record.help_personal_data_source')),
                ]),
            ...RetentionPeriodInput::make(
                'retention_period',
                __('dpia_record.personal_data_retention_period'),
                __('dpia_record.help_personal_data_retention_period'),
            ),
            // The paragraaf 12 question, asked only where it applies.
            Placeholder::make('exception_notice')
                ->hiddenLabel()
                ->content(static fn (Get $get): ?HtmlString => self::exceptionNotice($get))
                ->visible(static fn (Get $get): bool => self::requiresExceptionGround($get))
                ->columnSpanFull(),
            Textarea::make('exception_ground')
                ->label(__('dpia_record.personal_data_exception_ground'))
                ->helperText(__('dpia_record.help_personal_data_exception_ground'))
                ->rows(3)
                ->columnSpanFull()
                ->visible(static fn (Get $get): bool => self::requiresExceptionGround($get)),
            Hidden::make('organisation_id')
                ->default(Authentication::organisation()->id->toString()),
        ];
    }

    public static function requiresExceptionGround(Get $get): bool
    {
        $type = $get('type');

        if ($type instanceof PersonalDataType) {
            return $type->requiresExceptionGround();
        }

        if (!is_string($type) || $type === '') {
            return false;
        }

        return PersonalDataType::tryFrom($type)?->requiresExceptionGround() === true;
    }

    public static function exceptionNotice(Get $get): ?HtmlString
    {
        if (!self::requiresExceptionGround($get)) {
            return null;
        }

        return new HtmlString(
            '<p class="text-sm text-warning-600 dark:text-warning-400">'
            . e(__('dpia_record.personal_data_exception_notice'))
            . '</p>',
        );
    }
}
