<?php

declare(strict_types=1);

namespace App\Filament\RelationManagers;

use App\Filament\Resources\UserResource;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Tables\Table;

use function __;

class UsersRelationManager extends RelationManager
{
    protected static string $languageFile = 'user';
    protected static string $relationship = 'users';
    protected static string $resource = UserResource::class;

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->recordTitleAttribute('email')
            ->headerActions([
                AttachAction::make()
                    ->modalHeading(__('organisation.user_attach'))
                    ->modalDescription(__('organisation.user_attach_description'))
                    ->multiple()
                    ->attachAnother(false),
            ])
            ->recordActions([
                DetachAction::make(),
            ]);
    }
}
