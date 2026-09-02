<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Group;

use App\Filament\Infolists\Components\SelectMultipleEntry;
use Filament\Schemas\Components\Group;

use function __;

class ProcessingRecordContactPersons extends Group
{
    public static function makeGroup(): static
    {
        return parent::make([
            SelectMultipleEntry::make('users.name')
                ->label(__('contact_person.form_title_users'))
                ->placeholder(__('general.none_selected')),
            SelectMultipleEntry::make('contactPersons.name')
                ->label(__('contact_person.form_title_contact_persons'))
                ->placeholder(__('general.none_selected')),
        ]);
    }
}
