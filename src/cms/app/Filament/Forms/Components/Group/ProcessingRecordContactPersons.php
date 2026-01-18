<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Group;

use App\Facades\Authentication;
use App\Filament\Forms\Components\SelectMultipleWithLookup;
use App\Filament\Resources\LookupListResource\LookupListResourceForm;
use App\Models\ContactPerson;
use App\Models\OrganisationUser;
use App\Rules\CurrentOrganisation;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

use function __;

class ProcessingRecordContactPersons extends Group
{
    public static function makeGroup(): static
    {
        return parent::make([
            Select::make('users')
                ->multiple()
                ->relationship('users', 'name', static function (Builder $query): void {
                    $query->whereAttachedTo(Authentication::organisation());
                })
                ->rules([CurrentOrganisation::forModel(OrganisationUser::class, 'user_id')])
                ->default([Authentication::user()->id->toString()])
                ->label(__('contact_person.form_title_users')),
            SelectMultipleWithLookup::makeForRelationshipWithCreate(
                'contactPersons',
                'contactPersons',
                ContactPerson::class,
                LookupListResourceForm::getSchema(),
                'name',
            )
                ->label(__('contact_person.form_title_contact_persons')),
        ]);
    }
}
