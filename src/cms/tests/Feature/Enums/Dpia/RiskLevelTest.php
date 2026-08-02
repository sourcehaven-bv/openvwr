<?php

declare(strict_types=1);

use App\Enums\Dpia\RiskLevel;
use App\Models\Dpia\DpiaRisk;
use Tests\Helpers\Model\OrganisationTestHelper;

// The matrix from paragraaf 16: low on either axis keeps the risk low, high on
// either axis (with neither low) makes it high.
it('suggests a risk level from kans x impact', function (
    ?RiskLevel $likelihood,
    ?RiskLevel $impact,
    ?RiskLevel $expected,
): void {
    expect(RiskLevel::suggest($likelihood, $impact))->toBe($expected);
})->with([
    'low x low' => [RiskLevel::LOW, RiskLevel::LOW, RiskLevel::LOW],
    'low x high' => [RiskLevel::LOW, RiskLevel::HIGH, RiskLevel::LOW],
    'high x low' => [RiskLevel::HIGH, RiskLevel::LOW, RiskLevel::LOW],
    'medium x medium' => [RiskLevel::MEDIUM, RiskLevel::MEDIUM, RiskLevel::MEDIUM],
    'medium x high' => [RiskLevel::MEDIUM, RiskLevel::HIGH, RiskLevel::HIGH],
    'high x high' => [RiskLevel::HIGH, RiskLevel::HIGH, RiskLevel::HIGH],
    'nothing chosen yet' => [null, null, null],
    'only kans chosen' => [RiskLevel::HIGH, null, null],
]);

// The matrix is illustrative, so a deviation is flagged rather than corrected.
it('detects when the chosen level deviates from the matrix', function (): void {
    $organisation = OrganisationTestHelper::create();

    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'likelihood' => RiskLevel::HIGH,
        'impact' => RiskLevel::HIGH,
        'level' => RiskLevel::LOW,
    ]);

    expect($risk->suggestedLevel())->toBe(RiskLevel::HIGH)
        ->and($risk->deviatesFromMatrix())->toBeTrue();
});

it('does not flag a level that follows the matrix', function (): void {
    $organisation = OrganisationTestHelper::create();

    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'likelihood' => RiskLevel::HIGH,
        'impact' => RiskLevel::HIGH,
        'level' => RiskLevel::HIGH,
    ]);

    expect($risk->deviatesFromMatrix())->toBeFalse();
});
