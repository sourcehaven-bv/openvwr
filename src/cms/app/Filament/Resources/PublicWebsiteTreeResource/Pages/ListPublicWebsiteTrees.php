<?php

declare(strict_types=1);

namespace App\Filament\Resources\PublicWebsiteTreeResource\Pages;

use App\Filament\Resources\PublicWebsiteTreeResource;
use App\Models\PublicWebsiteTree;
use Filament\Actions\CreateAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use SolutionForest\FilamentTree\Resources\Pages\TreePage;
use Webmozart\Assert\Assert;

use function __;
use function view;

class ListPublicWebsiteTrees extends TreePage
{
    protected static string $resource = PublicWebsiteTreeResource::class;
    protected static ?string $breadcrumb = '';

    protected function getActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('public_website_tree.create')),
        ];
    }

    public static function canAccess(array $parameters = []): bool
    {
        return Gate::allows('update', PublicWebsiteTree::class);
    }

    public function getTreeRecordDescription(?Model $record = null): HtmlString
    {
        Assert::isInstanceOf($record, PublicWebsiteTree::class);

        return new HtmlString(view('filament.resources.public_website_tree.description', ['publicWebsiteTree' => $record])->render());
    }

    public function getTreeRecordIcon(?Model $record = null): string
    {
        Assert::isInstanceOf($record, PublicWebsiteTree::class);

        $publicationDate = $record->public_from;

        if ($publicationDate === null || $publicationDate->isFuture()) {
            return 'heroicon-o-eye-slash';
        }

        return 'heroicon-o-eye';
    }
}
