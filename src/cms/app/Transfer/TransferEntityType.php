<?php

declare(strict_types=1);

namespace App\Transfer;

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
use App\Models\ContactPersonPosition;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\FgRemark;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Remark;
use App\Models\Responsible;
use App\Models\Stakeholder;
use App\Models\StakeholderDataItem;
use App\Models\System;
use App\Models\Tag;
use App\Models\Wpg\WpgGoal;
use App\Models\Wpg\WpgProcessingRecord;
use App\Models\Wpg\WpgProcessingRecordService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use function __;
use function array_search;
use function class_basename;
use function in_array;
use function is_string;
use function sprintf;

enum TransferEntityType: string
{
    // main records
    case AVG_RESPONSIBLE_PROCESSING_RECORD = 'avg_responsible_processing_record';
    case AVG_PROCESSOR_PROCESSING_RECORD = 'avg_processor_processing_record';
    case WPG_PROCESSING_RECORD = 'wpg_processing_record';
    case ALGORITHM_RECORD = 'algorithm_record';
    case DATA_BREACH_RECORD = 'data_breach_record';

    // shared library entities
    case PROCESSOR = 'processor';
    case RECEIVER = 'receiver';
    case RESPONSIBLE = 'responsible';
    case SYSTEM = 'system';
    case CONTACT_PERSON = 'contact_person';
    case DOCUMENT = 'document';
    case STAKEHOLDER = 'stakeholder';
    case STAKEHOLDER_DATA_ITEM = 'stakeholder_data_item';
    case TAG = 'tag';
    case AVG_GOAL = 'avg_goal';
    case WPG_GOAL = 'wpg_goal';

    // owned entities, transferred with their parent
    case ADDRESS = 'address';
    case REMARK = 'remark';
    case FG_REMARK = 'fg_remark';

    // lookup lists, matched by name on import
    case DOCUMENT_TYPE = 'document_type';
    case CONTACT_PERSON_POSITION = 'contact_person_position';
    case AVG_RESPONSIBLE_PROCESSING_RECORD_SERVICE = 'avg_responsible_processing_record_service';
    case AVG_PROCESSOR_PROCESSING_RECORD_SERVICE = 'avg_processor_processing_record_service';
    case WPG_PROCESSING_RECORD_SERVICE = 'wpg_processing_record_service';
    case ALGORITHM_THEME = 'algorithm_theme';
    case ALGORITHM_STATUS = 'algorithm_status';
    case ALGORITHM_PUBLICATION_CATEGORY = 'algorithm_publication_category';

    private const array MODEL_CLASSES = [
        self::AVG_RESPONSIBLE_PROCESSING_RECORD->value => AvgResponsibleProcessingRecord::class,
        self::AVG_PROCESSOR_PROCESSING_RECORD->value => AvgProcessorProcessingRecord::class,
        self::WPG_PROCESSING_RECORD->value => WpgProcessingRecord::class,
        self::ALGORITHM_RECORD->value => AlgorithmRecord::class,
        self::DATA_BREACH_RECORD->value => DataBreachRecord::class,
        self::PROCESSOR->value => Processor::class,
        self::RECEIVER->value => Receiver::class,
        self::RESPONSIBLE->value => Responsible::class,
        self::SYSTEM->value => System::class,
        self::CONTACT_PERSON->value => ContactPerson::class,
        self::DOCUMENT->value => Document::class,
        self::STAKEHOLDER->value => Stakeholder::class,
        self::STAKEHOLDER_DATA_ITEM->value => StakeholderDataItem::class,
        self::TAG->value => Tag::class,
        self::AVG_GOAL->value => AvgGoal::class,
        self::WPG_GOAL->value => WpgGoal::class,
        self::ADDRESS->value => Address::class,
        self::REMARK->value => Remark::class,
        self::FG_REMARK->value => FgRemark::class,
        self::DOCUMENT_TYPE->value => DocumentType::class,
        self::CONTACT_PERSON_POSITION->value => ContactPersonPosition::class,
        self::AVG_RESPONSIBLE_PROCESSING_RECORD_SERVICE->value => AvgResponsibleProcessingRecordService::class,
        self::AVG_PROCESSOR_PROCESSING_RECORD_SERVICE->value => AvgProcessorProcessingRecordService::class,
        self::WPG_PROCESSING_RECORD_SERVICE->value => WpgProcessingRecordService::class,
        self::ALGORITHM_THEME->value => AlgorithmTheme::class,
        self::ALGORITHM_STATUS->value => AlgorithmStatus::class,
        self::ALGORITHM_PUBLICATION_CATEGORY->value => AlgorithmPublicationCategory::class,
    ];

