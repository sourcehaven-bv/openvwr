<?php

declare(strict_types=1);

use App\Enums\Import\MappingTransform;
use App\Import\Mapping\TransformResolver;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * A model without any casts, so the column type has to decide.
 */
function uncastBreach(): Model
{
    return new class extends Model {
        protected $table = 'data_breach_records';
    };
}

it('reads the conversion off the column type when the model has no cast', function (string $attribute, MappingTransform $expected): void {
    expect(app(TransformResolver::class)->forAttribute(uncastBreach(), $attribute))->toBe($expected);
})->with([
    'boolean column' => ['ap_reported', MappingTransform::Boolean],
    'date column' => ['reported_at', MappingTransform::Date],
    'timestamp column' => ['created_at', MappingTransform::Date],
    'integer column' => ['affected_count', MappingTransform::Integer],
    'text column' => ['name', MappingTransform::Text],
    'no column at all' => ['not_a_column', MappingTransform::Text],
]);

it('treats the calendar date cast as a date', function (): void {
    expect(app(TransformResolver::class)->forAttribute(new AvgResponsibleProcessingRecord(), 'review_at'))->toBe(MappingTransform::Date);
});
