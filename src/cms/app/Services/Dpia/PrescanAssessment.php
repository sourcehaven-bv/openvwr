<?php

declare(strict_types=1);

namespace App\Services\Dpia;

use App\Enums\Dpia\PrescanOutcome;

use function __;
use function count;
use function implode;

/**
 * The verdict for one assessment type (DPIA, DTIA, KIA or IAMA), together with
 * the reasons that produced it.
 *
 * The reasons matter as much as the verdict. Paragraaf 1.2 of the Rijksmodel
 * requires a written, substantiated record when it is decided not to carry out
 * a DPIA ("dan dient een beoordeling met onderbouwing schriftelijk plaats te
 * vinden en moet deze worden vastgelegd"), so an unexplained "niet verplicht"
 * would be useless as evidence.
 */
final readonly class PrescanAssessment
{
    /**
     * @param array<int, string> $reasons
     */
    public function __construct(
        public string $type,
        public PrescanOutcome $outcome,
        public array $reasons,
    ) {
    }

    public function isRequired(): bool
    {
        return $this->outcome === PrescanOutcome::REQUIRED;
    }

    public function isAdvised(): bool
    {
        return $this->outcome !== PrescanOutcome::NOT_REQUIRED;
    }

    public function label(): string
    {
        return __('dpia_prescan_record.assessment_' . $this->type);
    }

    /**
     * A single sentence combining the verdict and why, for the outcome summary
     * and the archived motivation.
     */
    public function summary(): string
    {
        if ($this->reasons === []) {
            return __('dpia_prescan_record.summary_none', [
                'assessment' => $this->label(),
                'outcome' => $this->outcome->label(),
            ]);
        }

        return __('dpia_prescan_record.summary_because', [
            'assessment' => $this->label(),
            'outcome' => $this->outcome->label(),
            'reasons' => $this->reasonList(),
        ]);
    }

    private function reasonList(): string
    {
        if (count($this->reasons) === 1) {
            return $this->reasons[0];
        }

        $last = $this->reasons[count($this->reasons) - 1];
        $head = [];

        foreach ($this->reasons as $index => $reason) {
            if ($index === count($this->reasons) - 1) {
                continue;
            }

            $head[] = $reason;
        }

        return implode(', ', $head) . ' ' . __('general.and') . ' ' . $last;
    }
}
