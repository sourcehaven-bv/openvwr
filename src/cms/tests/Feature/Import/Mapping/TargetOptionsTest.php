<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Enums\Import\ImportTarget;
use App\Import\Mapping\TargetOptions;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('offers the FG note only to someone who may read FG notes', function (): void {
    $this->asFilamentUser();

    expect((new TargetOptions(ImportTarget::AvgResponsibleProcessingRecord))->flat())->toHaveKey('fgRemark');

    $user = UserTestHelper::create();
    $this->withPermissions($user, [Permission::CORE_ENTITY_IMPORT]);
    $this->withFilamentSession($user, OrganisationTestHelper::create());

    expect((new TargetOptions(ImportTarget::AvgResponsibleProcessingRecord))->flat())
        ->not->toHaveKey('fgRemark')
        ->toHaveKey('remarks');
});
