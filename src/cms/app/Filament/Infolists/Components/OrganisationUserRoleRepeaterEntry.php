<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use App\Enums\Authorization\Role;
use App\Models\User;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;

use function __;

class OrganisationUserRoleRepeaterEntry extends RepeatableEntry
{
    public static function makeForUser(string $name, User $user): static
    {
        return parent::make($name)
            ->hiddenLabel()
            ->columns()
            ->schema([
                TextEntry::make('name')
                    ->label(__('organisation.model_singular'))
                    ->columnSpan(2),
                ...self::getOrganisationUserRoleEntries($user),
            ]);
    }

    /**
     * @return array<OrganisationUserRoleEntry>
     */
    private static function getOrganisationUserRoleEntries(User $user): array
    {
        $organisationUserRoleEntries = [];

        foreach (Role::organisationRoles() as $organisationRole) {
            $organisationUserRoleEntries[] = OrganisationUserRoleEntry::makeForUser($user, $organisationRole);
        }

        return $organisationUserRoleEntries;
    }
}
