<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\Algorithm\AlgorithmPublicationCategoryCollection;
use App\Collections\Algorithm\AlgorithmRecordCollection;
use App\Collections\Algorithm\AlgorithmStatusCollection;
use App\Collections\Algorithm\AlgorithmThemeCollection;
use App\Collections\Avg\AvgGoalCollection;
use App\Collections\Avg\AvgProcessorProcessingRecordCollection;
use App\Collections\Avg\AvgProcessorProcessingRecordServiceCollection;
use App\Collections\Avg\AvgResponsibleProcessingRecordCollection;
use App\Collections\Avg\AvgResponsibleProcessingRecordServiceCollection;
use App\Collections\ContactPersonCollection;
use App\Collections\ContactPersonPositionCollection;
use App\Collections\DataBreachRecordCollection;
use App\Collections\DocumentCollection;
use App\Collections\MediaCollection;
use App\Collections\OrganisationCollection;
use App\Collections\ProcessorCollection;
use App\Collections\ReceiverCollection;
use App\Collections\ResponsibleCollection;
use App\Collections\SnapshotCollection;
use App\Collections\StakeholderCollection;
use App\Collections\StakeholderDataItemCollection;
use App\Collections\SystemCollection;
use App\Collections\TagCollection;
use App\Collections\UserCollection;
use App\Collections\Wpg\WpgGoalCollection;
use App\Collections\Wpg\WpgProcessingRecordCollection;
use App\Collections\Wpg\WpgProcessingRecordServiceCollection;
use App\Components\Uuid\UuidInterface;
use App\Enums\Media\MediaGroup;
use App\Models\Algorithm\AlgorithmPublicationCategory;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Algorithm\AlgorithmStatus;
use App\Models\Algorithm\AlgorithmTheme;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgProcessorProcessingRecordService;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\Casts\UuidCast;
use App\Models\Concerns\HasDefaultMediaCollections;
use App\Models\Concerns\HasSoftDeletes;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Wpg\WpgGoal;
use App\Models\Wpg\WpgProcessingRecord;
use App\Models\Wpg\WpgProcessingRecordService;
use Carbon\CarbonImmutable;
use Database\Factories\OrganisationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property array<array-key, string> $allowed_email_domains
 * @property string $allowed_ips
 * @property ?UuidInterface $databreach_entity_number_counter_id
 * @property string $name
 * @property ?string $public_website_content
 * @property ?UuidInterface $register_entity_number_counter_id
 * @property UuidInterface $responsible_legal_entity_id
 * @property CarbonImmutable|null $public_from
 * @property CarbonImmutable|null $published_at
 * @property int $review_at_default_in_months
 * @property string $slug
 *
 * @property-read AlgorithmPublicationCategoryCollection $algorithmPublicationCategories
 * @property-read AlgorithmRecordCollection $algorithmRecords
 * @property-read AlgorithmStatusCollection $algorithmStatuses
 * @property-read AlgorithmThemeCollection $algorithmThemes
 * @property-read AvgGoalCollection $avgGoals
 * @property-read AvgProcessorProcessingRecordServiceCollection $avgProcessorProcessingRecordServices
 * @property-read AvgProcessorProcessingRecordCollection $avgProcessorProcessingRecords
 * @property-read AvgResponsibleProcessingRecordServiceCollection $avgResponsibleProcessingRecordServices
 * @property-read AvgResponsibleProcessingRecordCollection $avgResponsibleProcessingRecords
 * @property-read ContactPersonPositionCollection $contactPersonPositions
 * @property-read ContactPersonCollection $contactPersons
 * @property-read EntityNumberCounter $databreachEntityNumberCounter
 * @property-read DataBreachRecordCollection $dataBreachRecords
 * @property-read DocumentCollection $documents
 * @property-read MediaCollection $media
 * @property-read ProcessorCollection $processors
 * @property-read ?PublicWebsiteTree $publicWebsiteTree
 * @property-read ReceiverCollection $receivers
 * @property-read EntityNumberCounter $registerEntityNumberCounter
 * @property-read ResponsibleLegalEntity $responsibleLegalEntity
 * @property-read ResponsibleCollection $responsibles
 * @property-read StakeholderDataItemCollection $stakeholderDataItems
 * @property-read SnapshotCollection $snapshots
 * @property-read StakeholderCollection $stakeholders
 * @property-read SystemCollection $systems
 * @property-read TagCollection $tags
 * @property-read UserCollection $users
 * @property-read WpgProcessingRecordServiceCollection $wpgProcessingRecordServices
 * @property-read WpgProcessingRecordCollection $wpgProcessingRecords
 * @property-read WpgGoalCollection $wpgGoals
 */
class Organisation extends Model implements HasMedia
{
    use HasDefaultMediaCollections;
    /** @use HasFactory<OrganisationFactory> */
    use HasFactory;
    use HasTimestamps;
    use HasUuidAsId;
    use HasSoftDeletes;

    protected static string $collectionClass = OrganisationCollection::class;
    protected $fillable = [
        'name',
        'slug',
        'allowed_email_domains',
        'allowed_ips',
        'responsible_legal_entity_id',
        'review_at_default_in_months',
        'public_website_content',
        'register_entity_number_counter_id',
        'databreach_entity_number_counter_id',
        'public_from',
    ];

    public function casts(): array
    {
        return [
            'allowed_email_domains' => 'array',
            'databreach_entity_number_counter_id' => UuidCast::class,
            'public_from' => 'datetime',
            'published_at' => 'datetime',
            'responsible_legal_entity_id' => UuidCast::class,
            'register_entity_number_counter_id' => UuidCast::class,
        ];
    }

