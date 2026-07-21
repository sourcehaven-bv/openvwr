<?php

declare(strict_types=1);

namespace App\Filament\RelationManagers;

use App\Filament\Resources\DocumentResource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DocumentRelationManager extends RelationManager
{
    protected static string $languageFile = 'document';
    protected static string $relationship = 'documents';
    protected static string $resource = DocumentResource::class;

    public function table(Table $table): Table
    {
        return DocumentResource::table($table)
            ->recordUrl(static function (Model $record): string {
                return DocumentResource::getUrl('view', ['record' => $record]);
            });
    }
}
