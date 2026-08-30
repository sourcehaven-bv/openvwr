<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use App\Filament\Resources\ContactPersonResource\Pages\ListContactPersons;
use App\Filament\Resources\ContactPersonResource\Pages\CreateContactPerson;
use App\Filament\Resources\ContactPersonResource\Pages\ViewContactPerson;
use App\Filament\Resources\ContactPersonResource\Pages\EditContactPerson;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\RelationManagers\AvgProcessorProcessingRecordRelationManager;
use App\Filament\RelationManagers\AvgResponsibleProcessingRecordRelationManager;
use App\Filament\RelationManagers\ContactPersonPositionRelationManager;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\RelationManagers\WpgProcessingRecordRelationManager;
use App\Filament\Resources\ContactPersonResource\ContactPersonResourceForm;
use App\Filament\Resources\ContactPersonResource\ContactPersonResourceInfolist;
use App\Filament\Resources\ContactPersonResource\ContactPersonResourceTable;
use App\Filament\Resources\ContactPersonResource\Pages;
use App\Models\ContactPerson;
use Filament\Tables\Table;

use function __;

class ContactPersonResource extends Resource
{
    protected static ?string $model = ContactPerson::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left';
    protected static ?int $navigationSort = 10;
    protected static ?string $tenantRelationshipName = 'contactPersons';

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::MANAGEMENT->value);
    }

    public static function form(Schema $schema): Schema
    {
        return ContactPersonResourceForm::form($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContactPersonResourceInfolist::infolist($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactPersonResourceTable::table($table);
    }

    public static function getRelations(): array
    {
        return [
            SnapshotsRelationManager::class,
            ContactPersonPositionRelationManager::class,
            AvgResponsibleProcessingRecordRelationManager::class,
            AvgProcessorProcessingRecordRelationManager::class,
            WpgProcessingRecordRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactPersons::route('/'),
            'create' => CreateContactPerson::route('/create'),
            'view' => ViewContactPerson::route('/{record}'),
            'edit' => EditContactPerson::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('contact_person.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('contact_person.model_plural');
    }
}
