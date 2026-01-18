<?php

declare(strict_types=1);

namespace App\Filament\Resources\PublicWebsiteTreeResource;

use App\Components\Uuid\UuidInterface;
use App\Enums\Media\MediaGroup;
use App\Filament\Forms\Components\MarkdownEditor\MarkdownEditor;
use App\Filament\Forms\Components\PublicFromField;
use App\Models\Organisation;
use App\Models\PublicWebsiteTree;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Illuminate\Support\Str;

use function __;

class PublicWebsiteTreeResourceForm
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label(__('public_website_tree.title'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label(__('public_website_tree.slug'))
                    ->afterStateUpdated(static function (Set $set, string $state): void {
                        $set('slug', Str::slug($state));
                    })
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                PublicFromField::makeForModel(PublicWebsiteTree::class)
                    ->hintIcon('heroicon-o-information-circle', __('organisation.public_from_hint_icon_text')),
                MarkdownEditor::make('public_website_content')
                    ->label(__('organisation.public_website_content')),
                SpatieMediaLibraryFileUpload::make('poster')
                    ->label(__('public_website_tree.poster'))
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
                    ->collection(MediaGroup::PUBLIC_WEBSITE_TREE->value)
                    ->image(),
                Select::make('organisation_id')
                    ->relationship('organisation', 'name')
                    ->label(__('public_website_tree.organisation'))
                    ->unique(ignoreRecord: true)
                    ->formatStateUsing(static function (string|UuidInterface|null $state): ?string {
                        if ($state === null) {
                            return null;
                        }

                        if ($state instanceof UuidInterface) {
                            return $state->toString();
                        }

                        // @codeCoverageIgnoreStart
                        // https://github.com/minvws/nl-rdo-dataprocessing-register-private/issues/1231
                        return $state;
                        // @codeCoverageIgnoreEnd
                    })
                    ->exists(Organisation::class, 'id'),
            ]);
    }
}
