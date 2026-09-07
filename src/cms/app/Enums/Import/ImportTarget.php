<?php

declare(strict_types=1);

namespace App\Enums\Import;

use App\Import\Mapping\LookupTarget;
use App\Import\Mapping\RelationTarget;
use App\Import\Mapping\SubRecordTarget;
use App\Models\Address;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\DataBreachRecord;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\System;
use App\Models\Wpg\WpgProcessingRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

use function __;

/**
 * The registers a guided import may write to.
 *
 * The page exposes the *enum value* rather than a class name, so a class name
 * coming from the browser can never reach `new $class()`. Adding a register is
 * a deliberate change here, not a matter of passing a different string.
 */
enum ImportTarget: string
{
    case DataBreachRecord = 'data_breach_record';
    case AvgResponsibleProcessingRecord = 'avg_responsible_processing_record';

    /**
     * @return class-string<Model>
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::DataBreachRecord => DataBreachRecord::class,
            self::AvgResponsibleProcessingRecord => AvgResponsibleProcessingRecord::class,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::DataBreachRecord => __('data_breach_record.model_plural'),
            self::AvgResponsibleProcessingRecord => __('avg_responsible_processing_record.model_plural'),
        };
    }

    /**
     * Shared entities a source column can feed. A column holding "Firma A"
     * resolves to a Processor record rather than becoming plain text.
     *
     * @return array<int, RelationTarget>
     */
    public function relations(): array
    {
        return match ($this) {
            self::DataBreachRecord => [
                new RelationTarget(
                    'avgResponsibleProcessingRecords',
                    AvgResponsibleProcessingRecord::class,
                    'name',
                    'avg_responsible_processing_record.model_plural',
                    static fn (DataBreachRecord $record): MorphToMany => $record->avgResponsibleProcessingRecords(),
                    missing: MissingEntityPolicy::Report,
                ),
                new RelationTarget(
                    'avgProcessorProcessingRecords',
                    AvgProcessorProcessingRecord::class,
                    'name',
                    'avg_processor_processing_record.model_plural',
                    static fn (DataBreachRecord $record): MorphToMany => $record->avgProcessorProcessingRecords(),
                    missing: MissingEntityPolicy::Report,
                ),
                new RelationTarget(
                    'wpgProcessingRecords',
                    WpgProcessingRecord::class,
                    'name',
                    'wpg_processing_record.model_plural',
                    static fn (DataBreachRecord $record): MorphToMany => $record->wpgProcessingRecords(),
                    missing: MissingEntityPolicy::Report,
                ),
            ],
            self::AvgResponsibleProcessingRecord => self::processingRecordRelations(),
        };
    }

    /**
     * @return array<int, RelationTarget>
     */
    private static function processingRecordRelations(): array
    {
        return [
            new RelationTarget(
                'processors',
                Processor::class,
                'name',
                'processor.model_plural',
                static fn (AvgResponsibleProcessingRecord $record): MorphToMany => $record->processors(),
                extraAttributes: [
                    'email' => 'processor.email',
                    'phone' => 'processor.phone',
                ],
            ),
            new RelationTarget(
                'systems',
                System::class,
                'description',
                'system.model_plural',
                static fn (AvgResponsibleProcessingRecord $record): MorphToMany => $record->systems(),
            ),
            new RelationTarget(
                'receivers',
                Receiver::class,
                'description',
                'receiver.model_plural',
                static fn (AvgResponsibleProcessingRecord $record): MorphToMany => $record->receivers(),
            ),
            new RelationTarget(
                'responsibles',
                Responsible::class,
                'name',
                'responsible.model_plural',
                static fn (AvgResponsibleProcessingRecord $record): MorphToMany => $record->responsibles(),
            ),
        ];
    }

    /**
     * Lookup lists a source column can fill. An unknown value is added to the
     * list, the way the existing json import does.
     *
     * @return array<int, LookupTarget>
     */
    public function lookups(): array
    {
        return match ($this) {
            self::DataBreachRecord => [],
            self::AvgResponsibleProcessingRecord => [
                new LookupTarget(
                    'service',
                    AvgResponsibleProcessingRecordService::class,
                    'avg_responsible_processing_record_service_id',
                    'avg_responsible_processing_record_service.model_singular',
                ),
            ],
        };
    }

    /**
     * Records that belong to a single related record and are spread over
     * several columns, such as an address.
     *
     * @return array<string, SubRecordTarget> keyed by the relation they belong to
     */
    public static function subRecords(): array
    {
        return [
            'processors' => new SubRecordTarget(
                'address',
                Address::class,
                [
                    'address' => 'address.address',
                    'postal_code' => 'address.postal_code',
                    'city' => 'address.city',
                    'country' => 'address.country',
                ],
                'address.model_singular',
                static fn (Processor $record): MorphOne => $record->address(),
            ),
        ];
    }

    /**
     * Groups the importable fields the way the register's own form groups them,
     * so the target dropdown reads like the screen the user already knows.
     *
     * Keys are translation keys for the group heading; values are attributes in
     * the order the form presents them.
     *
     * @return array<string, array<int, string>>
     */
    public function fieldGroups(): array
    {
        return match ($this) {
            self::DataBreachRecord => [
                'data_breach_record.step_name' => [
                    'name',
                    'type',
                    'reported_at',
                    'ap_reported',
                ],
                'data_breach_record.step_dates' => [
                    'discovered_at',
                    'started_at',
                    'ended_at',
                    'ap_reported_at',
                    'completed_at',
                ],
                'data_breach_record.step_incident' => [
                    'nature_of_incident',
                    'nature_of_incident_other',
                    'summary',
                    'involved_people',
                    'personal_data_categories',
                    'personal_data_categories_other',
                    'personal_data_special_categories',
                    'estimated_risk',
                    'measures',
                    'reported_to_involved',
                    'reported_to_involved_communication',
                    'reported_to_involved_communication_other',
                    'fg_reported',
                ],
                'import_mapping.group_source' => [
                    'import_id',
                ],
            ],
            self::AvgResponsibleProcessingRecord => [
                'import_mapping.group_source' => [
                    'import_id',
                ],
            ],
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
