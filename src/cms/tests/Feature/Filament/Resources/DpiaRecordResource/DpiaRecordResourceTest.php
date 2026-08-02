<?php

declare(strict_types=1);

use App\Enums\Dpia\DpiaSubjectType;
use App\Enums\RegisterLayout;
use App\Filament\Resources\DpiaPrescanRecordResource;
use App\Filament\Resources\DpiaPrescanRecordResource\Pages\CreateDpiaPrescanRecord;
use App\Filament\Resources\DpiaRecordResource;
use App\Models\Dpia\DpiaRecord;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the DPIA list page', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(DpiaRecordResource::getUrl('index', tenant: $organisation))
        ->assertOk();
});

// Both layouts render the same 17 paragraphs, so both are worth loading.
it('loads the DPIA create page in all layouts', function (RegisterLayout $registerLayout): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, ['register_layout' => $registerLayout]);

    $this->asFilamentUser($user)
        ->get(DpiaRecordResource::getUrl('create', tenant: $organisation))
        ->assertOk();
})->with(RegisterLayout::cases());

it('loads the DPIA edit page in all layouts', function (RegisterLayout $registerLayout): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, ['register_layout' => $registerLayout]);
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();

    $this->asFilamentUser($user)
        ->get(DpiaRecordResource::getUrl('edit', ['record' => $dpiaRecord], tenant: $organisation))
        ->assertOk();
})->with(RegisterLayout::cases());

it('loads the pre-scan list page', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(DpiaPrescanRecordResource::getUrl('index', tenant: $organisation))
        ->assertOk();
});

it('loads the pre-scan create page', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(DpiaPrescanRecordResource::getUrl('create', tenant: $organisation))
        ->assertOk();
});

it('gives a new DPIA an entity number', function (): void {
    $organisation = OrganisationTestHelper::create();

    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();

    expect($dpiaRecord->getNumber())->not->toBeEmpty();
});

it('defaults to a DPIA about a verwerking', function (): void {
    $organisation = OrganisationTestHelper::create();

    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create([
        'subject_type' => DpiaSubjectType::PROCESSING,
    ]);

    expect($dpiaRecord->subject_type)->toBe(DpiaSubjectType::PROCESSING);
});

// The pre-scan is normally filled in on the day it is carried out, so the date
// should not have to be typed. It stays editable for scans entered afterwards.
it('defaults the pre-scan date to today', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(CreateDpiaPrescanRecord::class)
        ->assertFormFieldExists('assessed_at')
        ->assertFormSet(static function (array $state): bool {
            // The picker carries a time component; only the date matters here.
            return str_starts_with((string) $state['assessed_at'], today()->format('Y-m-d'));
        });
});
