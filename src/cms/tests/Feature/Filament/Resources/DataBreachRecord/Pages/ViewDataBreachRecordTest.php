<?php

declare(strict_types=1);

use App\Enums\RegisterLayout;
use App\Filament\Resources\DataBreachRecord\Pages\ViewDataBreachRecord;
use App\Filament\Resources\DataBreachRecordResource;
use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecord\Closed;
use App\Models\States\DataBreachRecord\InResponse;
use App\Models\States\DataBreachRecord\NoBreach;
use App\Models\States\DataBreachRecord\Reported;
use App\Models\States\DataBreachRecord\Verified;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('can load the view page with all layouts', function (RegisterLayout $registerLayout): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, ['register_layout' => $registerLayout]);

    $dataBreachRecord = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentUser($user)
        ->get(DataBreachRecordResource::getUrl('view', ['record' => $dataBreachRecord]))
        ->assertSuccessful();
})->with(RegisterLayout::cases());

it('can transition a data breach record through a header action', function (
    string $fromState,
    string $toState,
): void {
    $organisation = OrganisationTestHelper::create();

    $dataBreachRecord = DataBreachRecord::factory()
        ->recycle($organisation)
        ->inState($fromState)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewDataBreachRecord::class, [
            'record' => $dataBreachRecord->getRouteKey(),
        ])
        ->callAction(sprintf('data_breach_record_transition_to_%s', $toState::$name));

    $this->assertDatabaseHas(DataBreachRecord::class, [
        'id' => $dataBreachRecord->id,
        'state' => $toState::$name,
    ]);
})->with([
    // Forward through the workflow — covers every concrete action class.
    'verify' => [Reported::class, Verified::class],
    'respond' => [Verified::class, InResponse::class],
    'close' => [InResponse::class, Closed::class],
    'mark as no breach' => [Reported::class, NoBreach::class],
    // A correction backwards — covers the reopen action and the
    // transition_back label branch.
    'reopen' => [Closed::class, InResponse::class],
]);
