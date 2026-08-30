<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components\Section;

use Filament\Schemas\Components\Section;
use App\Filament\Infolists\Components\OrganisationUserRoleRepeaterEntry;
use App\Models\User;

use function __;

class OrganisationUserRolesSection extends Section
{
    public static function makeForUser(User $user, ?string $heading = null): static
    {
        return parent::make($heading ?? __('user.organisation_roles'))
            ->columns(1)
            ->schema([
                OrganisationUserRoleRepeaterEntry::makeForUser('organisations', $user),
            ]);
    }
}
