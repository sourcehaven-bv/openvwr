<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components\Section;

use App\Enums\Authorization\Role;
use App\Filament\Infolists\Components\UserGlobalRoleEntry;
use Closure;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\Support\Htmlable;
use Webmozart\Assert\Assert;

use function __;

class UserGlobalRolesSection extends Section
{
    public static function make(Htmlable|array|Closure|string|null $heading = null): static
    {
        $defaultHeading = __('user.global_roles');
        Assert::string($defaultHeading);

        return parent::make($heading ?? $defaultHeading)
            ->columns()
            ->schema([
                ...self::getGlobalRoleEntries(),
            ]);
    }

    /**
     * @return array<UserGlobalRoleEntry>
     */
    private static function getGlobalRoleEntries(): array
    {
        $organisationUserRoleEntries = [];

        foreach (Role::globalRoles() as $globalRole) {
            $organisationUserRoleEntries[] = UserGlobalRoleEntry::makeForUser($globalRole);
        }

        return $organisationUserRoleEntries;
    }
}
