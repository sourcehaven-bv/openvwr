<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentResource;

use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
use App\Filament\Forms\Components\DatePicker\DatePicker;
use App\Filament\Forms\Components\RelationTable;
use App\Filament\Forms\Components\RelationTableColumns;
use App\Filament\Forms\Components\Select\SelectSingleWithLookup;
use App\Filament\Forms\Components\TagsInput;
use App\Filament\Forms\Components\Upload\AttachmentFileField;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\DataBreachRecord;
use App\Models\DocumentType;
use App\Models\Wpg\WpgProcessingRecord;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Webmozart\Assert\Assert;

use function __;
use function filled;

class DocumentResourceForm
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getSchema());
    }

    /**
     * @return array<Component>
     */
    public static function getSchema(): array
    {
        return [
            self::getGeneralSection(),
            self::getAttachProcessingRecordsSection(),
        ];
    }

    private static function getGeneralSection(): Section
    {
        return Section::make()
            ->columns(1)
            ->schema([
                TextInput::make('name')
                    ->label(__('document.name'))
                    ->required()
                    ->maxLength(255),
                SelectSingleWithLookup::makeWithDisabledOptions('document_type_id', 'documentType', DocumentType::class, 'name')
                    ->label(__('document.type'))
                    ->helperText(__('document.help_type')),
                TagsInput::make(),
                DatePicker::make('expires_at')
                    ->label(__('document.expires_at'))
                    ->helperText(__('document.help_expires_at'))
                    ->live()
                    ->validationMessages(['required_unless' => __('document.expires_at_required_unless')]),
                DatePicker::make('notify_at')
                    ->label(__('document.notify_at'))
                    ->helperText(__('document.help_notify_at'))
                    ->hintAction(
                        Action::make('notify_at_expires_at')
                            ->label(__('document.notification_options.expires_at'))
                            ->icon('heroicon-m-clock')
                            ->color('gray')
                            ->visible(static fn (Get $get): bool => filled($get('expires_at')))
                            ->action(static function (Get $get, Set $set): void {
                                $set('notify_at', self::readExpiresAt($get));
                            }),
                    )
                    ->hintAction(
                        Action::make('notify_at_1_month_before')
                            ->label(__('document.notification_options.1_month_before'))
                            ->icon('heroicon-m-clock')
                            ->color('gray')
                            ->visible(static fn (Get $get): bool => filled($get('expires_at')))
                            ->action(static function (Get $get, Set $set): void {
                                $set('notify_at', self::readExpiresAt($get)->subMonth());
                            }),
                    )
                    ->hintAction(
                        Action::make('notify_at_3_months_before')
                            ->label(__('document.notification_options.3_months_before'))
                            ->icon('heroicon-m-clock')
                            ->color('gray')
                            ->visible(static fn (Get $get): bool => filled($get('expires_at')))
                            ->action(static function (Get $get, Set $set): void {
                                $set('notify_at', self::readExpiresAt($get)->subMonths(3));
                            }),
                    ),
                Textarea::make('location')
                    ->label(__('document.location'))
                    ->helperText(__('document.help_location')),
                AttachmentFileField::make('Attachments'),
            ]);
    }

    /**
     * Read the expires_at field state as a date. A DatePicker stores its value
     * as a plain "Y-m-d" string, so parse leniently rather than with a fixed
     * date-time format. Only called from actions that are visible when
     * expires_at is filled, so the value is guaranteed to be a non-empty string.
     */
    private static function readExpiresAt(Get $get): CarbonImmutable
    {
        $expiresAt = $get('expires_at');
        Assert::stringNotEmpty($expiresAt);

        return CarbonImmutable::parse($expiresAt);
    }

    private static function getAttachProcessingRecordsSection(): Section
    {
        return Section::make(__('document.attach_processing_records'))
            ->collapsible()
            ->collapsed()
            ->schema([
                RelationTable::makeForRelationship(
                    'avg_responsible_processing_record_id',
                    'avgResponsibleProcessingRecords',
                    AvgResponsibleProcessingRecord::class,
                    'name',
                    RelationTableColumns::for(AvgResponsibleProcessingRecord::class),
                )
                    ->label(__('avg_responsible_processing_record.model_plural'))
                    ->visible(Authorization::hasPermission(Permission::CORE_ENTITY_VIEW))
                    ->columnSpanFull(),
                RelationTable::makeForRelationship(
                    'avg_processor_processing_record_id',
                    'avgProcessorProcessingRecords',
                    AvgProcessorProcessingRecord::class,
                    'name',
                    RelationTableColumns::for(AvgProcessorProcessingRecord::class),
                )
                    ->label(__('avg_processor_processing_record.model_plural'))
                    ->visible(Authorization::hasPermission(Permission::CORE_ENTITY_VIEW))
                    ->columnSpanFull(),
                RelationTable::makeForRelationship(
                    'wpg_processing_record_id',
                    'WpgProcessingRecords',
                    WpgProcessingRecord::class,
                    'name',
                    RelationTableColumns::for(WpgProcessingRecord::class),
                )
                    ->label(__('wpg_processing_record.model_plural'))
                    ->visible(Authorization::hasPermission(Permission::CORE_ENTITY_VIEW))
                    ->columnSpanFull(),
                RelationTable::makeForRelationship(
                    'algorithm_record_id',
                    'AlgorithmRecords',
                    AlgorithmRecord::class,
                    'name',
                    RelationTableColumns::for(AlgorithmRecord::class),
                )
                    ->label(__('algorithm_record.model_plural'))
                    ->visible(Authorization::hasPermission(Permission::CORE_ENTITY_VIEW))
                    ->columnSpanFull(),
                RelationTable::makeForRelationship(
                    'data_breach_record_id',
                    'DataBreachRecords',
                    DataBreachRecord::class,
                    'name',
                    RelationTableColumns::for(DataBreachRecord::class),
                )
                    ->label(__('data_breach_record.model_plural'))
                    ->visible(Authorization::hasPermission(Permission::DATA_BREACH_RECORD_VIEW))
                    ->columnSpanFull(),
            ]);
    }
}