    /**
     * Relations on main records whose targets a user can include/exclude in an export,
     * keyed by the Eloquent relation method name.
     */
    public const array SELECTABLE_RELATIONS = [
        'processors' => self::PROCESSOR,
        'receivers' => self::RECEIVER,
        'responsibles' => self::RESPONSIBLE,
        'systems' => self::SYSTEM,
        'contactPersons' => self::CONTACT_PERSON,
        'documents' => self::DOCUMENT,
        'stakeholders' => self::STAKEHOLDER,
        'tags' => self::TAG,
        'avgGoals' => self::AVG_GOAL,
        'wpgGoals' => self::WPG_GOAL,
        'dataBreachRecords' => self::DATA_BREACH_RECORD,
    ];

    /**
     * @return class-string<Model>
     */
    public function modelClass(): string
    {
        return self::MODEL_CLASSES[$this->value];
    }

    public static function fromModel(Model $model): self
    {
        $type = array_search($model::class, self::MODEL_CLASSES, true);

        if ($type === false) {
            throw new TransferException(sprintf('model %s is not transferable', $model::class));
        }

        return self::from($type);
    }

    public static function tryFromModelClass(string $modelClass): ?self
    {
        $type = array_search($modelClass, self::MODEL_CLASSES, true);

        return $type === false ? null : self::from($type);
    }

    public static function fromKey(string $key): self
    {
        return self::from(Str::snake(class_basename($key)));
    }

    public function isMainRecord(): bool
    {
        return in_array($this, [
            self::AVG_RESPONSIBLE_PROCESSING_RECORD,
            self::AVG_PROCESSOR_PROCESSING_RECORD,
            self::WPG_PROCESSING_RECORD,
            self::ALGORITHM_RECORD,
            self::DATA_BREACH_RECORD,
        ], true);
    }

    public function isLookup(): bool
    {
        return in_array($this, [
            self::DOCUMENT_TYPE,
            self::CONTACT_PERSON_POSITION,
            self::AVG_RESPONSIBLE_PROCESSING_RECORD_SERVICE,
            self::AVG_PROCESSOR_PROCESSING_RECORD_SERVICE,
            self::WPG_PROCESSING_RECORD_SERVICE,
            self::ALGORITHM_THEME,
            self::ALGORITHM_STATUS,
            self::ALGORITHM_PUBLICATION_CATEGORY,
        ], true);
    }

    public function isOwned(): bool
    {
        return in_array($this, [self::ADDRESS, self::REMARK, self::FG_REMARK], true);
    }

    /**
     * Column used to match "the same content" in the destination organisation (besides origin_id).
     */
    public function matchColumn(): ?string
    {
        return match ($this) {
            self::AVG_GOAL => 'goal',
            self::SYSTEM, self::RECEIVER, self::WPG_GOAL, self::STAKEHOLDER, self::STAKEHOLDER_DATA_ITEM => 'description',
            self::ADDRESS, self::REMARK, self::FG_REMARK => null,
            default => 'name',
        };
    }

    public function displayName(Model $model): string
    {
        $column = $this->matchColumn();
        $value = $column === null ? null : $model->getAttribute($column);

        if (!is_string($value) || $value === '') {
            return ModelGraph::id($model);
        }

        return Str::limit($value, 80);
    }

    public function label(): string
    {
        return __(sprintf('%s.model_singular', $this->value));
    }
}
