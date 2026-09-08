<?php

declare(strict_types=1);

namespace App\Enums\Import;

use App\Config\Feature;
use App\Import\Mapping\LookupTarget;
use App\Import\Mapping\RelationTarget;
use App\Import\Mapping\SubRecordTarget;
use App\Models\Address;
use App\Models\Algorithm\AlgorithmPublicationCategory;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Algorithm\AlgorithmStatus;
use App\Models\Algorithm\AlgorithmTheme;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgProcessorProcessingRecordService;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\ContactPerson;
use App\Models\DataBreachRecord;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Models\Dpia\DpiaRecord;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\Stakeholder;
use App\Models\System;
use App\Models\Tag;
use App\Models\Wpg\WpgGoal;
use App\Models\Wpg\WpgProcessingRecord;
use App\Models\Wpg\WpgProcessingRecordService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

use function __;
use function in_array;
use function method_exists;
use function sprintf;

/**
 * The registers a guided import may write to.
 *
 * The page exposes the *enum value* rather than a class name, so a class name
 * coming from the browser can never reach `new $class()`. Adding a register is
 * a deliberate change here, not a matter of passing a different string.
 *
 * What a register can link to follows from the relations its model has: every
 * register with processors offers them, every register that links to
 * processing records offers those. Lookup lists are the one thing declared per
 * register, because their foreign keys are.
 */
enum ImportTarget: string
{
    case DataBreachRecord = 'data_breach_record';
    case AvgResponsibleProcessingRecord = 'avg_responsible_processing_record';
    case AvgProcessorProcessingRecord = 'avg_processor_processing_record';
    case WpgProcessingRecord = 'wpg_processing_record';
    case AlgorithmRecord = 'algorithm_record';
    case DpiaRecord = 'dpia_record';
    case DpiaPrescanRecord = 'dpia_prescan_record';

    /**
     * Shared entities a source column can feed, created when the register does
     * not hold them yet. A column holding "Firma A" resolves to a Processor
     * record rather than becoming plain text.
     *
     * relation method => [model, name attribute, label key, extra attributes]
     */
    private const SHARED_ENTITIES = [
        'processors' => [Processor::class, 'name', 'processor.model_plural', ['email' => 'processor.email', 'phone' => 'processor.phone']],
        'systems' => [System::class, 'description', 'system.model_plural', []],
        'receivers' => [Receiver::class, 'description', 'receiver.model_plural', []],
        'responsibles' => [Responsible::class, 'name', 'responsible.model_plural', []],
        'contactPersons' => [ContactPerson::class, 'name', 'contact_person.model_plural', ['email' => 'contact_person.email', 'phone' => 'contact_person.phone', 'role' => 'contact_person.role']],
        'stakeholders' => [Stakeholder::class, 'description', 'stakeholder.model_plural', []],
        'avgGoals' => [AvgGoal::class, 'goal', 'avg_goal.model_plural', ['avg_goal_legal_base' => 'avg_goal.avg_goal_legal_base']],
        'wpgGoals' => [WpgGoal::class, 'description', 'wpg_goal.model_plural', []],
        'tags' => [Tag::class, 'name', 'tag.model_plural', []],
    ];

    /**
     * Other registers a row can point at. These are maintained deliberately,
     * so an unknown name is reported rather than turned into an empty record.
     *
     * relation method => [model, name attribute, label key]
     */
    private const LINKED_REGISTERS = [
        'avgResponsibleProcessingRecords' => [AvgResponsibleProcessingRecord::class, 'name', 'avg_responsible_processing_record.model_plural'],
        'avgProcessorProcessingRecords' => [AvgProcessorProcessingRecord::class, 'name', 'avg_processor_processing_record.model_plural'],
        'wpgProcessingRecords' => [WpgProcessingRecord::class, 'name', 'wpg_processing_record.model_plural'],
        'dataBreachRecords' => [DataBreachRecord::class, 'name', 'data_breach_record.model_plural'],
        'algorithmRecords' => [AlgorithmRecord::class, 'name', 'algorithm_record.model_plural'],
        'dpiaRecords' => [DpiaRecord::class, 'name', 'dpia_record.model_plural'],
    ];

