<?php

declare(strict_types=1);

namespace Filament\Actions;

use App\Enums\CoreEntityDataCollectionSource;
use App\Enums\YesNoUnknown;
use App\Filament\Actions\Exports\ExportColumn;
use App\Services\DateFormatService;
use Carbon\CarbonImmutable;
use Tests\Fixtures\Filament\FormattedExportLabel;

use function expect;
use function fake;
use function it;

it('can format boolean', function (bool $input, string $expectedOutput): void {
    $exportColumn = new ExportColumn(fake()->word());
    $output = $exportColumn->formatState($input);

    expect($output)
        ->toBe($expectedOutput);
})->with([
    [true, YesNoUnknown::YES->value],
    [false, YesNoUnknown::NO->value],
]);

it('can format date', function (): void {
    $date = CarbonImmutable::instance(fake()->dateTime());

    $exportColumn = new ExportColumn(fake()->word());
    $output = $exportColumn->formatState($date);

    expect($output)
        ->toBe($date->format(DateFormatService::FORMAT_DATE_TIME));
});

it('can format enum', function (): void {
    $enum = fake()->randomElement(YesNoUnknown::cases());

    $exportColumn = new ExportColumn(fake()->word());
    $output = $exportColumn->formatState($enum);

    expect($output)
        ->toBe($enum->value);
});

it('can format enum that implements hasLabel', function (): void {
    $enum = fake()->randomElement(CoreEntityDataCollectionSource::cases());

    $exportColumn = new ExportColumn(fake()->word());
    $output = $exportColumn->formatState($enum);

    expect($output)
        ->toBe($enum->getLabel());
});


it('exports a rich enum label as plain spreadsheet text', function (): void {
    expect((new ExportColumn('status'))->formatState(FormattedExportLabel::EXAMPLE))
        ->toBe('Leesbare waarde');
});
