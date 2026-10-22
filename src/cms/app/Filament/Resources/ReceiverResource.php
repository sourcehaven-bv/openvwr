<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\RelationManagers\AvgProcessorProcessingRecordRelationManager;
use App\Filament\RelationManagers\AvgResponsibleProcessingRecordRelationManager;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\Resources\ReceiverResource\Pages\CreateReceiver;
use App\Filament\Resources\ReceiverResource\Pages\EditReceiver;
use App\Filament\Resources\ReceiverResource\Pages\ListReceivers;
use App\Filament\Resources\ReceiverResource\Pages\ViewReceiver;
use App\Filament\Resources\ReceiverResource\ReceiverResourceForm;
use App\Filament\Resources\ReceiverResource\ReceiverResourceInfolist;
use App\Filament\Resources\ReceiverResource\ReceiverResourceTable;
use App\Models\Receiver;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

use function __;

class ReceiverResource extends Resource
{
    protected static ?string $model = Receiver::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::MANAGEMENT->value);
    }

    public static function form(Schema $schema): Schema
    {
        return ReceiverResourceForm::form($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReceiverResourceInfolist::infolist($schema);
    }

    public static function table(Table $table): Table
    {
        return ReceiverResourceTable::table($table);
    }

    public static function getRelations(): array
    {
        return [
            SnapshotsRelationManager::class,
            AvgResponsibleProcessingRecordRelationManager::class,
            AvgProcessorProcessingRecordRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReceivers::route('/'),
            'create' => CreateReceiver::route('/create'),
            'view' => ViewReceiver::route('/{record}'),
            'edit' => EditReceiver::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('receiver.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('receiver.model_plural');
    }
}
