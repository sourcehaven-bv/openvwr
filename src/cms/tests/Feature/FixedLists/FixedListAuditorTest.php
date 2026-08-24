<?php

declare(strict_types=1);

namespace Tests\Feature\FixedLists;

use App\FixedLists\Audit\FixedListAuditor;
use App\FixedLists\Audit\FixedListFinding;
use App\FixedLists\Audit\FixedListFindingType;
use App\FixedLists\Lists\AdequacyDecisionCountryList;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use Illuminate\Support\Facades\Lang;
use Tests\Doubles\FixedLists\CountryListWithRetiredEntry;
use Webmozart\Assert\Assert;

use function app;
use function array_filter;
use function array_values;
use function beforeEach;
use function expect;
use function it;

/**
 * Only the findings for the country column of the responsible record, so that the other registered columns
 * do not make these expectations brittle.
 *
 * @return list<FixedListFinding>
 */
function countryFindings(FixedListFindingType $type): array
{
    $findings = app(FixedListAuditor::class)->audit();

    return array_values(array_filter(
        $findings,
        static fn (FixedListFinding $finding): bool => $finding->column === 'country'
            && $finding->type === $type
            && $finding->model === AvgResponsibleProcessingRecord::class,
    ));
}

beforeEach(function (): void {
    // A double, so these tests keep passing when an actual adequacy decision is granted or withdrawn.
    app()->instance(AdequacyDecisionCountryList::class, new CountryListWithRetiredEntry());
});

it('reports no retired or unknown findings when the stored value is current', function (): void {
    AvgResponsibleProcessingRecord::factory()->create(['outside_eu' => true, 'country' => 'Japan']);

    expect(countryFindings(FixedListFindingType::RETIRED))->toBeEmpty()
        ->and(countryFindings(FixedListFindingType::UNKNOWN))->toBeEmpty();
});

it('reports records holding a retired value, with the reason and record count', function (): void {
    AvgResponsibleProcessingRecord::factory()->create([
        'outside_eu' => true,
        'country' => CountryListWithRetiredEntry::RETIRED_VALUE,
    ]);
    AvgResponsibleProcessingRecord::factory()->create([
        'outside_eu' => true,
        'country' => CountryListWithRetiredEntry::RETIRED_VALUE,
    ]);

    $findings = countryFindings(FixedListFindingType::RETIRED);

    expect($findings)->toHaveCount(1)
        ->and($findings[0]->value)->toBe(CountryListWithRetiredEntry::RETIRED_VALUE)
        ->and($findings[0]->reason)->toBe(CountryListWithRetiredEntry::RETIRED_REASON)
        ->and($findings[0]->count)->toBe(2);
});

it('reports records holding a value the list never contained', function (): void {
    AvgResponsibleProcessingRecord::factory()->create(['outside_eu' => true, 'country' => 'Atlantis']);

    $findings = countryFindings(FixedListFindingType::UNKNOWN);

    expect($findings)->toHaveCount(1)
        ->and($findings[0]->value)->toBe('Atlantis')
        ->and($findings[0]->count)->toBe(1);
});

it('counts soft deleted records, because they can be restored', function (): void {
    $record = AvgResponsibleProcessingRecord::factory()->create(['outside_eu' => true, 'country' => 'Atlantis']);
    $record->delete();

    $findings = countryFindings(FixedListFindingType::UNKNOWN);

    expect($findings)->toHaveCount(1)
        ->and($findings[0]->count)->toBe(1);
});

it('reports list values that no record uses', function (): void {
    AvgResponsibleProcessingRecord::factory()->create(['outside_eu' => true, 'country' => 'Japan']);

    $findings = countryFindings(FixedListFindingType::UNUSED);

    expect($findings)->toHaveCount(1)
        ->and($findings[0]->value)->toBe(CountryListWithRetiredEntry::RETIRED_VALUE);
});

it('ignores records without a value', function (): void {
    AvgResponsibleProcessingRecord::factory()->create(['outside_eu' => true, 'country' => null]);
    AvgResponsibleProcessingRecord::factory()->create(['outside_eu' => true, 'country' => '']);

    expect(countryFindings(FixedListFindingType::UNKNOWN))->toBeEmpty();
});

it('does not crash on a value that looks like a number', function (): void {
    // PHP turns integer-like array keys into ints, which used to break the string type declarations.
    AvgResponsibleProcessingRecord::factory()->create([
        'outside_eu' => true,
        'country' => '2024',
    ]);

    $findings = countryFindings(FixedListFindingType::UNKNOWN);

    expect($findings)->toHaveCount(1)
        ->and($findings[0]->value)->toBe('2024');
});

it('does not report the "anders, namelijk" sentinel as unknown', function (): void {
    $sentinel = Lang::get('general.country_other', [], 'nl');
    Assert::string($sentinel);

    AvgResponsibleProcessingRecord::factory()->create([
        'outside_eu' => true,
        'country' => $sentinel,
        'country_other' => 'India',
    ]);

    expect(countryFindings(FixedListFindingType::UNKNOWN))->toBeEmpty();
});

it('does not report the sentinel stored under another locale as unknown', function (): void {
    $englishSentinel = Lang::get('general.country_other', [], 'en');
    Assert::string($englishSentinel);

    AvgResponsibleProcessingRecord::factory()->create([
        'outside_eu' => true,
        'country' => $englishSentinel,
        'country_other' => 'India',
    ]);

    expect(countryFindings(FixedListFindingType::UNKNOWN))->toBeEmpty();
});
