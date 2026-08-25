<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Enums\LabelColor;
use App\Filament\Forms\Components\TagsInput;
use App\Models\Tag;
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

/**
 * Same failure mode as the label filter (see TagFilterTest): Filament's
 * getOptionLabelFromRecordUsing keys its result by the record's key, and ids
 * are cast to a Uuid object here, which PHP cannot use as an array key. The
 * path is getOptionLabels(), reached when the picker draws the labels that are
 * already chosen.
 */
it('labels the selected labels with their colour', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $this->withFilamentSession($user, $organisation);

    $tag = Tag::factory()->for($organisation)->create(['color' => LabelColor::PURPLE]);

    $tagsInput = TagsInput::make();
    $tagsInput->container(FilamentTestHelper::createTestForm());
    $tagsInput->state([$tag->id->toString()]);

    $labels = $tagsInput->getOptionLabels();

    expect($labels)->toHaveKey($tag->id->toString())
        ->and($labels[$tag->id->toString()])->toContain($tag->name)
        ->and($labels[$tag->id->toString()])->toContain('purple-600');
});

it('labels nothing when no labels are selected', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $this->withFilamentSession($user, $organisation);

    $tagsInput = TagsInput::make();
    $tagsInput->container(FilamentTestHelper::createTestForm());
    $tagsInput->state([]);

    expect($tagsInput->getOptionLabels())->toBe([]);
});