    /**
     * @return HasMany<AvgGoal, $this>
     */
    public function avgGoals(): HasMany
    {
        return $this->hasMany(AvgGoal::class);
    }

    /**
     * @return HasMany<AlgorithmPublicationCategory, $this>
     */
    public function algorithmPublicationCategories(): HasMany
    {
        return $this->hasMany(AlgorithmPublicationCategory::class);
    }

    /**
     * @return HasMany<AlgorithmRecord, $this>
     */
    public function algorithmRecords(): HasMany
    {
        return $this->hasMany(AlgorithmRecord::class);
    }

    /**
     * @return HasMany<AlgorithmStatus, $this>
     */
    public function algorithmStatuses(): HasMany
    {
        return $this->hasMany(AlgorithmStatus::class);
    }

    /**
     * @return HasMany<AlgorithmTheme, $this>
     */
    public function algorithmThemes(): HasMany
    {
        return $this->hasMany(AlgorithmTheme::class);
    }

    /**
     * @return HasMany<AvgProcessorProcessingRecordService, $this>
     */
    public function avgProcessorProcessingRecordServices(): HasMany
    {
        return $this->hasMany(AvgProcessorProcessingRecordService::class);
    }

    /**
     * @return HasMany<AvgProcessorProcessingRecord, $this>
     */
    public function avgProcessorProcessingRecords(): HasMany
    {
        return $this->hasMany(AvgProcessorProcessingRecord::class);
    }

    /**
     * @return HasMany<AvgResponsibleProcessingRecordService, $this>
     */
    public function avgResponsibleProcessingRecordServices(): HasMany
    {
        return $this->hasMany(AvgResponsibleProcessingRecordService::class);
    }

    /**
     * @return HasMany<AvgResponsibleProcessingRecord, $this>
     */
    public function avgResponsibleProcessingRecords(): HasMany
    {
        return $this->hasMany(AvgResponsibleProcessingRecord::class);
    }

    /**
     * @return HasMany<ContactPerson, $this>
     */
    public function contactPersons(): HasMany
    {
        return $this->hasMany(ContactPerson::class);
    }

    /**
     * @return HasMany<ContactPersonPosition, $this>
     */
    public function contactPersonPositions(): HasMany
    {
        return $this->hasMany(ContactPersonPosition::class);
    }

    /**
     * @return BelongsTo<EntityNumberCounter, $this>
     */
    public function databreachEntityNumberCounter(): BelongsTo
    {
        return $this->belongsTo(EntityNumberCounter::class);
    }

    /**
     * @return HasMany<DataBreachRecord, $this>
     */
    public function dataBreachRecords(): HasMany
    {
        return $this->hasMany(DataBreachRecord::class);
    }

    /**
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * @return HasMany<DocumentType, $this>
     */
    public function documentTypes(): HasMany
    {
        return $this->hasMany(DocumentType::class);
    }

    /**
     * @return HasMany<Processor, $this>
     */
    public function processors(): HasMany
    {
        return $this->hasMany(Processor::class);
    }

    /**
     * @return HasOne<PublicWebsiteTree, $this>
     */
    public function publicWebsiteTree(): HasOne
    {
        return $this->hasOne(PublicWebsiteTree::class);
    }

    /**
     * @return HasMany<Receiver, $this>
     */
    public function receivers(): HasMany
    {
        return $this->hasMany(Receiver::class);
    }

    /**
     * @return BelongsTo<EntityNumberCounter, $this>
     */
    public function registerEntityNumberCounter(): BelongsTo
    {
        return $this->belongsTo(EntityNumberCounter::class);
    }

    /**
     * @return HasMany<Responsible, $this>
     */
    public function responsibles(): HasMany
    {
        return $this->hasMany(Responsible::class);
    }

    /**
     * @return BelongsTo<ResponsibleLegalEntity, $this>
     */
    public function responsibleLegalEntity(): BelongsTo
    {
        return $this->belongsTo(ResponsibleLegalEntity::class);
    }

    /**
     * @return HasMany<Snapshot, $this>
     */
    public function snapshots(): HasMany
    {
        return $this->hasMany(Snapshot::class);
    }

    /**
     * @return HasMany<StakeholderDataItem, $this>
     */
    public function stakeholderDataItems(): HasMany
    {
        return $this->hasMany(StakeholderDataItem::class);
    }

    /**
     * @return HasMany<Stakeholder, $this>
     */
    public function stakeholders(): HasMany
    {
        return $this->hasMany(Stakeholder::class);
    }

    /**
     * @return HasMany<System, $this>
     */
    public function systems(): HasMany
    {
        return $this->hasMany(System::class);
    }

    /**
     * @return HasMany<Tag, $this>
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * @return HasMany<WpgGoal, $this>
     */
    public function wpgGoals(): HasMany
    {
        return $this->hasMany(WpgGoal::class);
    }

    /**
     * @return HasMany<WpgProcessingRecordService, $this>
     */
    public function wpgProcessingRecordServices(): HasMany
    {
        return $this->hasMany(WpgProcessingRecordService::class);
    }

    /**
     * @return HasMany<WpgProcessingRecord, $this>
     */
    public function wpgProcessingRecords(): HasMany
    {
        return $this->hasMany(WpgProcessingRecord::class);
    }

    public function getFilamentPoster(): ?Media
    {
        return $this->getFirstMedia(MediaGroup::ORGANISATION_POSTERS->value);
    }
}
