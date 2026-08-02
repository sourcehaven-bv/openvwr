<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Filament\Forms\Components\RelationTableColumns;
use App\Filament\Resources\SystemResource;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\ContactPerson;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Models\Dpia\DpiaRecord;
use App\Models\EntityNumber;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\System;
use App\Models\User;
use App\Models\Wpg\WpgProcessingRecord;
use Illuminate\Database\Eloquent\Model;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

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

it('links the first column of every supported model to the record', function (string $model): void {
    $columns = RelationTableColumns::for($model);

    expect($columns[0]['href'] ?? null)->not->toBeNull();
})->with([
    AlgorithmRecord::class,
    AvgProcessorProcessingRecord::class,
    AvgResponsibleProcessingRecord::class,
    ContactPerson::class,
    DataBreachRecord::class,
    Document::class,
    DpiaPrescanRecord::class,
    DpiaRecord::class,
    Processor::class,
    Receiver::class,
    Responsible::class,
    System::class,
    User::class,
    WpgProcessingRecord::class,
]);

it('links to the view page when the user may not edit the record', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $system = System::factory()->recycle($organisation)->create();

    // Read-only rights: the edit page is off limits, so the link falls back to
    // the view page.
    $this->withFilamentSession($user, $organisation)
        ->withPermissions($user, [Permission::MANAGEMENT_VIEW]);

    $columns = RelationTableColumns::for(System::class);

    expect(($columns[0]['href'])($system))
        ->toBe(SystemResource::getUrl('view', ['record' => $system, 'tenant' => $organisation]));
});

it('has no link for a resource without an edit or view page the user may open', function (): void {
    // DataBreachRecordResource registers no view page, so a record the user
    // may not edit has nowhere to link to and must render as plain text.
    $columns = RelationTableColumns::for(DataBreachRecord::class);

    expect(($columns[0]['href'])(new DataBreachRecord()))->toBeNull();
});

it('throws when no columns are defined for a model', function (): void {
    RelationTableColumns::for(Model::class);
})->throws(InvalidArgumentException::class);
