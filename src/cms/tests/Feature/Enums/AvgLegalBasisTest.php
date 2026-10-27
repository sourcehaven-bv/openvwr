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

it('shows the label the form uses', function (): void {
    expect(AvgLegalBasis::PUBLIC_TASK->label())->toBe(__('avg_goal_legal_base.options')[4]);
});

it('falls back to the value without a translated label', function (): void {
    app('translator')->addLines(['avg_goal_legal_base.options' => 'geen lijst'], app()->getLocale());

    expect(AvgLegalBasis::CONSENT->label())->toBe('consent');
});
