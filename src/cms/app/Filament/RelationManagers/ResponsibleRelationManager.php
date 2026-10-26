<?php

declare(strict_types=1);

namespace App\Filament\RelationManagers;

use App\Filament\Resources\ResponsibleResource;
use Filament\Tables\Table;

class ResponsibleRelationManager extends RelationManager
{
    protected static string $languageFile = 'responsible';
    protected static string $relationship = 'responsibles';
    protected static string $resource = ResponsibleResource::class;

    public function table(Table $table): Table
    {
        return $this->detachSharedRecords(parent::table($table));
    }
}
