<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Config\Feature;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\Resources\PublicWebsiteTreeResource\Pages\ListPublicWebsiteTrees;
use App\Filament\Resources\PublicWebsiteTreeResource\PublicWebsiteTreeResourceForm;
use App\Models\PublicWebsiteTree;
use Filament\Forms\Form;
use Filament\Resources\Resource;

use function __;

class PublicWebsiteTreeResource extends Resource
{
    protected static ?string $model = PublicWebsiteTree::class;
    protected static ?string $navigationIcon = 'heroicon-o-numbered-list';
    protected static ?int $navigationSort = 5;
    protected static bool $isScopedToTenant = false;
    protected static ?string $slug = 'public-website-tree';

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::FUNCTIONAL_MANAGEMENT->value);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Feature::publishingEnabled();
    }

    public static function form(Form $form): Form
    {
        return PublicWebsiteTreeResourceForm::form($form);
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
