<?php

declare(strict_types=1);

use App\Enums\RegisterLayout;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\CreateAvgResponsibleProcessingRecord;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\ViewAvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('switches the layout and redirects back to the record', function (
    RegisterLayout $current,
    RegisterLayout $expected,
): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, ['register_layout' => $current]);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentUser($user)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->callAction('toggle_register_layout')
        ->assertRedirect(EditAvgResponsibleProcessingRecord::getUrl([
            'record' => $avgResponsibleProcessingRecord,
        ]));

    expect($user->refresh()->register_layout)
        ->toBe($expected);
})->with([
    'steps to one page' => [RegisterLayout::STEPS, RegisterLayout::ONE_PAGE],
    'one page to steps' => [RegisterLayout::ONE_PAGE, RegisterLayout::STEPS],
]);

it('switches the layout from the view page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, [
        'register_layout' => RegisterLayout::STEPS,
    ]);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentUser($user)
        ->createLivewireTestable(ViewAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->callAction('toggle_register_layout')
        ->assertRedirect(ViewAvgResponsibleProcessingRecord::getUrl([
            'record' => $avgResponsibleProcessingRecord,
        ]));

    expect($user->refresh()->register_layout)
        ->toBe(RegisterLayout::ONE_PAGE);
});

it('switches the layout from the create page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, [
        'register_layout' => RegisterLayout::STEPS,
    ]);

    $this->asFilamentUser($user)
        ->createLivewireTestable(CreateAvgResponsibleProcessingRecord::class)
        ->callAction('toggle_register_layout')
        ->assertRedirect(CreateAvgResponsibleProcessingRecord::getUrl());

    expect($user->refresh()->register_layout)
        ->toBe(RegisterLayout::ONE_PAGE);
});

it('only changes the layout of the acting user', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, [
        'register_layout' => RegisterLayout::STEPS,
    ]);
    $otherUser = UserTestHelper::createForOrganisation($organisation, [
        'register_layout' => RegisterLayout::STEPS,
    ]);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentUser($user)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->callAction('toggle_register_layout');

    expect($user->refresh()->register_layout)
        ->toBe(RegisterLayout::ONE_PAGE)
        ->and($otherUser->refresh()->register_layout)
        ->toBe(RegisterLayout::STEPS);
});
