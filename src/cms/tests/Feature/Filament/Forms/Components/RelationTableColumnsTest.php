<?php

declare(strict_types=1);

use App\Filament\Forms\Components\RelationTableColumns;
use App\Models\EntityNumber;
use App\Models\Wpg\WpgProcessingRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * @param array<int, array{label: string, get: Closure(Model): (string|null)}> $columns
 */
function relationTableColumnValue(array $columns, string $label, Model $record): ?string
{
    foreach ($columns as $column) {
        if ($column['label'] === $label) {
            return ($column['get'])($record);
        }
    }

    return null;
}

it('renders the number and name columns for a processing record', function (): void {
    $record = WpgProcessingRecord::factory()
        ->create([
            'entity_number_id' => EntityNumber::factory()->state([
                'number' => 'WPG00042',
            ]),
            'name' => 'Cameratoezicht',
        ]);

    $columns = RelationTableColumns::for(WpgProcessingRecord::class);

    expect(relationTableColumnValue($columns, __('processing_record.number'), $record))->toBe('WPG00042')
        ->and(relationTableColumnValue($columns, __('general.name'), $record))->toBe('Cameratoezicht');
});

it('renders an empty number when the record has no entity number', function (): void {
    $record = WpgProcessingRecord::factory()
        ->create(['name' => 'Cameratoezicht']);

    $record->forceFill(['entity_number_id' => null])->save();
    $record->unsetRelation('entityNumber');

    $columns = RelationTableColumns::for(WpgProcessingRecord::class);

    expect(relationTableColumnValue($columns, __('processing_record.number'), $record))->toBeNull();
});

it('has no number link for a model without a filament resource', function (): void {
    $columns = RelationTableColumns::for(WpgProcessingRecord::class);

    foreach ($columns as $column) {
        if ($column['label'] !== __('processing_record.number')) {
            continue;
        }

        expect(($column['href'] ?? null))->not->toBeNull()
            ->and(($column['href'])(new EntityNumber()))->toBeNull();
    }
});

it('throws when no columns are defined for a model', function (): void {
    RelationTableColumns::for(Model::class);
})->throws(InvalidArgumentException::class);
