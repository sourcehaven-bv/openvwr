<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Filament\Pages\TransferCopy;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\ListAvgResponsibleProcessingRecords;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\User;

function userWithCopyTarget(Organisation $source, Organisation $destination): User
{
    $user = User::factory()->hasAttached(collect([$source, $destination]))->create();
    $user->organisationRoles()->create(['organisation_id' => $source->id, 'role' => Role::CHIEF_PRIVACY_OFFICER]);
    $user->organisationRoles()->create(['organisation_id' => $destination->id, 'role' => Role::CHIEF_PRIVACY_OFFICER]);

    return $user;
}

it('is hidden without the transfer export permission', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    // A role that grants neither export nor import.
    $user = User::factory()->hasAttached(collect([$source, $destination]))->create();
    $user->organisationRoles()->create(['organisation_id' => $source->id, 'role' => Role::MANDATE_HOLDER]);
    $record = AvgResponsibleProcessingRecord::factory()->for($source)->create();

    $this->withFilamentSession($user, $source)
        ->createLivewireTestable(ListAvgResponsibleProcessingRecords::class)
        ->assertTableBulkActionHidden('transfer_copy', [$record]);
});

it('is hidden when the user has no other organisation to copy into', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->hasAttached(collect([$organisation]))->create();
    $user->organisationRoles()->create(['organisation_id' => $organisation->id, 'role' => Role::CHIEF_PRIVACY_OFFICER]);
    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create();

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(ListAvgResponsibleProcessingRecords::class)
        ->assertTableBulkActionHidden('transfer_copy', [$record]);
});

it('is visible and redirects to the copy page when a copy target exists', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = userWithCopyTarget($source, $destination);
    $record = AvgResponsibleProcessingRecord::factory()->for($source)->create([
        'has_processors' => true,
        'has_systems' => true,
    ]);

    $this->withFilamentSession($user, $source)
        ->createLivewireTestable(ListAvgResponsibleProcessingRecords::class)
        ->assertTableBulkActionVisible('transfer_copy', [$record])
        ->callTableBulkAction('transfer_copy', [$record])
        ->assertRedirect(
            TransferCopy::getUrl(tenant: $source) . '?' . http_build_query([
                'type' => 'avg_responsible_processing_record',
                'records' => $record->id->toString(),
            ]),
        );
});
