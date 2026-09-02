<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use App\Config\Feature;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\Resources\PublicWebsiteTreeResource\Pages\ListPublicWebsiteTrees;
use App\Filament\Resources\PublicWebsiteTreeResource\PublicWebsiteTreeResourceForm;
use App\Models\PublicWebsiteTree;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

use function __;

class PublicWebsiteTreeResource extends Resource
{
    protected static ?string $model = PublicWebsiteTree::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-numbered-list';
    protected static ?int $navigationSort = 5;
    protected static bool $isScopedToTenant = false;
    protected static ?string $slug = 'public-website-tree';

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::FUNCTIONAL_MANAGEMENT->value);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Feature::publishingEnabled() && parent::shouldRegisterNavigation();
    }

    /**
     * Filament checks this before it renders the resource page, so switching the
     * feature off also makes the url unreachable instead of only hiding the menu
     * entry.
     */
    public static function canViewAny(): bool
    {
        return Feature::publishingEnabled() && parent::canViewAny();
    }

    public static function canCreate(): bool
    {
        return Feature::publishingEnabled() && parent::canCreate();
    }

    public static function canView(Model $record): bool
    {
        return Feature::publishingEnabled() && parent::canView($record);
    }

    public static function canEdit(Model $record): bool
    {
        return Feature::publishingEnabled() && parent::canEdit($record);
    }

    public static function form(Schema $schema): Schema
    {
        return PublicWebsiteTreeResourceForm::form($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublicWebsiteTrees::route('/'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('public_website_tree.header');
    }
}
