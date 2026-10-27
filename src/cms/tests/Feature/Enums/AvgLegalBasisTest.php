<?php

declare(strict_types=1);

use App\Enums\AvgLegalBasis;

it('maps the stored label to a legal basis', function (?string $label, ?AvgLegalBasis $expected): void {
    expect(AvgLegalBasis::fromLabel($label))->toBe($expected);
})->with([
    'dutch label' => ['Taak van algemeen belang', AvgLegalBasis::PUBLIC_TASK],
    'english label' => ['Consent of the data subject', AvgLegalBasis::CONSENT],
    'last option' => ['Gerechtvaardigd belang verantwoordelijke', AvgLegalBasis::LEGITIMATE_INTERESTS],
    'unknown label' => ['Iets anders', null],
    'no label' => [null, null],
]);
