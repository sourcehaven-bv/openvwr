<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Filament\Pages\TransferCopy;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\User;
use App\Transfer\TransferEntityType;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Helpers\Model\OrganisationTestHelper;

/**
 * A user who belongs to both organisations with an import-capable role in each.
 */
function copyableFilamentUser(Organisation $source, Organisation $destination): User
{
    $user = User::factory()->hasAttached(collect([$source, $destination]))->create();
    $user->organisationRoles()->create(['organisation_id' => $source->id, 'role' => Role::CHIEF_PRIVACY_OFFICER]);
    $user->organisationRoles()->create(['organisation_id' => $destination->id, 'role' => Role::CHIEF_PRIVACY_OFFICER]);

    return $user;
}

function seedCopyRecordWithProcessor(Organisation $organisation): AvgResponsibleProcessingRecord
{
    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create([
        'name' => 'Verwerking',
        'has_processors' => true,
        'has_systems' => true,
    ]);
    $record->processors()->attach(Processor::factory()->for($organisation)->create(['name' => 'Verwerker']));

    return $record;
}

/**
 * Assert that mounting the copy page aborts with the given status. The exception handler is
 * disabled by the caller so mount() aborts surface as HttpExceptions.
 */
function assertCopyPageAborts(int $status, string $type, string $records): void
{
    try {
        Livewire::test(TransferCopy::class, ['type' => $type, 'records' => $records]);
        expect(false)->toBeTrue('expected the copy page to abort but it mounted');
    } catch (HttpException $exception) {
        expect($exception->getStatusCode())->toBe($status);
    }
}

it('cannot access the copy page without the transfer export permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = User::factory()->hasAttached(collect([$organisation]))->create();
    $record = seedCopyRecordWithProcessor($organisation);

    $this->withFilamentSession($user, $organisation)->withoutExceptionHandling();

    assertCopyPageAborts(403, TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->value, $record->id->toString());
});

it('aborts when the record type is not a main record', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableFilamentUser($source, $destination);

    $this->withFilamentSession($user, $source)->withoutExceptionHandling();

    assertCopyPageAborts(404, 'processor', fake()->uuid());
});

it('aborts when no selected record belongs to the current organisation', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableFilamentUser($source, $destination);
    // record belongs to the destination, not the active (source) org
    $foreign = seedCopyRecordWithProcessor($destination);

    $this->withFilamentSession($user, $source)->withoutExceptionHandling();

    assertCopyPageAborts(404, TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->value, $foreign->id->toString());
});

it('aborts when the records parameter is empty', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableFilamentUser($source, $destination);

    $this->withFilamentSession($user, $source)->withoutExceptionHandling();

    assertCopyPageAborts(404, TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->value, '');
});

it('ignores malformed related selections when copying', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableFilamentUser($source, $destination);
    $record = seedCopyRecordWithProcessor($source);

    $this->withFilamentSession($user, $source)
        ->createLivewireTestable(TransferCopy::class, [
            'type' => TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->value,
            'records' => $record->id->toString(),
        ])
        ->set('targetOrganisationId', $destination->id->toString())
        // a non-array value under a relation key must be skipped, not fatal
        ->set('related', ['processors' => 'not-an-array'])
        ->call('analyse')
        ->call('copy');

    expect(AvgResponsibleProcessingRecord::query()->whereBelongsTo($destination)->exists())->toBeTrue();
});

it('protects the server-set record ids from client tampering', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableFilamentUser($source, $destination);
    $record = seedCopyRecordWithProcessor($source);

    $this->withFilamentSession($user, $source);

    expect(fn () => $this->createLivewireTestable(TransferCopy::class, [
        'type' => TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->value,
        'records' => $record->id->toString(),
    ])->set('recordIds', ['tampered']))->toThrow(CannotUpdateLockedPropertyException::class);
});

it('lists the target organisations and pre-selects related items', function (): void {
    $source = Organisation::factory()->create(['name' => 'Bron']);
    $destination = Organisation::factory()->create(['name' => 'Doel']);
    $user = copyableFilamentUser($source, $destination);
    $record = seedCopyRecordWithProcessor($source);

    $component = $this->withFilamentSession($user, $source)
        ->createLivewireTestable(TransferCopy::class, [
            'type' => TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->value,
            'records' => $record->id->toString(),
        ]);

    expect($component->instance()->targetOptions())->toHaveKey($destination->id->toString())
        ->and($component->get('related'))->toHaveKey('processors');
});

