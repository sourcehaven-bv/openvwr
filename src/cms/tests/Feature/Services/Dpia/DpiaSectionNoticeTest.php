<?php

/**
 * The aandachtspunten shown under paragraaf 16 and 17: which risks nobody
 * addresses, and which maatregelen address nothing. Both read the repeater
 * state of the form rather than the database, so they keep up while the
 * invuller is still typing.
 */

declare(strict_types=1);

use App\Services\Dpia\DpiaSectionNotice;
use Tests\Helpers\Dpia\ArrayGet;

it('says nothing when there are no risks yet', function (): void {
    expect(DpiaSectionNotice::risks(new ArrayGet(['risks' => [], 'measures' => []])))->toBeNull();
});

it('names the risks that no maatregel addresses', function (): void {
    $notice = DpiaSectionNotice::risks(new ArrayGet([
        'risks' => [
            'record-1' => ['title' => 'Onbevoegde inzage'],
            'record-2' => ['title' => 'Datalek'],
        ],
        'measures' => [
            'record-9' => ['description' => 'Toegang beperken', 'risks' => ['record-1']],
        ],
    ]));

    expect($notice?->toHtml())
        ->toContain(e(__('dpia_quality.section_risks_without_measure')))
        ->toContain('Datalek')
        ->not->toContain('Onbevoegde inzage');
});

it('says nothing when every risk is addressed', function (): void {
    $notice = DpiaSectionNotice::risks(new ArrayGet([
        'risks' => ['record-1' => ['title' => 'Onbevoegde inzage']],
        'measures' => [
            'record-9' => ['description' => 'Toegang beperken', 'risks' => ['record-1']],
        ],
    ]));

    expect($notice)->toBeNull();
});

// A risk without a title cannot be named in the list, so it is skipped rather
// than reported as an empty bullet.
it('skips a risk that has no title yet', function (): void {
    $notice = DpiaSectionNotice::risks(new ArrayGet([
        'risks' => [
            'record-1' => ['title' => '   '],
            'record-2' => ['title' => null],
            'record-3' => 'geen array',
        ],
        'measures' => [],
    ]));

    expect($notice)->toBeNull();
});

it('copes with a state that is not a repeater array', function (): void {
    expect(DpiaSectionNotice::risks(new ArrayGet(['risks' => 'geen array', 'measures' => null])))
        ->toBeNull()
        ->and(DpiaSectionNotice::measures(new ArrayGet(['risks' => null, 'measures' => 'geen array'])))
        ->toBeNull();
});

it('names the maatregelen that address no risk', function (): void {
    $notice = DpiaSectionNotice::measures(new ArrayGet([
        'risks' => ['record-1' => ['title' => 'Onbevoegde inzage']],
        'measures' => [
            'record-8' => ['description' => 'Losse maatregel', 'risks' => []],
            'record-9' => ['description' => 'Toegang beperken', 'risks' => ['record-1']],
        ],
    ]));

    expect($notice?->toHtml())
        ->toContain(e(__('dpia_quality.section_measures_without_risk')))
        ->toContain('Losse maatregel')
        ->not->toContain('Toegang beperken');
});

it('says nothing when every maatregel addresses a risk', function (): void {
    $notice = DpiaSectionNotice::measures(new ArrayGet([
        'risks' => ['record-1' => ['title' => 'Onbevoegde inzage']],
        'measures' => [
            'record-9' => ['description' => 'Toegang beperken', 'risks' => ['record-1']],
        ],
    ]));

    expect($notice)->toBeNull();
});

it('skips a maatregel that has no description yet', function (): void {
    $notice = DpiaSectionNotice::measures(new ArrayGet([
        'risks' => [],
        'measures' => [
            'record-8' => ['description' => '  ', 'risks' => []],
            'record-9' => ['description' => null, 'risks' => []],
        ],
    ]));

    expect($notice)->toBeNull();
});
