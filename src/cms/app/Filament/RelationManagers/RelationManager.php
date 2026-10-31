<?php

declare(strict_types=1);

namespace App\Filament\RelationManagers;

use App\Filament\Actions\DetachSharedBulkAction;
use App\Filament\Resources\Resource;
use Filament\Resources\RelationManagers\RelationManager as FilamentRelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

use function __;
use function sprintf;

abstract class RelationManager extends FilamentRelationManager
{
    /** @var class-string<Resource> $resource */
    protected static string $resource;
    protected static string $languageFile;

    public function table(Table $table): Table
    {
        $resource = static::$resource;

        return $resource::table($table)
            ->recordUrl(static function (Model $record) use ($resource): string {
                return $resource::getUrl($resource::canEdit($record) ? 'edit' : 'view', ['record' => $record]);
            });
    }

    /**
     * Replace the bulk actions of the resource table, which delete the shared
     * records organisation-wide, with one that only removes the links.
     */
    protected function detachSharedRecords(Table $table): Table
    {
        if (!$this->getRelationship() instanceof BelongsToMany) {
            return $table;
        }

        return $table->toolbarActions([
            DetachSharedBulkAction::make(),
        ]);
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $relationshipCollection = $ownerRecord->getAttribute(static::$relationship);


        if ($relationshipCollection instanceof Collection) {
            return (string) $relationshipCollection->count();
        }

        return '0';
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __(sprintf('%s.model_plural', static::$languageFile));
    }
}
