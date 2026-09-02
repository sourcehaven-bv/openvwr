<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganisationUserResource;

use App\Filament\Infolists\Components\Section\OrganisationUserRolesSection;
use App\Filament\Infolists\Components\Section\UserGlobalRolesSection;
use App\Filament\Infolists\Components\Section\UserSection;
use App\Models\User;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class OrganisationUserResourceInfolist
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
     * @return array<Component>
     */
    public static function getSchema(User $user): array
    {
        return [
            UserSection::make(),
            UserGlobalRolesSection::make(),
            OrganisationUserRolesSection::makeForUser($user),
        ];
    }
}
