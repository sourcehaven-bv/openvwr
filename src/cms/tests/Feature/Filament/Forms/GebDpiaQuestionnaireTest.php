<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Forms;

use App\Filament\Forms\GebDpiaQuestionnaire;
use Filament\Forms\Get;
use Mockery;

use function expect;
use function it;

/**
 * @param array<string, bool> $state
 */
function gebGetMock(array $state): Get
{
    /** @var Get $mock */
    $mock = Mockery::mock(Get::class);
    $mock->shouldReceive('__invoke')
        ->andReturnUsing(static fn (string $field): bool => $state[$field] ?? false);

    return $mock;
}

it('hides every criterion once a GEB has been executed', function (): void {
    $visible = GebDpiaQuestionnaire::criterionVisible('geb_dpia_automated');

    expect($visible(gebGetMock([GebDpiaQuestionnaire::EXECUTED_FIELD => true])))->toBeFalse();
});

it('shows the first criterion while nothing is answered', function (): void {
    $visible = GebDpiaQuestionnaire::criterionVisible('geb_dpia_automated');

    expect($visible(gebGetMock([])))->toBeTrue();
});

it('hides a later criterion once an earlier one is answered yes', function (): void {
    $visible = GebDpiaQuestionnaire::criterionVisible('geb_dpia_large_scale_processing');

    expect($visible(gebGetMock(['geb_dpia_automated' => true])))->toBeFalse();
});

it('shows a later criterion while every earlier one is still no', function (): void {
    $visible = GebDpiaQuestionnaire::criterionVisible('geb_dpia_high_risk_freedoms');

    expect($visible(gebGetMock([])))->toBeTrue();
});
