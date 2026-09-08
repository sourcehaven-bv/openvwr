<?php

declare(strict_types=1);

use App\Import\Mapping\MultiValue;

it('splits a cell on line breaks, dropping blanks', function (): void {
    expect(MultiValue::split("Firma A\n\nFirma B\n"))->toBe(['Firma A', 'Firma B']);
});

it('splits a cell without line breaks on comma and space', function (): void {
    expect(MultiValue::split('Naam, Adres en woonplaats'))->toBe(['Naam', 'Adres en woonplaats']);
});

it('keeps the blanks of an exported list so the positions hold', function (string $cell, array $entries): void {
    expect(MultiValue::entries($cell))->toBe($entries);
})->with([
    'blank in the middle' => ['a@x, , b@x', ['a@x', '', 'b@x']],
    'blank at the start' => [', b@x', ['', 'b@x']],
    'blank at the end, trimmed by the reader' => ['a@x,', ['a@x', '']],
    'line breaks' => ["a@x\n\nb@x", ['a@x', '', 'b@x']],
    'single value' => ['a@x', ['a@x']],
]);

it('takes a folded row as it is and ignores what is not text', function (): void {
    expect(MultiValue::entries(['a@x', null, 'b@x']))->toBe(['a@x', '', 'b@x'])
        ->and(MultiValue::entries(42))->toBe([]);
});

it('keeps a choice that itself contains a comma in one piece', function (): void {
    $options = ['Naam', 'Adres, postcode en woonplaats', 'Telefoonnummer'];

    expect(MultiValue::split('Naam, Adres, postcode en woonplaats, Telefoonnummer', "\n", $options))
        ->toBe(['Naam', 'Adres, postcode en woonplaats', 'Telefoonnummer'])
        ->and(MultiValue::split('Naam, Onbekend', "\n", $options))->toBe(['Naam', 'Onbekend']);
});
