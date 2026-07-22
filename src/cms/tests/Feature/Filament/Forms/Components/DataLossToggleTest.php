<?php

declare(strict_types=1);

use App\Filament\Forms\Components\DataLossToggle;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Processor;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

use function Pest\Livewire\livewire;

function editPageForRecordWithProcessors(bool $withProcessor): array
{
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['has_processors' => true]);

    if ($withProcessor) {
        $processor = Processor::factory()->recycle($organisation)->create();
        $avgResponsibleProcessingRecord->processors()->attach($processor);
    }

    return [$user, $avgResponsibleProcessingRecord];
}

it('asks for confirmation when processors would be discarded', function (): void {
    [$user, $avgResponsibleProcessingRecord] = editPageForRecordWithProcessors(true);

    $this->asFilamentUser($user);

    livewire(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->getRouteKey(),
    ])
        ->set('data.has_processors', false)
        ->assertFormComponentActionMounted(
            'has_processors',
            DataLossToggle::confirmActionName('has_processors'),
        );
});

it('keeps the toggle on while the confirmation is pending', function (): void {
    [$user, $avgResponsibleProcessingRecord] = editPageForRecordWithProcessors(true);

    $this->asFilamentUser($user);

    livewire(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->getRouteKey(),
    ])
        ->set('data.has_processors', false)
        ->assertFormSet(['has_processors' => true]);
});

it('does not ask for confirmation when there is nothing to lose', function (): void {
    [$user, $avgResponsibleProcessingRecord] = editPageForRecordWithProcessors(false);

    $this->asFilamentUser($user);

    livewire(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->getRouteKey(),
    ])
        ->set('data.has_processors', false)
        ->assertFormSet(['has_processors' => false]);
});

it('turns the toggle off once the confirmation is accepted', function (): void {
    [$user, $avgResponsibleProcessingRecord] = editPageForRecordWithProcessors(true);

    $this->asFilamentUser($user);

    livewire(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->getRouteKey(),
    ])
        ->set('data.has_processors', false)
        ->call('callMountedFormComponentAction')
        ->assertFormSet(['has_processors' => false]);
});
