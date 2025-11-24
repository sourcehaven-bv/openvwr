<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Filament\Forms\Components\TagsInput;
use Tests\Helpers\FilamentTestHelper;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('can build the options form with correct permission', function (array $permissions, bool $expectsFormVisible): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, $permissions);
    $this->withFilamentSession($user, $organisation);

    $tagsInput = TagsInput::make();
    $actionForm = $tagsInput->getCreateOptionActionForm(FilamentTestHelper::createTestForm());

    expect(!empty($actionForm)) // without permission, the array of form-fields is empty
    ->toBe($expectsFormVisible);
})->with([
    [[], false],
    [[Permission::TAG_CREATE], true],
]);
