<?php

declare(strict_types=1);

use App\Filament\Resources\AlgorithmRecordResource\AlgorithmRecordResourceInfolistSchemas;
use Filament\Infolists\Components\TextEntry;
use Tests\TestCase;

uses(TestCase::class);

it('formats nullable impact boolean entries', function (?bool $state, ?string $expectedTranslationKey): void {
    $impactSchema = AlgorithmRecordResourceInfolistSchemas::getImpact();
    $impactEntry = $impactSchema[0];

    expect($impactSchema)
        ->toHaveCount(4)
        ->and($impactEntry)
        ->toBeInstanceOf(TextEntry::class);

    $expected = $expectedTranslationKey === null ? null : (string) __($expectedTranslationKey);

    expect($impactEntry->formatState($state))
        ->toBe($expected);
})->with([
    'true' => [true, 'general.yes'],
    'false' => [false, 'general.no'],
    'null' => [null, null],
]);
