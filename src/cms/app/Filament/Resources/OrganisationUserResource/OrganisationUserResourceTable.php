<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganisationUserResource;

use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use App\Models\OrganisationUserRole;
use App\Models\User;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use function __;
use function sprintf;

class OrganisationUserResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('general.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('user.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('organisationRoles')
                    ->label(__('user.organisation_roles'))
                    ->formatStateUsing(static function (User $user): string {
                        return $user->organisationRoles
                            ->map(static function (OrganisationUserRole $organisationUserRole): string {
                                return __(sprintf('role.%s', $organisationUserRole->role->value));
                            })
                            ->join(', ');
                    }),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->defaultSort('users.name')
            ->emptyStateHeading(__('user.table_empty_heading'))
            ->emptyStateDescription(null)
            ->actions([
                EditAction::make()
                    ->label(''),
            ]);
    }
}
