<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Group;

use App\Facades\Authentication;
use App\Filament\Forms\Components\RelationTable;
use App\Filament\Forms\Components\RelationTableColumns;
use App\Filament\Resources\LookupListResource\LookupListResourceForm;
use App\Models\ContactPerson;
use App\Models\OrganisationUser;
use App\Models\User;
use App\Rules\CurrentOrganisation;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Group;
use Illuminate\Database\Eloquent\Builder;

use function __;

class ProcessingRecordContactPersons extends Group
{
    public static function makeGroup(): static
    {
        return parent::make([
            RelationTable::makeForRelationship(
                'users',
                'users',
                User::class,
                'name',
                RelationTableColumns::for(User::class),
                // Users are linked to the organisation through a pivot, not an
                // organisation_id column, so the default scope/rule don't apply.
                scope: static function (Builder $query): void {
                    $query->whereAttachedTo(Authentication::organisation());
                },
                rules: [CurrentOrganisation::forModel(OrganisationUser::class, 'user_id')],
            )
                ->preload()
                // Resolved lazily: the schema is also built where nobody is
                // acting (the queued export job rebuilds the resource to read
                // its columns), and a default contact means nothing there.
                ->default(static function (): array {
                    $user = Filament::auth()->user();

                    return $user instanceof User ? [$user->getKey()->toString()] : [];
                })
                ->label(__('contact_person.form_title_users'))
                ->helperText(__('contact_person.help_form_title_users')),
            RelationTable::makeForRelationship(
                'contactPersons',
                'contactPersons',
                ContactPerson::class,
                'name',
                RelationTableColumns::for(ContactPerson::class),
                LookupListResourceForm::getSchema(),
            )
                ->label(__('contact_person.form_title_contact_persons'))
                ->helperText(__('contact_person.help_form_title_contact_persons')),
        ]);
    }
}
