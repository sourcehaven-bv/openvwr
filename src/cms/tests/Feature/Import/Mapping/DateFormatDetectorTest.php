<?php

declare(strict_types=1);

use App\Import\Mapping\DateFormatDetector;

it('keeps every format all samples fit', function (): void {
    expect((new DateFormatDetector())->candidates(['04-03-2026', '', '12-11-2026']))
        ->toBe(['d-m-Y', 'm-d-Y']);
});

it('decides on its own when only one format fits', function (): void {
    $detector = new DateFormatDetector();

    expect($detector->detect(['13-03-2026', '04-03-2026']))->toBe('d-m-Y')
        ->and($detector->detect(['2026-03-04']))->toBe('Y-m-d')
        ->and($detector->detect(['04-03-2026']))->toBeNull()
        ->and($detector->detect(['n.v.t.']))->toBeNull()
        ->and($detector->detect(['', ' ']))->toBeNull();
});

it('rejects a value that only fits a format by rolling over', function (): void {
    expect((new DateFormatDetector())->parse('31-02-2026', 'd-m-Y'))->toBeNull();
});

it('says which part of a format comes first', function (): void {
    $detector = new DateFormatDetector();

    expect($detector->describe('d-m-Y'))->toBe(__('import_mapping.date_order.day_first'))
        ->and($detector->describe('m/d/Y'))->toBe(__('import_mapping.date_order.month_first'))
        ->and($detector->describe('Y-m-d H:i'))->toBe(__('import_mapping.date_order.year_first'));
});
