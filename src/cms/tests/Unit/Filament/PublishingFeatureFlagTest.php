<?php

declare(strict_types=1);

namespace Tests\Unit\Filament;

use App\Filament\Forms\Components\PublicFromField;
use App\Filament\Forms\Components\Section\StaticWebsiteCheckSection as FormStaticWebsiteCheckSection;
use App\Filament\Infolists\Components\Section\StaticWebsiteCheckSection as InfolistStaticWebsiteCheckSection;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\AvgResponsibleProcessingRecordResourceFormSchemas;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\AvgResponsibleProcessingRecordResourceInfolist;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\AvgResponsibleProcessingRecordResourceInfolistSchemas;
use App\Filament\Resources\PublicWebsiteTreeResource;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Infolist;
use Tests\Helpers\LivewireTestHelper;
use Tests\TestCase;

use function __;
use function beforeEach;
use function config;
use function expect;
use function it;
use function uses;

uses(TestCase::class);

beforeEach(function (): void {
    config()->set('features.publishing', true);
});

/**
 * The headings of the visible top level tabs/sections of an infolist.
 *
 * @param array<int, mixed> $components
 *
 * @return array<int, string>
 */
function visibleLabels(array $components): array
{
    $labels = [];

    foreach ($components as $component) {
        if ($component->isHidden()) {
            continue;
        }

        // Tabs expose a label, sections a heading.
        $label = $component instanceof Section ? $component->getHeading() : $component->getLabel();

        $labels[] = (string) $label;
    }

    return $labels;
}

/**
 * @return array<int, string>
 */
function infolistTabLabels(): array
{
    $infolist = AvgResponsibleProcessingRecordResourceInfolist::stepsInfolist(
        Infolist::make(LivewireTestHelper::createTestFormComponent()),
    );

    $tabs = $infolist->getComponents()[0];
    expect($tabs)->toBeInstanceOf(Tabs::class);

    return visibleLabels($tabs->getChildComponents());
}

/**
 * @return array<int, string>
 */
function onePageInfolistSectionLabels(): array
{
    $infolist = AvgResponsibleProcessingRecordResourceInfolist::onePageInfolist(
        Infolist::make(LivewireTestHelper::createTestFormComponent()),
    );

    return visibleLabels($infolist->getComponents());
}

it('shows the publish form fields when publishing is enabled', function (): void {
    expect(AvgResponsibleProcessingRecordResourceFormSchemas::getPublish())->not->toBeEmpty();
});

it('hides the publish form fields when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    expect(AvgResponsibleProcessingRecordResourceFormSchemas::getPublish())->toBeEmpty();
});

it('shows the publish infolist entries when publishing is enabled', function (): void {
    expect(AvgResponsibleProcessingRecordResourceInfolistSchemas::getPublish())->not->toBeEmpty();
});

it('hides the publish infolist entries when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    expect(AvgResponsibleProcessingRecordResourceInfolistSchemas::getPublish())->toBeEmpty();
});

it('shows the static website check section on forms when publishing is enabled', function (): void {
    expect(FormStaticWebsiteCheckSection::makeTable()->isHidden())->toBeFalse();
});

it('hides the static website check section on forms when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    expect(FormStaticWebsiteCheckSection::makeTable()->isHidden())->toBeTrue();
});

it('shows the static website check section on infolists when publishing is enabled', function (): void {
    expect(InfolistStaticWebsiteCheckSection::makeTable()->isHidden())->toBeFalse();
});

it('hides the static website check section on infolists when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    expect(InfolistStaticWebsiteCheckSection::makeTable()->isHidden())->toBeTrue();
});

it('shows the public from field when publishing is enabled', function (): void {
    $field = PublicFromField::makeForModel(AvgResponsibleProcessingRecord::class);

    expect($field->isHidden())->toBeFalse();
});

it('hides the public from field when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    $field = PublicFromField::makeForModel(AvgResponsibleProcessingRecord::class);

    expect($field->isHidden())->toBeTrue();
});

it('registers the public website tree navigation when publishing is enabled', function (): void {
    expect(PublicWebsiteTreeResource::shouldRegisterNavigation())->toBeTrue();
});

it('hides the public website tree navigation when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    expect(PublicWebsiteTreeResource::shouldRegisterNavigation())->toBeFalse();
});

it('shows the publish tab in the infolist when publishing is enabled', function (): void {
    expect(infolistTabLabels())->toContain(__('avg_responsible_processing_record.step_publish'));
});

it('hides the publish tab in the infolist when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    $labels = infolistTabLabels();

    expect($labels)
        ->not->toContain(__('avg_responsible_processing_record.step_publish'))
        ->and($labels)->toContain(__('avg_responsible_processing_record.step_processing_name'));
});

it('shows the publish section in the one page infolist when publishing is enabled', function (): void {
    expect(onePageInfolistSectionLabels())->toContain(__('avg_responsible_processing_record.step_publish'));
});

it('hides the publish section in the one page infolist when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    $labels = onePageInfolistSectionLabels();

    expect($labels)
        ->not->toContain(__('avg_responsible_processing_record.step_publish'))
        ->and($labels)->toContain(__('avg_responsible_processing_record.step_processing_name'));
});
