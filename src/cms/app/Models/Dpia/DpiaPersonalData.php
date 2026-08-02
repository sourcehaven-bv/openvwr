<?php

declare(strict_types=1);

namespace App\Models\Dpia;

use App\Collections\Dpia\DpiaPersonalDataCollection;
use App\Components\Uuid\UuidInterface;
use App\Enums\Dpia\PersonalDataType;
use App\Models\Casts\UuidCast;
use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Contracts\TenantAware;
use Database\Factories\Dpia\DpiaPersonalDataFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One category of personal data processed (paragraaf 2).
 *
 * @property string|null $description
 * @property PersonalDataType|null $type
 * @property string|null $data_subject_category
 * @property string|null $source
 * @property string|null $retention_period
 * @property string|null $exception_ground
 * @property int $order_column
 * @property UuidInterface|null $dpia_record_id
 *
 * @property-read DpiaRecord $dpiaRecord
 */
class DpiaPersonalData extends Model implements TenantAware
{
    /** @use HasFactory<DpiaPersonalDataFactory> */
    use HasFactory;
    use HasOrganisation;
    use HasTimestamps;
    use HasUuidAsId;

    protected $table = 'dpia_personal_data';

    protected static string $collectionClass = DpiaPersonalDataCollection::class;

    protected $fillable = [
        'dpia_record_id',
        'description',
        'type',
        'data_subject_category',
        'source',
        'retention_period',
        'exception_ground',
        'order_column',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'dpia_record_id' => UuidCast::class,
            'type' => PersonalDataType::class,
            'order_column' => 'int',
        ];
    }

    /**
     * @return BelongsTo<DpiaRecord, $this>
     */
    public function dpiaRecord(): BelongsTo
    {
        return $this->belongsTo(DpiaRecord::class);
    }

    /**
     * Whether paragraaf 12 still owes a ground for this gegeven.
     */
    public function missesExceptionGround(): bool
    {
        return $this->type?->requiresExceptionGround() === true
            && ($this->exception_ground === null || $this->exception_ground === '');
    }
}
