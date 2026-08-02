<?php

declare(strict_types=1);

namespace App\Services\Dpia;

use function __;

/**
 * One thing worth looking at before the DPIA is considered done.
 *
 * These are advisory on purpose. A DPIA is a professional judgement, not a
 * form to satisfy: a risk can legitimately have no measure yet while the
 * assessment is still in progress, and the invuller is the one who decides
 * when it is finished. Blocking the save would only teach people to enter
 * placeholder text.
 */
final readonly class DpiaQualityFinding
{
    /**
     * @param string $paragraph the paragraph this is about, e.g. "16"
     * @param array<string, string|int> $replacements
     */
    public function __construct(
        public string $key,
        public string $paragraph,
        public array $replacements = [],
    ) {
    }

    public function message(): string
    {
        return __('dpia_quality.' . $this->key, $this->replacements);
    }

    public function paragraphLabel(): string
    {
        return __('dpia_quality.paragraph', ['paragraph' => $this->paragraph]);
    }
}
