<?php

declare(strict_types=1);

namespace Tests\Fixtures\Documentation;

use App\Models\Avg\AvgResponsibleProcessingRecord;
use Filament\Resources\Resource;

/**
 * A resource whose schema class carries notes that cannot be resolved.
 *
 * Exists so SectionNotes can be pointed at AwkwardNotesResourceFormSchemas
 * through the usual naming convention.
 */
class AwkwardNotesResource extends Resource
{
    protected static ?string $model = AvgResponsibleProcessingRecord::class;
}
