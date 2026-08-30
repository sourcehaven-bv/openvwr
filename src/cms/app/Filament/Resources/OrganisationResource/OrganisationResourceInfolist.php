<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganisationResource;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Filament\Infolists\Components\DateTimeEntry;
use App\Filament\Infolists\Components\EntityNumberPrefixEntry;
use App\Filament\Infolists\Components\TextareaEntry;
use App\Models\Organisation;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\HtmlString;

use function __;
use function view;

class OrganisationResourceInfolist
{
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(self::getSchema());
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    public static function getSchema(): array
    {
        return [
            Section::make(__('organisation.section_general'))
                ->columns()
                ->schema([
                    TextEntry::make('name')
                        ->label(__('general.name')),
                    TextEntry::make('review_at_default_in_months')
                        ->label(__('organisation.review_at_default_in_months')),
                    TextEntry::make('responsibleLegalEntity.name')
                        ->label(__('responsible_legal_entity.model_singular')),
                ]),
            Section::make(__('organisation.section_prefix'))
                ->columns()
                ->schema([
                    EntityNumberPrefixEntry::make('register'),
                    EntityNumberPrefixEntry::make('databreach'),
                ]),
            Section::make(__('organisation.section_public'))
                ->columns()
                ->schema([
                    TextEntry::make('slug')
                        ->label(__('organisation.slug')),
                    DateTimeEntry::make('public_from')
                        ->label(__('general.public_from')),
                    TextareaEntry::make('allowed_ips')
                        ->label(__('organisation.allowed_ips')),
                    TextareaEntry::make('public_website_content')
                        ->label(__('organisation.public_website_content'))
                        ->markdown()
                        ->columnSpan(2),
                    TextEntry::make('name')
                        ->label(__('organisation.poster'))
                        ->columnSpan(2)
                        ->placeholder(__('general.none_selected'))
                        ->formatStateUsing(static function (Organisation $organisation): HtmlString {
                            $poster = $organisation->getFilamentPoster();
                            $view = $poster === null
                                ? view(
                                    'filament.infolists.components.entries.organisation.poster_none_selected',
                                )
                                : view(
                                    'filament.infolists.components.entries.organisation.poster',
                                    [
                                        'poster' => $poster,
                                    ],
                                );

                            return new HtmlString($view->render());
                        }),
                ]),
        ];
    }
}
