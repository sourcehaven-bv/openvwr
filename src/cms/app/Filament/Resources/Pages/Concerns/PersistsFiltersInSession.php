<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Concerns;

use Filament\Tables\Table;

trait PersistsFiltersInSession
{
    final public function table(Table $table): Table
    {
        return parent::table($table)->persistFiltersInSession();
    }
}
