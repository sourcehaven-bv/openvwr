<?php

declare(strict_types=1);

namespace App\Models\Dpia;

use App\Collections\Dpia\DpiaMeasureCollection;
use App\Collections\Dpia\DpiaRiskCollection;
use App\Components\Uuid\UuidInterface;
use App\Enums\Dpia\MeasureType;
use App\Enums\Dpia\RiskLevel;
use App\Models\Casts\UuidCast;
use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Contracts\TenantAware;
use Database\Factories\Dpia\DpiaMeasureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * One measure that addresses one or more risks (paragraaf 17).
 *
 * The link to the risks is explicit because paragraaf 17 requires it: the DPIA
 * has to describe which measure addresses which risk.
 *
 * @property string|null $description
 * @property MeasureType|null $type
 * @property string|null $origin
 * @property RiskLevel|null $residual_level
 * @property string|null $ap_advice
 * @property string|null $monitoring_country
 * @property string|null $owner
 * @property int $order_column
 * @property UuidInterface|null $dpia_record_id
 *
 * @property-read DpiaRecord $dpiaRecord
 * @property-read DpiaRiskCollection $risks
 */
class DpiaMeasure extends Model implements TenantAware
{
    /** @use HasFactory<DpiaMeasureFactory> */
    use HasFactory;
    use HasOrganisation;
    use HasTimestamps;
    use HasUuidAsId;

    protected static string $collectionClass = DpiaMeasureCollection::class;

    protected $fillable = [
        'dpia_record_id',
        'description',
        'type',
        'origin',
        'residual_level',
        'ap_advice',
        'monitoring_country',
        'owner',
        'order_column',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'dpia_record_id' => UuidCast::class,
            'type' => MeasureType::class,
            'residual_level' => RiskLevel::class,
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
     * @return BelongsToMany<DpiaRisk, $this>
     */
    public function risks(): BelongsToMany
    {
        return $this->belongsToMany(DpiaRisk::class, 'dpia_measure_risk');
    }
}
