<?php

declare(strict_types=1);

namespace App\Filament\Resources\PublicWebsiteTreeResource\Pages;

use App\Config\Feature;
use App\Filament\Resources\PublicWebsiteTreeResource;
use App\Models\PublicWebsiteTree;
use Filament\Actions\CreateAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use SolutionForest\FilamentTree\Resources\Pages\TreePage;
use Webmozart\Assert\Assert;

use function __;
use function array_filter;
use function view;

class ListPublicWebsiteTrees extends TreePage
{
    protected static string $resource = PublicWebsiteTreeResource::class;
    protected static ?string $breadcrumb = '';

    /**
     * The v4 tree page derives its title from the class name ("List Public
     * Website Trees"); v2 used the resource label.
     */
    public function getTitle(): string
    {
        return PublicWebsiteTreeResource::getPluralModelLabel();
    }

    /**
     * With an empty page crumb v5 still renders it, as a trailing separator.
     *
     * @return array<array-key, string>
     */
    public function getBreadcrumbs(): array
    {
        return array_filter(parent::getBreadcrumbs(), static fn (string $crumb): bool => $crumb !== '');
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->configureCreateAction(CreateAction::make())
                ->label(__('public_website_tree.create')),
        ];
    }

    /**
     * The tree page replaces the resource's own access check, so the feature flag
     * has to be repeated here: without it the url stays reachable for a tenant
     * that does not publish, even though the menu entry is already hidden.
     *
     * @param array<array-key, mixed> $parameters
     */
    public static function canAccess(array $parameters = []): bool
    {
        return Feature::publishingEnabled() && Gate::allows('update', PublicWebsiteTree::class);
    }

    public function getTreeRecordDescription(?Model $record = null): HtmlString
    {
        Assert::isInstanceOf($record, PublicWebsiteTree::class);

        return new HtmlString(view('filament.resources.public_website_tree.description', [
            'publicWebsiteTree' => $record,
            'publishingEnabled' => Feature::publishingEnabled(),
        ])->render());
    }

    public function getTreeRecordIcon(?Model $record = null): string
    {
        Assert::isInstanceOf($record, PublicWebsiteTree::class);

        // Without publishing every node is private, so there is nothing for the
        // eye icon to distinguish.
        if (!Feature::publishingEnabled()) {
            return 'heroicon-o-document-text';
        }

        $publicationDate = $record->public_from;

        if ($publicationDate === null || $publicationDate->isFuture()) {
            return 'heroicon-o-eye-slash';
        }

        return 'heroicon-o-eye';
    }
}
