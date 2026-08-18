<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\RetentionPeriodCollection;
use Database\Factories\RetentionPeriodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Suggested bewaartermijnen, maintained per organisation.
 *
 * Unlike the other lookup lists this one is not pointed at by a foreign key.
 * A chosen term is copied into the record's own retention_period text column,
 * because a bewaartermijn is recorded as it applied at that moment: editing
 * the list later must not silently rewrite registers that were already
 * established. The list only supplies the suggestions.
 */
class RetentionPeriod extends LookupListModel
{
    /** @use HasFactory<RetentionPeriodFactory> */
    use HasFactory;

    protected static string $collectionClass = RetentionPeriodCollection::class;
    protected $table = 'retention_periods';
}
