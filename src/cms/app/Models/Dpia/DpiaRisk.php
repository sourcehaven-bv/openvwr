<?php

declare(strict_types=1);

namespace App\Models\Dpia;

use App\Collections\Dpia\DpiaMeasureCollection;
use App\Collections\Dpia\DpiaRiskCollection;
use App\Components\Uuid\UuidInterface;
use App\Enums\Dpia\RiskLevel;
use App\Models\Casts\UuidCast;
use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Contracts\TenantAware;
use Database\Factories\Dpia\DpiaRiskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use function __;
use function mb_strlen;
use function mb_substr;
use function trim;

/**
 * One risk for the rights and freedoms of data subjects (paragraaf 16).
 *
 * Note that the scope is wider than privacy alone: the model explicitly names
 * the prohibition of discrimination as an example, so this is not a security
 * risk register.
 *
 * @property string|null $title
 * @property string|null $description
 * @property string|null $origin
 * @property RiskLevel|null $likelihood
 * @property string|null $likelihood_motivation
 * @property RiskLevel|null $impact
 * @property string|null $impact_motivation
 * @property RiskLevel|null $level
 * @property string|null $level_motivation
 * @property int $order_column
 * @property UuidInterface|null $dpia_record_id
 *
 * @property-read DpiaRecord $dpiaRecord
 * @property-read DpiaMeasureCollection $measures
 */
class DpiaRisk extends Model implements TenantAware
{
    /** @use HasFactory<DpiaRiskFactory> */
    use HasFactory;
    use HasOrganisation;
    use HasTimestamps;
    use HasUuidAsId;

    protected static string $collectionClass = DpiaRiskCollection::class;

    protected $fillable = [
        'dpia_record_id',
        'title',
        'description',
        'origin',
        'likelihood',
        'likelihood_motivation',
        'impact',
        'impact_motivation',
        'level',
        'level_motivation',
        'order_column',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'dpia_record_id' => UuidCast::class,
            'likelihood' => RiskLevel::class,
            'impact' => RiskLevel::class,
            'level' => RiskLevel::class,
            'order_column' => 'int',
        ];
    }

    /**
     * The short name used wherever this risk is referred to: the measure
     * checkboxes, the repeater header and the aandachtspunten.
     */
    public function label(): string
    {
        $title = $this->title;

        if ($title !== null && trim($title) !== '') {
            return trim($title);
        }

        $description = $this->description;

        if ($description === null || trim($description) === '') {
            return __('dpia_quality.unnamed');
        }

        $description = trim($description);

        return mb_strlen($description) > 60
            ? mb_substr($description, 0, 57) . '...'
            : $description;
    }

    /**
     * @return BelongsTo<DpiaRecord, $this>
     */
    public function dpiaRecord(): BelongsTo
    {
        return $this->belongsTo(DpiaRecord::class);
    }

    /**
     * @return BelongsToMany<DpiaMeasure, $this>
     */
    public function measures(): BelongsToMany
    {
        return $this->belongsToMany(DpiaMeasure::class, 'dpia_measure_risk');
    }

    /**
     * The level the kans x impact matrix suggests, used to flag a deviation
     * rather than to overwrite what the invuller decided.
     */
    public function suggestedLevel(): ?RiskLevel
    {
        return RiskLevel::suggest($this->likelihood, $this->impact);
    }

    public function deviatesFromMatrix(): bool
    {
        $suggested = $this->suggestedLevel();

        return $suggested instanceof RiskLevel
            && $this->level instanceof RiskLevel
            && $suggested !== $this->level;
    }
}
