<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\Wpg\WpgProcessingRecord;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

use function array_values;
use function beforeEach;
use function expect;
use function it;
use function json_decode;

use const JSON_THROW_ON_ERROR;

beforeEach(function (): void {
    // The seeded organisations of the testing database would drown out the
    // fixtures, so every test starts from an empty organisation table inside
    // the surrounding transaction.
    foreach (Organisation::withTrashed()->get() as $organisation) {
        $organisation->forceDelete();
    }
});

/**
 * @param array<string, bool> $options
 *
 * @return list<array<string, mixed>>
 */
function runOrganisationListJson(array $options = []): array
{
    $bufferedOutput = new BufferedOutput();
    Artisan::call('org:list', ['--json' => true] + $options, $bufferedOutput);

    return array_values(json_decode($bufferedOutput->fetch(), true, 512, JSON_THROW_ON_ERROR));
}

it('can list organisations as a table', function (): void {
    $organisation = OrganisationTestHelper::create();
    UserTestHelper::createForOrganisation($organisation);

    $this->artisan('org:list')
        ->assertOk()
        ->expectsTable(['Name', 'Slug', 'Users', 'Processings', 'Legal entity', 'Created at', 'Deleted at'], [
            [
                $organisation->name,
                $organisation->slug,
                '1',
                '0',
                $organisation->responsibleLegalEntity->name,
                $organisation->created_at->toDateTimeString(),
                '-',
            ],
        ]);
});

it('can list organisations as json', function (): void {
    $organisation = OrganisationTestHelper::create();
    UserTestHelper::createForOrganisation($organisation);

    expect(runOrganisationListJson())->toBe([
        [
            'id' => $organisation->id->toString(),
            'name' => $organisation->name,
            'slug' => $organisation->slug,
            'users_count' => 1,
            'processing_records_count' => 0,
            'responsible_legal_entity' => $organisation->responsibleLegalEntity->name,
            'created_at' => $organisation->created_at->toISOString(),
            'deleted_at' => null,
        ],
    ]);
});

it('emits an empty json array when there are no organisations', function (): void {
    expect(runOrganisationListJson())->toBe([]);

    $this->artisan('org:list', ['--json' => true])
        ->assertOk();
});

it('excludes soft deleted organisations by default', function (): void {
    $organisation = OrganisationTestHelper::create();
    $organisation->delete();

    expect(runOrganisationListJson())->toBe([]);
});

it('includes soft deleted organisations with the with-trashed option', function (): void {
    $organisation = OrganisationTestHelper::create();
    $organisation->delete();

    /** @var Organisation $trashedOrganisation */
    $trashedOrganisation = Organisation::withTrashed()
        ->findOrFail($organisation->id->toString());

    $rows = runOrganisationListJson(['--with-trashed' => true]);

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['slug'])->toBe($organisation->slug)
        ->and($rows[0]['deleted_at'])->toBe($trashedOrganisation->deleted_at?->toISOString());

    $this->artisan('org:list', ['--with-trashed' => true])
        ->assertOk()
        ->expectsTable(['Name', 'Slug', 'Users', 'Processings', 'Legal entity', 'Created at', 'Deleted at'], [
            [
                $organisation->name,
                $organisation->slug,
                '0',
                '0',
                $organisation->responsibleLegalEntity->name,
                $organisation->created_at->toDateTimeString(),
                $trashedOrganisation->deleted_at?->toDateTimeString(),
            ],
        ]);
});

it('sums the processing records of every register type', function (): void {
    $organisation = OrganisationTestHelper::create();

    AvgResponsibleProcessingRecord::factory()->for($organisation)->recycle($organisation)->count(1)->create();
    AvgProcessorProcessingRecord::factory()->for($organisation)->recycle($organisation)->count(2)->create();
    WpgProcessingRecord::factory()->for($organisation)->recycle($organisation)->count(3)->create();

    $rows = runOrganisationListJson();

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['processing_records_count'])->toBe(6);
});
