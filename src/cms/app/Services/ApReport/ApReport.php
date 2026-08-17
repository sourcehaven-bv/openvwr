<?php

declare(strict_types=1);

namespace App\Services\ApReport;

use App\Models\DataBreachRecord;

use function array_filter;
use function array_merge;
use function array_values;
use function count;

/**
 * The full preparation for one data breach record, in the chapter order of the
 * AP notification form.
 */
class ApReport
{
    /**
     * @param array<int, ApChapter> $chapters
     */
    public function __construct(
        public readonly DataBreachRecord $dataBreachRecord,
        public readonly array $chapters,
    ) {
    }

    /**
     * @return array<int, ApAnswer>
     */
    public function answers(): array
    {
        $answers = [];
        foreach ($this->chapters as $chapter) {
            $answers = array_merge($answers, $chapter->answers);
        }

        return $answers;
    }

    /**
     * The questions the register cannot answer. Shown up front so the officer
     * knows what to collect before opening the online form.
     *
     * @return array<int, ApAnswer>
     */
    public function missingAnswers(): array
    {
        return array_values(array_filter(
            $this->answers(),
            static fn (ApAnswer $answer): bool => $answer->isMissing(),
        ));
    }

    /**
     * The suggestions taken from linked content, which the officer must check
     * against what actually leaked before filing them.
     *
     * @return array<int, ApAnswer>
     */
    public function answersNeedingConfirmation(): array
    {
        return array_values(array_filter(
            $this->answers(),
            static fn (ApAnswer $answer): bool => $answer->needsConfirmation(),
        ));
    }

    public function missingCount(): int
    {
        return count($this->missingAnswers());
    }

    public function needsConfirmationCount(): int
    {
        return count($this->answersNeedingConfirmation());
    }
}
