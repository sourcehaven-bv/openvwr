<?php

declare(strict_types=1);

namespace App\Models;

use App\Components\Uuid\UuidInterface;
use App\Import\Mapping\MappingProfile;
use App\Models\Casts\UuidCast;
use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasSoftDeletes;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Contracts\TenantAware;
use Illuminate\Database\Eloquent\Model;

/**
 * A saved, reusable mapping. Editing creates a new version rather than
 * overwriting, so an import stays traceable to the mapping that produced it.
 *
 * @property string $name
 * @property string $target
 * @property string $source_fingerprint
 * @property array<string, mixed> $mapping
 * @property int $version
 * @property ?UuidInterface $created_by
 */
class ImportMappingProfile extends Model implements TenantAware
{
    use HasOrganisation;
    use HasSoftDeletes;
    use HasTimestamps;
    use HasUuidAsId;

    protected $fillable = [
        'name',
        'target',
        'source_fingerprint',
        'mapping',
        'version',
        'created_by',
    ];

    public function casts(): array
    {
        return [
            'mapping' => 'array',
            'version' => 'integer',
            'created_by' => UuidCast::class,
        ];
    }

    /**
     * @return MappingProfile<Model>
     */
    public function toMappingProfile(): MappingProfile
    {
        return MappingProfile::fromArray($this->mapping);
    }
}
