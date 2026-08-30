<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource;

use Filament\Schemas\Schema;
use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
use App\Filament\Infolists\Components\Section\OrganisationUserRolesSection;
use App\Filament\Infolists\Components\Section\UserGlobalRolesSection;
use App\Filament\Infolists\Components\Section\UserSection;
use App\Models\User;

use function __;

class UserResourceInfolist
{
    public static function infolist(Schema $schema): Schema
    {
        /** @var User $user */
        $user = $schema->record;

        return $schema
            ->columns(1)
            ->components(self::getSchema($user));
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    public static function getSchema(User $user): array
    {
        return [
            UserSection::make(__('user.model_singular')),
            UserGlobalRolesSection::make()
                ->visible(Authorization::hasPermission(Permission::USER_UPDATE)),
            OrganisationUserRolesSection::makeForUser($user)
                ->visible(Authorization::hasPermission(Permission::USER_UPDATE)),
        ];
    }
}
