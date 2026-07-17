<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\PublicWebsiteTreeCollection;
use App\Components\Uuid\UuidInterface;
use App\Enums\Media\MediaGroup;
use App\Models\Casts\UuidCast;
use App\Models\Concerns\HasDefaultMediaCollections;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use Carbon\CarbonImmutable;
use Database\Factories\PublicWebsiteTreeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\FilamentTree\Concern\ModelTree;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property ?UuidInterface $organisation_id
 * @property int $order
 * @property ?UuidInterface $parent_id
 * @property string $title
 * @property string $slug
 * @property ?string $public_url
 * @property ?CarbonImmutable $public_from
 * @property string $public_website_content
 *
 * @property-read PublicWebsiteTreeCollection $children
 * @property-read ?Organisation $organisation
 * @property-read ?PublicWebsiteTree $parent
 */
class PublicWebsiteTree extends Model implements HasMedia
{
    use HasDefaultMediaCollections;
    /** @use HasFactory<PublicWebsiteTreeFactory> */
    use HasFactory;
    use HasTimestamps;
    use HasUuidAsId;
    use ModelTree;

    protected static string $collectionClass = PublicWebsiteTreeCollection::class;
    protected $fillable = [
        'organisation_id',
        'order',
        'parent_id',
        'title',
        'slug',
        'public_url',
        'public_from',
        'public_website_content',
    ];
    protected $table = 'public_website_tree';

    public function casts(): array
    {
        return [
            'organisation_id' => UuidCast::class,
            'order' => 'int',
            'parent_id' => UuidCast::class,
            'public_from' => 'datetime',
        ];
    }

    public function getFilamentPoster(): ?Media
    {
        return $this->getFirstMedia(MediaGroup::PUBLIC_WEBSITE_TREE->value);
    }

    /**
     * @return BelongsTo<Organisation, $this>
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
