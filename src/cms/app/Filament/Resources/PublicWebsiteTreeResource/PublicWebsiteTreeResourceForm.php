<?php

declare(strict_types=1);

namespace App\Filament\Resources\PublicWebsiteTreeResource;

use App\Components\Uuid\UuidInterface;
use App\Enums\Media\MediaGroup;
use App\Filament\Forms\Components\MarkdownEditor\MarkdownEditor;
use App\Filament\Forms\Components\PublicFromField;
use App\Filament\Forms\Components\Upload\PosterFileField;
use App\Models\Organisation;
use App\Models\PublicWebsiteTree;
use Filament\Forms\Components\Select;
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
                TextInput::make('public_url')
                    ->label(__('public_website_tree.public_url'))
                    ->url()
                    ->maxLength(255),
                PublicFromField::makeForModel(PublicWebsiteTree::class)
                    ->hintIcon('heroicon-o-information-circle', __('organisation.public_from_hint_icon_text')),
                MarkdownEditor::make('public_website_content')
                    ->label(__('organisation.public_website_content')),
                PosterFileField::make('poster')
                    ->label(__('public_website_tree.poster'))
                    ->collection(MediaGroup::PUBLIC_WEBSITE_TREE->value),
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
