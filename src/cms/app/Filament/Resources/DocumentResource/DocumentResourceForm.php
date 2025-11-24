<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentResource;

use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
use App\Filament\Forms\Components\DatePicker\DatePicker;
use App\Filament\Forms\Components\Select\SelectSingleWithLookup;
use App\Filament\Forms\Components\SelectMultipleWithLookup;
use App\Filament\Forms\Components\Upload\AttachmentFileField;
use App\Models\Algorithm\AlgorithmRecord;
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
            ->schema([
                TextInput::make('name')
                    ->label(__('document.name'))
                    ->required()
                    ->maxLength(255),
                SelectSingleWithLookup::makeWithDisabledOptions('document_type_id', 'documentType', DocumentType::class, 'name')
                    ->label(__('document.type')),
                DatePicker::make('expires_at')
                    ->label(__('document.expires_at'))
                    ->live()
                    ->validationMessages(['required_unless' => __('document.expires_at_required_unless')]),
                DatePicker::make('notify_at')
                    ->label(__('document.notify_at'))
                    ->hintAction(
                        Action::make('notify_at_expires_at')
                            ->label(__('document.notification_options.expires_at'))
                            ->icon('heroicon-m-clock')
                            ->action(static function (Get $get, Set $set): void {
                                $expiresAt = $get('expires_at');
                                Assert::nullOrString($expiresAt);

                                if ($expiresAt === null) {
                                    return;
                                }

                                $set('notify_at', CarbonImmutable::createFromFormat('Y-m-d H:i:s', $expiresAt));
                            }),
                    )
                    ->hintAction(
                        Action::make('notify_at_1_month_before')
                            ->label(__('document.notification_options.1_month_before'))
                            ->icon('heroicon-m-clock')
                            ->action(static function (Get $get, Set $set): void {
                                $expiresAt = $get('expires_at');
                                Assert::nullOrString($expiresAt);

                                if ($expiresAt === null) {
                                    return;
                                }

                                $expiresAt = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $expiresAt);
                                Assert::isInstanceOf($expiresAt, CarbonImmutable::class);

                                $set('notify_at', $expiresAt->subMonth());
                            }),
                    )
                    ->hintAction(
                        Action::make('notify_at_3_months_before')
                            ->label(__('document.notification_options.3_months_before'))
                            ->icon('heroicon-m-clock')
                            ->action(static function (Get $get, Set $set): void {
                                $expiresAt = $get('expires_at');
                                Assert::nullOrString($expiresAt);

                                if ($expiresAt === null) {
                                    return;
                                }

                                $expiresAt = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $expiresAt);
                                Assert::isInstanceOf($expiresAt, CarbonImmutable::class);

                                $set('notify_at', $expiresAt->subMonths(3));
                            }),
                    ),
                Textarea::make('location')
                    ->label(__('document.location'))
                    ->columnSpan(2),
                AttachmentFileField::make('Attachments')
                    ->columnSpan(2),
            ]);
    }

    private static function getAttachProcessingRecordsSection(): Section
    {
        return Section::make(__('document.attach_processing_records'))
            ->label('foo')
            ->collapsible()
            ->collapsed()
            ->schema([
                SelectMultipleWithLookup::makeForRelationship(
                    'avg_responsible_processing_record_id',
                    'avgResponsibleProcessingRecords',
                    AvgResponsibleProcessingRecord::class,
                    'name',
                )
                    ->label(__('avg_responsible_processing_record.model_plural'))
                    ->visible(Authorization::hasPermission(Permission::CORE_ENTITY_VIEW))
                    ->columnSpan(2),
                SelectMultipleWithLookup::makeForRelationship(
                    'avg_processor_processing_record_id',
                    'avgProcessorProcessingRecords',
                    AvgResponsibleProcessingRecord::class,
                    'name',
                )
                    ->label(__('avg_processor_processing_record.model_plural'))
                    ->visible(Authorization::hasPermission(Permission::CORE_ENTITY_VIEW))
                    ->columnSpan(2),
                SelectMultipleWithLookup::makeForRelationship(
                    'wpg_processing_record_id',
                    'WpgProcessingRecords',
                    WpgProcessingRecord::class,
                    'name',
                )
                    ->label(__('wpg_processing_record.model_plural'))
                    ->visible(Authorization::hasPermission(Permission::CORE_ENTITY_VIEW))
                    ->columnSpan(2),
                SelectMultipleWithLookup::makeForRelationship(
                    'algorithm_record_id',
                    'AlgorithmRecords',
                    AlgorithmRecord::class,
                    'name',
                )
                    ->label(__('algorithm_record.model_plural'))
                    ->visible(Authorization::hasPermission(Permission::CORE_ENTITY_VIEW))
                    ->columnSpan(2),
                SelectMultipleWithLookup::makeForRelationship(
                    'data_breach_record_id',
                    'DataBreachRecords',
                    DataBreachRecord::class,
                    'name',
                )
                    ->label(__('data_breach_record.model_plural'))
                    ->visible(Authorization::hasPermission(Permission::DATA_BREACH_RECORD_VIEW))
                    ->columnSpan(2),
            ]);
    }
}