    /**
     * @return class-string<Model>
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::DataBreachRecord => DataBreachRecord::class,
            self::AvgResponsibleProcessingRecord => AvgResponsibleProcessingRecord::class,
            self::AvgProcessorProcessingRecord => AvgProcessorProcessingRecord::class,
            self::WpgProcessingRecord => WpgProcessingRecord::class,
            self::AlgorithmRecord => AlgorithmRecord::class,
            self::DpiaRecord => DpiaRecord::class,
            self::DpiaPrescanRecord => DpiaPrescanRecord::class,
        };
    }

    /**
     * @param class-string<Model> $modelClass
     */
    public static function forModel(string $modelClass): self
    {
        foreach (self::cases() as $case) {
            if ($case->modelClass() === $modelClass) {
                return $case;
            }
        }

        throw new InvalidArgumentException(sprintf('%s is not an import target', $modelClass));
    }

    public function label(): string
    {
        return __(sprintf('%s.model_plural', $this->value));
    }

    /**
     * A register behind a feature flag is only a target while the flag is on.
     */
    public function enabled(): bool
    {
        return $this !== self::WpgProcessingRecord || Feature::wpgEnabled();
    }

    /**
     * @return array<int, RelationTarget>
     */
    public function relations(): array
    {
        $modelClass = $this->modelClass();
        $model = new $modelClass();
        $targets = [];

        foreach (self::SHARED_ENTITIES as $method => [$class, $nameAttribute, $labelKey, $extra]) {
            if (!method_exists($model, $method)) {
                continue;
            }

            $targets[] = new RelationTarget(
                $method,
                $class,
                $nameAttribute,
                $labelKey,
                static fn (Model $record): mixed => self::relation($record, $method),
                extraAttributes: $extra,
            );
        }

        foreach (self::LINKED_REGISTERS as $method => [$class, $nameAttribute, $labelKey]) {
            if (!method_exists($model, $method) || !self::registerEnabled($class)) {
                continue;
            }

            $targets[] = new RelationTarget(
                $method,
                $class,
                $nameAttribute,
                $labelKey,
                static fn (Model $record): mixed => self::relation($record, $method),
                missing: MissingEntityPolicy::Report,
            );
        }

        return $targets;
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
            self::AvgResponsibleProcessingRecord => [
                new LookupTarget(
                    'service',
                    AvgResponsibleProcessingRecordService::class,
                    'avg_responsible_processing_record_service_id',
                    'avg_responsible_processing_record_service.model_singular',
                ),
            ],
            self::AvgProcessorProcessingRecord => [
                new LookupTarget(
                    'service',
                    AvgProcessorProcessingRecordService::class,
                    'avg_processor_processing_record_service_id',
                    'avg_processor_processing_record_service.model_singular',
                ),
            ],
            self::WpgProcessingRecord => [
                new LookupTarget(
                    'service',
                    WpgProcessingRecordService::class,
                    'wpg_processing_record_service_id',
                    'wpg_processing_record_service.model_singular',
                ),
            ],
            self::AlgorithmRecord => [
                new LookupTarget('theme', AlgorithmTheme::class, 'algorithm_theme_id', 'algorithm_theme.model_singular'),
                new LookupTarget('status', AlgorithmStatus::class, 'algorithm_status_id', 'algorithm_status.model_singular'),
                new LookupTarget(
                    'publication_category',
                    AlgorithmPublicationCategory::class,
                    'algorithm_publication_category_id',
                    'algorithm_publication_category.model_singular',
                ),
            ],
            self::DataBreachRecord, self::DpiaRecord, self::DpiaPrescanRecord => [],
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
     * Fields the groups do not mention are still offered, under "Overig".
     *
     * Keys are translation keys for the group heading; values are attributes in
     * the order the form presents them.
     *
     * @return array<string, array<int, string>>
     */
    public function fieldGroups(): array
    {
        $groups = match ($this) {
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
            ],
            default => [],
        };

        // Only registers that keep a source reference can offer one.
        $modelClass = $this->modelClass();
        if (in_array('import_id', (new $modelClass())->getFillable(), true)) {
            $groups['import_mapping.group_source'] = ['import_id'];
        }

        return $groups;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            if ($case->enabled()) {
                $options[$case->value] = $case->label();
            }
        }

        return $options;
    }

    /**
     * Calls the relation method by name; RelationTarget checks what comes back.
     */
    private static function relation(Model $record, string $method): mixed
    {
        $callable = [$record, $method];
        Assert::isCallable($callable);

        return $callable();
    }

    /**
     * @param class-string<Model> $class
     */
    private static function registerEnabled(string $class): bool
    {
        return $class !== WpgProcessingRecord::class || Feature::wpgEnabled();
    }
}
