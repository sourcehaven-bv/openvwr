<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\ListAvgResponsibleProcessingRecords;
use App\Jobs\TransferExportJob;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Processor;
use Illuminate\Support\Facades\Bus;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('dispatches the export job with the selected related items', function (): void {
    Bus::fake();

    $organisation = OrganisationTestHelper::create();

    $record = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'name' => 'Verwerking',
            'has_processors' => true,
            'has_systems' => true,
        ]);

    $processor = Processor::factory()->for($organisation)->create(['name' => 'Verwerker 1']);
    $record->processors()->attach($processor);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAvgResponsibleProcessingRecords::class)
        ->callTableBulkAction('transfer_export', [$record], [
            'related' => [
                'processors' => [$processor->id->toString()],
            ],
        ])
        ->assertHasNoTableBulkActionErrors()
        ->assertNotified();

    Bus::assertDispatched(
        TransferExportJob::class,
        static function (TransferExportJob $job) use ($record, $processor): bool {
            return $job->recordIds === [$record->id->toString()]
                && ($job->selectedRelated['processors'] ?? []) === [$processor->id->toString()];
        },
    );
});

it('dispatches the export job when no related items are selected', function (): void {
    Bus::fake();

    $organisation = OrganisationTestHelper::create();
    $record = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create(['name' => 'Verwerking']);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAvgResponsibleProcessingRecords::class)
        ->callTableBulkAction('transfer_export', [$record])
        ->assertHasNoTableBulkActionErrors();

    Bus::assertDispatched(TransferExportJob::class);
});

it('filters out empty and non-string related ids', function (): void {
    Bus::fake();

    $organisation = OrganisationTestHelper::create();
    $record = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create(['name' => 'Verwerking']);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAvgResponsibleProcessingRecords::class)
        ->callTableBulkAction('transfer_export', [$record], [
            'related' => [
                // empty and non-string ids are filtered out
                'tags' => ['', 123],
            ],
        ])
        ->assertHasNoTableBulkActionErrors();

    Bus::assertDispatched(
        TransferExportJob::class,
        static function (TransferExportJob $job): bool {
            return ($job->selectedRelated['tags'] ?? []) === [];
        },
    );
});

it('is hidden without the transfer export permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $record = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();

    $this->withFilamentSession($user, $organisation)
        ->withPermissions($user, [Permission::CORE_ENTITY_VIEW])
        ->createLivewireTestable(ListAvgResponsibleProcessingRecords::class)
        ->assertTableBulkActionHidden('transfer_export', [$record]);
});
