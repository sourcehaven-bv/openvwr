<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganisationResource;

use App\Components\Uuid\UuidInterface;
use App\Enums\Authorization\Permission;
use App\Enums\Media\MediaGroup;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\Forms\Components\MarkdownEditor\MarkdownEditor;
use App\Filament\Forms\Components\PublicFromField;
use App\Filament\Forms\Components\TextInput\EntityNumberPrefix;
use App\Models\Organisation;
use App\Models\ResponsibleLegalEntity;
use App\Rules\IPRanges;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

use function __;

class OrganisationResourceForm
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->heading(__('organisation.section_general'))
                    ->columns()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('general.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('review_at_default_in_months')
                            ->label(__('organisation.review_at_default_in_months'))
                            ->required()
                            ->default(36)
                            ->integer(),
                        Select::make('responsible_legal_entity_id')
                            ->label(__('responsible_legal_entity.model_singular'))
                            ->relationship('responsibleLegalEntity', 'name')
                            ->searchable(['name'])
                            ->required()
                            ->exists(ResponsibleLegalEntity::class, 'id')
                            ->formatStateUsing(static function (string|UuidInterface $state): string {
                                if ($state instanceof UuidInterface) {
                                    return $state->toString();
                                }

                                return $state;
                            }),
                    ]),
                Section::make()
                    ->heading(__('organisation.section_prefix'))
                    ->columns()
                    ->schema([
                        EntityNumberPrefix::makeForField('register'),
                        EntityNumberPrefix::makeForField('databreach'),
                    ]),
                Section::make()
                    ->heading(__('organisation.section_public'))
                    ->columns()
                    ->schema([
                        TextInput::make('slug')
                            ->label(__('organisation.slug'))
                            ->unique(ignoreRecord: true, modifyRuleUsing: static function (Unique $rule): void {
                                $rule->withoutTrashed();
                            })
                            ->afterStateUpdated(static function (Set $set, string $state): void {
                                $set('slug', Str::slug($state));
                            })
                            ->required()
                            ->maxLength(255),
                        PublicFromField::makeForModel(Organisation::class)
                            ->hintIcon('heroicon-o-information-circle', __('organisation.public_from_hint_icon_text')),
                        Textarea::make('allowed_ips')
                            ->label(__('organisation.allowed_ips'))
                            ->required()
                            ->default('*.*.*.*')
                            ->rules([new IPRanges()])
                            ->visible(Authorization::hasPermission(Permission::ORGANISATION_UPDATE_IP_WHITELIST)),
                        Repeater::make('allowed_email_domains')
                            ->label(__('organisation.allowed_email_domains'))
                            ->helperText(__('organisation.allowed_email_domains_help'))
                            ->addActionLabel(__('organisation.allowed_email_domains_add'))
                            ->reorderable(false)
                            ->simple(
                                TextInput::make('domain')
                                    ->prefix('@')
                                    ->required(),
                            ),
                        MarkdownEditor::make('public_website_content')
                            ->label(__('organisation.public_website_content'))
                            ->columnSpan(2),
                        SpatieMediaLibraryFileUpload::make('poster')
                            ->label(__('organisation.poster'))
                            ->imageEditor()
                            ->acceptedFileTypes(['image/jpeg'])
                            ->imagePreviewHeight('295')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('33:8')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('480')
                            ->panelAspectRatio('33:8')
                            ->panelLayout('integrated')
                            ->columnSpanFull()
                            ->properties([
                                'organisation_id' => Authentication::organisation()->id->toString(),
                            ])
                            ->collection(MediaGroup::ORGANISATION_POSTERS->value)
                            ->image(),
                    ]),
            ]);
    }
}
