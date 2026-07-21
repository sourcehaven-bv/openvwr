<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganisationUserResource\Pages;

use App\Filament\Resources\OrganisationUserResource;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

use function __;

class ListOrganisationUsers extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = OrganisationUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('user.organisation_role_attach')),
        ];
    }
}
