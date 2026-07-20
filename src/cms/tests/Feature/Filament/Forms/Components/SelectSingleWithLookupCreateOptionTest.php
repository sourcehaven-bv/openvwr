<?php

declare(strict_types=1);

use App\Components\Uuid\UuidInterface;
use App\Filament\Resources\AlgorithmRecordResource\Pages\CreateAlgorithmRecord;
use App\Models\Algorithm\AlgorithmTheme;
use Tests\Helpers\Model\OrganisationTestHelper;

it('selects the newly created option after inline creation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $name = 'Inline created theme';

    $component = $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateAlgorithmRecord::class);

    $component->callFormComponentAction('algorithm_theme_id', 'createOption', data: ['name' => $name]);

    $theme = AlgorithmTheme::query()->where('name', $name)->sole();
    $instance = $component->instance();

    expect($instance->form->getRawState()['algorithm_theme_id'])->toBe($theme->id->toString())
        ->and($instance->getFormSelectOptionLabel('data.algorithm_theme_id'))->toBe($name);
});

it('casts lookup list ids to the uuid value object', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    $theme = AlgorithmTheme::factory()->recycle($organisation)->create();

    expect($theme->getKey())->toBeInstanceOf(UuidInterface::class)
        ->and($theme->fresh()->getKey())->toBeInstanceOf(UuidInterface::class);
});
