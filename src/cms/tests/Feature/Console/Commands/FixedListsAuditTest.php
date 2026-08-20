<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\FixedLists\Lists\AdequacyDecisionCountryList;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use Tests\Doubles\FixedLists\CountryListWithRetiredEntry;

use function app;
use function it;

it('runs when there is nothing to report', function (): void {
    app()->instance(AdequacyDecisionCountryList::class, new CountryListWithRetiredEntry());
    AvgResponsibleProcessingRecord::factory()->create([
        'outside_eu' => true,
        'country' => 'Japan',
    ]);

    $this->artisan('fixed-lists:audit', ['--type' => 'retired'])
        ->assertOk()
        ->expectsOutputToContain('All stored values match their fixed list.');
});

it('runs when there are findings to report', function (): void {
    app()->instance(AdequacyDecisionCountryList::class, new CountryListWithRetiredEntry());
    // The observer clears the country when the record does not transfer outside the EER.
    AvgResponsibleProcessingRecord::factory()->create([
        'outside_eu' => true,
        'country' => CountryListWithRetiredEntry::RETIRED_VALUE,
    ]);

    $this->artisan('fixed-lists:audit', ['--type' => 'retired'])
        ->assertOk()
        ->doesntExpectOutputToContain('All stored values match their fixed list.');
});

it('leaves unused values out unless they are asked for', function (): void {
    app()->instance(AdequacyDecisionCountryList::class, new CountryListWithRetiredEntry());
    AvgResponsibleProcessingRecord::factory()->create([
        'outside_eu' => true,
        'country' => 'Japan',
    ]);

    $this->artisan('fixed-lists:audit')
        ->assertOk()
        ->expectsOutputToContain('All stored values match their fixed list.');

    $this->artisan('fixed-lists:audit', ['--type' => 'unused'])
        ->assertOk()
        ->doesntExpectOutputToContain('All stored values match their fixed list.');
});

it('rejects an unknown finding type', function (): void {
    $this->artisan('fixed-lists:audit', ['--type' => 'bogus'])
        ->assertFailed()
        ->expectsOutputToContain('Unknown finding type "bogus".');
});
