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

/**
 * @property UuidInterface $data_breach_record_id
 * @property UuidInterface|null $created_by
 * @property DataBreachRecordState $state
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
}
