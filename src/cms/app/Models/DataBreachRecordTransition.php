<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\DataBreachRecordTransitionCollection;
use App\Components\Uuid\UuidInterface;
use App\Models\Casts\UuidCast;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use App\Models\States\DataBreachRecordState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property UuidInterface $data_breach_record_id
 * @property UuidInterface|null $created_by
 * @property DataBreachRecordState $state
 *
 * @property-read DataBreachRecord $dataBreachRecord
 * @property-read User|null $creator
 */
class DataBreachRecordTransition extends Model
{
    use HasTimestamps;
    use HasUuidAsId;

    protected static string $collectionClass = DataBreachRecordTransitionCollection::class;
    protected $fillable = [
        'data_breach_record_id',
        'created_by',

        'state',
    ];

    public function casts(): array
    {
        return [
            'created_by' => UuidCast::class,
            'data_breach_record_id' => UuidCast::class,
            'state' => DataBreachRecordState::class,
        ];
    }

    /**
     * @return BelongsTo<DataBreachRecord, $this>
     */
    public function dataBreachRecord(): BelongsTo
    {
        return $this->belongsTo(DataBreachRecord::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
