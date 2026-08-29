<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Enums\Authorization\Role;
use App\Models\Organisation;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

use function expect;
use function it;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * @param array<string, bool> $options
 *
 * @return array<string, mixed>
 */
function runOrganisationInfoJson(string $identifier, array $options = []): array
{
    $bufferedOutput = new BufferedOutput();
    Artisan::call('org:info', ['organisation' => $identifier, '--json' => true] + $options, $bufferedOutput);

    return json_decode($bufferedOutput->fetch(), true, 512, JSON_THROW_ON_ERROR);
}

it('can show organisation info as a table', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $organisation);

    $this->artisan('org:info', ['organisation' => $organisation->slug])
        ->assertOk()
        ->expectsTable(['Key', 'Value'], [
            ['id', $organisation->id->toString()],
            ['name', $organisation->name],
            ['slug', $organisation->slug],
            ['responsible legal entity', $organisation->responsibleLegalEntity->name],
            ['coc number', $organisation->coc_number ?? 'null'],
            ['sector', $organisation->sector ?? 'null'],
            ['created at', $organisation->created_at->toDateTimeString()],
            ['updated at', $organisation->updated_at->toDateTimeString()],
            ['deleted at', 'null'],
            ['users', '1'],
            ['avg_responsible_processing_records', '0'],
            ['avg_processor_processing_records', '0'],
            ['wpg_processing_records', '0'],
            ['data_breach_records', '0'],
            ['algorithm_records', '0'],
            ['documents', '0'],
        ])
        ->expectsTable(['User', 'Email', 'Organisation roles'], [
            [$user->name, $user->email, Role::PRIVACY_OFFICER->value],
        ]);
});

it('can show organisation info as json looked up by slug', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $organisation);
    $user->assignOrganisationRole(Role::COUNSELOR, $organisation);

    expect(runOrganisationInfoJson($organisation->slug))->toBe([
        'id' => $organisation->id->toString(),
        'name' => $organisation->name,
        'slug' => $organisation->slug,
        'responsible_legal_entity' => $organisation->responsibleLegalEntity->name,
        'coc_number' => $organisation->coc_number,
        'sector' => $organisation->sector,
        'created_at' => $organisation->created_at->toISOString(),
        'updated_at' => $organisation->updated_at->toISOString(),
        'deleted_at' => null,
        'counts' => [
            'users' => 1,
            'avg_responsible_processing_records' => 0,
            'avg_processor_processing_records' => 0,
            'wpg_processing_records' => 0,
            'data_breach_records' => 0,
            'algorithm_records' => 0,
            'documents' => 0,
        ],
        'users' => [
            [
                'id' => $user->id->toString(),
                'name' => $user->name,
                'email' => $user->email,
                'roles' => [Role::COUNSELOR->value, Role::PRIVACY_OFFICER->value],
            ],
        ],
    ]);
});

it('can show organisation info looked up by uuid', function (): void {
    $organisation = OrganisationTestHelper::create();

    $organisationData = runOrganisationInfoJson($organisation->id->toString());

    expect($organisationData['slug'])->toBe($organisation->slug);
});

it('reports users without an organisation role with an empty role list', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $organisationData = runOrganisationInfoJson($organisation->slug);

    expect($organisationData['users'])->toBe([
        [
            'id' => $user->id->toString(),
            'name' => $user->name,
            'email' => $user->email,
            'roles' => [],
        ],
    ]);

    $this->artisan('org:info', ['organisation' => $organisation->slug])
        ->assertOk()
        ->expectsTable(['User', 'Email', 'Organisation roles'], [[$user->name, $user->email, '']]);
});

it('fails when the organisation does not exist', function (): void {
    $this->artisan('org:info', ['organisation' => 'no-such-organisation'])
        ->assertFailed();
});

it('fails when the organisation uuid does not exist', function (): void {
    $this->artisan('org:info', ['organisation' => '01a03a6c-c51b-72b5-aecc-000000000000', '--json' => true])
        ->assertFailed();
});

it('does not find soft deleted organisations by default', function (): void {
    $organisation = OrganisationTestHelper::create();
    $organisation->delete();

    $this->artisan('org:info', ['organisation' => $organisation->slug])
        ->assertFailed();
});

it('finds soft deleted organisations with the with-trashed option', function (): void {
    $organisation = OrganisationTestHelper::create();
    $organisation->delete();

    /** @var Organisation $trashedOrganisation */
    $trashedOrganisation = Organisation::withTrashed()
        ->findOrFail($organisation->id->toString());

    $organisationData = runOrganisationInfoJson($organisation->slug, ['--with-trashed' => true]);

    expect($organisationData['slug'])->toBe($organisation->slug)
        ->and($organisationData['deleted_at'])->toBe($trashedOrganisation->deleted_at?->toISOString());
});
