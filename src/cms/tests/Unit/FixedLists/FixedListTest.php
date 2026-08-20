<?php

declare(strict_types=1);

namespace Tests\Unit\FixedLists;

use App\FixedLists\FixedList;
use App\FixedLists\FixedListEntry;

use function expect;
use function it;

function testList(): FixedList
{
    return new class extends FixedList {
        protected function entries(): array
        {
            return [
                FixedListEntry::current('current'),
                FixedListEntry::retired('retired', 'no longer adequate'),
            ];
        }
    };
}

it('offers only current values for new input', function (): void {
    expect(testList()->currentValues())->toBe(['current']);
});

it('keeps retired values known so existing records stay valid', function (): void {
    $list = testList();

    expect($list->allValues())->toBe(['current', 'retired'])
        ->and($list->isKnown('retired'))->toBeTrue()
        ->and($list->isRetired('retired'))->toBeTrue()
        ->and($list->isCurrent('retired'))->toBeFalse();
});

it('reports the reason a value was retired', function (): void {
    $entry = testList()->find('retired');

    expect($entry?->retiredReason)->toBe('no longer adequate');
});

it('does not know values outside the list', function (): void {
    $list = testList();

    expect($list->find('nope'))->toBeNull()
        ->and($list->isKnown('nope'))->toBeFalse()
        ->and($list->isCurrent('nope'))->toBeFalse()
        ->and($list->isRetired('nope'))->toBeFalse();
});
