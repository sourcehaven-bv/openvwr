<?php

declare(strict_types=1);

use App\Enums\Dpia\PersonalDataType;
use App\Services\Dpia\SpecialCategoriesSummary;
use Tests\Helpers\Dpia\ArrayGet;

/**
 * Paragraaf 12 reports on the classification made in paragraaf 2 instead of
 * asking again, so what matters here is that it names exactly the gegevens
 * that need a ground, and separates the ones that still lack it.
 */
function summaryFor(mixed $personalData): string
{
    return SpecialCategoriesSummary::render(new ArrayGet(['personalData' => $personalData]))->toHtml();
}

it('says so when paragraaf 2 has no gegevens yet', function (): void {
    expect(summaryFor([]))->toContain(__('dpia_record.special_categories_no_personal_data'));
});

it('says so when the state is not a repeater array at all', function (): void {
    expect(summaryFor(null))->toContain(__('dpia_record.special_categories_no_personal_data'));
});

it('reports nothing to justify when every gegeven is gewoon', function (): void {
    $summary = summaryFor([
        ['description' => 'Naam', 'type' => PersonalDataType::ORDINARY->value],
    ]);

    expect($summary)->toContain(__('dpia_record.special_categories_none'));
});

// "Gevoelig" raises the risk assessment but is not a legal category, so it
// never asks for a ground.
it('does not ask for a ground for gevoelige gegevens', function (): void {
    $summary = summaryFor([
        ['description' => 'Locatie', 'type' => PersonalDataType::SENSITIVE->value],
    ]);

    expect($summary)->toContain(__('dpia_record.special_categories_none'));
});

it('lists a bijzonder gegeven that still lacks a ground', function (): void {
    $summary = summaryFor([
        ['description' => 'Gezondheidsgegevens', 'type' => PersonalDataType::SPECIAL->value],
    ]);

    expect($summary)
        ->toContain(__('dpia_record.special_categories_missing_ground'))
        ->toContain('Gezondheidsgegevens')
        ->toContain(PersonalDataType::SPECIAL->label());
});

it('lists a gegeven with a ground separately', function (): void {
    $summary = summaryFor([
        [
            'description' => 'Strafrechtelijke gegevens',
            'type' => PersonalDataType::CRIMINAL->value,
            'exception_ground' => 'Artikel 33 UAVG',
        ],
    ]);

    expect($summary)
        ->toContain(__('dpia_record.special_categories_with_ground'))
        ->toContain('Strafrechtelijke gegevens')
        ->not->toContain(__('dpia_record.special_categories_missing_ground'));
});

it('separates the gegevens that have a ground from those that do not', function (): void {
    $summary = summaryFor([
        [
            'description' => 'Burgerservicenummer',
            'type' => PersonalDataType::NATIONAL_IDENTIFIER->value,
            'exception_ground' => 'Wet algemene bepalingen burgerservicenummer',
        ],
        ['description' => 'Gezondheidsgegevens', 'type' => PersonalDataType::SPECIAL->value],
        ['description' => 'Naam', 'type' => PersonalDataType::ORDINARY->value],
    ]);

    expect($summary)
        ->toContain(__('dpia_record.special_categories_missing_ground'))
        ->toContain(__('dpia_record.special_categories_with_ground'))
        ->toContain('Burgerservicenummer')
        ->toContain('Gezondheidsgegevens')
        ->not->toContain('Naam');
});

// Whitespace is not a ground: the field has to say something.
it('treats a blank ground as no ground', function (): void {
    $summary = summaryFor([
        [
            'description' => 'Gezondheidsgegevens',
            'type' => PersonalDataType::SPECIAL->value,
            'exception_ground' => '   ',
        ],
    ]);

    expect($summary)->toContain(__('dpia_record.special_categories_missing_ground'));
});

it('falls back to a placeholder when the gegeven has no description', function (): void {
    $summary = summaryFor([
        ['description' => '  ', 'type' => PersonalDataType::SPECIAL->value],
    ]);

    expect($summary)->toContain(__('dpia_quality.unnamed'));
});

it('accepts a type that is already an enum', function (): void {
    $summary = summaryFor([
        ['description' => 'Gezondheidsgegevens', 'type' => PersonalDataType::SPECIAL],
    ]);

    expect($summary)->toContain('Gezondheidsgegevens');
});

it('ignores rows that are not filled in', function (): void {
    $summary = summaryFor([
        'not-an-array',
        ['description' => 'Zonder type'],
        ['description' => 'Leeg type', 'type' => ''],
        ['description' => 'Onbekend type', 'type' => 'bestaat-niet'],
    ]);

    expect($summary)->toContain(__('dpia_record.special_categories_none'));
});

// The description ends up in HTML, so it has to be escaped.
it('escapes the description', function (): void {
    $summary = summaryFor([
        ['description' => '<script>alert(1)</script>', 'type' => PersonalDataType::SPECIAL->value],
    ]);

    expect($summary)
        ->not->toContain('<script>')
        ->toContain('&lt;script&gt;');
});