it('warns when analyse is called without a target organisation', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableFilamentUser($source, $destination);
    $record = seedCopyRecordWithProcessor($source);

    $this->withFilamentSession($user, $source)
        ->createLivewireTestable(TransferCopy::class, [
            'type' => TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->value,
            'records' => $record->id->toString(),
        ])
        ->call('analyse')
        ->assertSet('analysed', false)
        ->assertNotified();
});

it('analyses, resets and copies into the target organisation', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableFilamentUser($source, $destination);
    $record = seedCopyRecordWithProcessor($source);

    $component = $this->withFilamentSession($user, $source)
        ->createLivewireTestable(TransferCopy::class, [
            'type' => TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->value,
            'records' => $record->id->toString(),
        ])
        ->set('targetOrganisationId', $destination->id->toString())
        ->call('analyse')
        ->assertSet('analysed', true);

    expect($component->get('items'))->not->toBeEmpty();

    // resetAnalysis clears the preview and returns to selection
    $component->call('resetAnalysis')->assertSet('analysed', false);

    $component->call('analyse')->call('copy');

    expect(AvgResponsibleProcessingRecord::query()->whereBelongsTo($destination)->where('name', 'Verwerking')->exists())
        ->toBeTrue();
});

it('exposes the page title and navigation group', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableFilamentUser($source, $destination);
    $record = seedCopyRecordWithProcessor($source);

    $component = $this->withFilamentSession($user, $source)
        ->createLivewireTestable(TransferCopy::class, [
            'type' => TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->value,
            'records' => $record->id->toString(),
        ]);

    expect($component->instance()->getTitle())->toBe(__('transfer.copy_page_title'))
        ->and(TransferCopy::getNavigationGroup())->not->toBeNull();
});

it('does not analyse when the selected target no longer exists', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableFilamentUser($source, $destination);
    $record = seedCopyRecordWithProcessor($source);

    $this->withFilamentSession($user, $source)
        ->createLivewireTestable(TransferCopy::class, [
            'type' => TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->value,
            'records' => $record->id->toString(),
        ])
        ->set('targetOrganisationId', fake()->uuid())
        ->call('analyse')
        ->assertSet('analysed', false)
        ->assertNotified();
});

it('notifies and does not copy when the copier rejects the transfer', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableFilamentUser($source, $destination);
    $record = seedCopyRecordWithProcessor($source);

    $component = $this->withFilamentSession($user, $source)
        ->createLivewireTestable(TransferCopy::class, [
            'type' => TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->value,
            'records' => $record->id->toString(),
        ])
        ->set('targetOrganisationId', $destination->id->toString())
        ->call('analyse')
        ->assertSet('analysed', true);

    // Revoke export in the source after analysis: resolveTarget still passes (it only checks
    // import in the target), but CrossOrgCopier rejects on the missing source export.
    $user->organisationRoles()->where('organisation_id', $source->id)->delete();
    $user->organisationRoles()->create(['organisation_id' => $source->id, 'role' => Role::MANDATE_HOLDER]);

    $component->call('copy')->assertNotified();

    expect(AvgResponsibleProcessingRecord::query()->whereBelongsTo($destination)->exists())->toBeFalse();
});

it('does not copy into an organisation the user may not import into', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    // import rights only in source; destination role cannot import
    $user = User::factory()->hasAttached(collect([$source, $destination]))->create();
    $user->organisationRoles()->create(['organisation_id' => $source->id, 'role' => Role::CHIEF_PRIVACY_OFFICER]);
    $user->organisationRoles()->create(['organisation_id' => $destination->id, 'role' => Role::MANDATE_HOLDER]);
    $record = seedCopyRecordWithProcessor($source);

    $component = $this->withFilamentSession($user, $source)
        ->withoutExceptionHandling()
        ->createLivewireTestable(TransferCopy::class, [
            'type' => TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->value,
            'records' => $record->id->toString(),
        ]);

    // forcing a forbidden target id must not analyse (resolveTarget returns null)
    $component->set('targetOrganisationId', $destination->id->toString())
        ->call('analyse')
        ->assertSet('analysed', false);

    // and attempting to copy is forbidden
    expect(fn () => $component->call('copy'))
        ->toThrow(Symfony\Component\HttpKernel\Exception\HttpException::class);

    expect(AvgResponsibleProcessingRecord::query()->whereBelongsTo($destination)->exists())->toBeFalse();
});
