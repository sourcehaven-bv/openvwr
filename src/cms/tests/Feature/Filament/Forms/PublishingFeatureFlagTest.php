<?php

declare(strict_types=1);

use App\Filament\Resources\AvgResponsibleProcessingRecordResource\AvgResponsibleProcessingRecordResourceForm;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Tests\Helpers\FilamentTestHelper;

beforeEach(function (): void {
    $this->asFilamentUser();

    config()->set('features.publishing', true);
});

/**
 * The headings of the visible top level steps/sections of a form.
 *
 * @param array<int, mixed> $components
 *
 * @return array<int, string>
 */
function visibleFormLabels(array $components): array
{
    $labels = [];

    foreach ($components as $component) {
        if ($component->isHidden()) {
            continue;
        }

        // Wizard steps expose a label, sections a heading.
        $label = $component instanceof Section ? $component->getHeading() : $component->getLabel();

        $labels[] = (string) $label;
    }

    return $labels;
}

/**
 * @return array<int, string>
 */
function wizardStepLabels(): array
{
    $form = AvgResponsibleProcessingRecordResourceForm::stepsForm(FilamentTestHelper::createTestForm());

    $wizard = $form->getComponents()[0];
    expect($wizard)->toBeInstanceOf(Wizard::class);

    return visibleFormLabels($wizard->getChildComponents());
}

/**
 * @return array<int, string>
 */
function onePageSectionLabels(): array
{
    $form = AvgResponsibleProcessingRecordResourceForm::onePageForm(FilamentTestHelper::createTestForm());

    return visibleFormLabels($form->getComponents());
}

it('shows the publish step in the wizard when publishing is enabled', function (): void {
    expect(wizardStepLabels())->toContain(__('avg_responsible_processing_record.step_publish'));
});

it('hides the publish step in the wizard when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    $labels = wizardStepLabels();

    expect($labels)
        ->not->toContain(__('avg_responsible_processing_record.step_publish'))
        ->and($labels)->toContain(__('avg_responsible_processing_record.step_processing_name'));
});

it('shows the publish section in the one page form when publishing is enabled', function (): void {
    expect(onePageSectionLabels())->toContain(__('avg_responsible_processing_record.step_publish'));
});

it('hides the publish section in the one page form when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    $labels = onePageSectionLabels();

    expect($labels)
        ->not->toContain(__('avg_responsible_processing_record.step_publish'))
        ->and($labels)->toContain(__('avg_responsible_processing_record.step_processing_name'));
});
