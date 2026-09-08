<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Enums\Import\MappingConfidence;
use App\Enums\Import\MappingTransform;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

use function __;
use function array_slice;
use function count;
use function in_array;
use function sprintf;
use function trim;

/**
 * One source column as the review screen sees it: what it holds, where it is
 * mapped to, and how sure the system is about that.
 */
readonly class ColumnReview
{
    /**
     * Values that mark a column as a yes/no answer rather than free text.
     */
    private const BOOLEAN_VALUES = ['ja', 'nee', 'neen', 'j', 'n', 'yes', 'no', 'true', 'false', 'waar', 'onwaar', '1', '0'];

    /**
     * @param array<int, string> $samples a few distinct values from the source
     * @param array<string, string> $settings the editable mapping entry
     */
    public function __construct(
        public string $header,
        private array $settings,
        private array $samples,
        private bool $fromProfile,
        private TargetOptions $options,
        private ?MappingTransform $transform,
        private bool $isRelation,
        private DateFormatDetector $dateFormatDetector,
    ) {
    }

    public function target(): string
    {
        return $this->settings['target'] ?? '';
    }

    public function isMapped(): bool
    {
        return $this->target() !== '';
    }

    /**
     * @return array<int, string>
     */
    public function samples(int $limit = 3): array
    {
        return array_slice($this->samples, 0, $limit);
    }

    /**
     * Only a reused profile is collapsed: that mapping was already reviewed and
     * approved once. A fresh analysis is always shown in full, however confident
     * the suggestions are.
     */
    public function isSettled(): bool
    {
        return $this->fromProfile && $this->isMapped();
    }

    /**
     * What the column's state actually is: filled in by the analyser, chosen by
     * hand, or still open. Saying "no choice made" over a suggestion the system
     * just made reads as if nothing happened.
     */
    public function statusLabel(): string
    {
        if (!$this->isMapped()) {
            return __('import_mapping.status_open');
        }

        return match ($this->confidence()) {
            MappingConfidence::Exact => __('import_mapping.status_suggested_strong'),
            MappingConfidence::Label, MappingConfidence::Content => __('import_mapping.status_suggested_weak'),
            default => __('import_mapping.status_manual'),
        };
    }

    /**
     * Weak suggestions are worth a second look; the rest are not.
     */
    public function needsAttention(): bool
    {
        return !$this->isMapped() || $this->confidence() === MappingConfidence::Label;
    }

    public function targetLabel(): string
    {
        if (!$this->isMapped()) {
            return __('import_mapping.ignore');
        }

        return $this->options->label($this->target());
    }

    /**
     * The conversion follows from the chosen field, so it is shown rather than
     * asked: picking "date" for a text field is not a meaningful choice.
     */
    public function transformLabel(): string
    {
        if (!$this->isMapped()) {
            return '';
        }

        if ($this->isRelation) {
            return __('import_mapping.transform.relation');
        }

        if (RelationKey::isRemarks($this->target())) {
            return __('import_mapping.transform.remark');
        }

        return ($this->transform ?? MappingTransform::Text)->label();
    }

    /**
     * True when a column holding yes/no values is mapped onto a date field, so
     * the user can say which date a "yes" stands for.
     */
    public function needsTrueDate(): bool
    {
        if ($this->transform !== MappingTransform::Date || $this->samples === []) {
            return false;
        }

        foreach ($this->samples as $sample) {
            if (!in_array(Str::lower(trim($sample)), self::BOOLEAN_VALUES, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Null means "the date of the import", which the engine fills in.
     */
    public function trueDate(): ?string
    {
        if (($this->settings['true_date_mode'] ?? 'today') !== 'fixed') {
            return null;
        }

        $date = $this->settings['true_date'] ?? '';

        return $date === '' ? null : sprintf('%sT00:00:00', $date);
    }

    /**
     * The formats every sample of a date column fits. More than one means the
     * values alone cannot decide, e.g. "04-03-2026".
     *
     * @return array<int, string>
     */
    public function dateFormatCandidates(): array
    {
        if ($this->transform !== MappingTransform::Date || $this->needsTrueDate()) {
            return [];
        }

        return $this->dateFormatDetector->candidates($this->samples);
    }

    /**
     * The format this column is read in: chosen by the user, or the only one
     * that fits. Null when it still has to be chosen or does not apply.
     */
    public function dateFormat(): ?string
    {
        $candidates = $this->dateFormatCandidates();
        $chosen = $this->settings['date_format'] ?? '';

        if ($chosen !== '' && in_array($chosen, $candidates, true)) {
            return $chosen;
        }

        return count($candidates) === 1 ? $candidates[0] : null;
    }

    /**
     * True when the samples fit several formats and the user has not said
     * which one applies.
     */
    public function needsDateFormat(): bool
    {
        return count($this->dateFormatCandidates()) > 1 && $this->dateFormat() === null;
    }

    /**
     * What the first sample means in each candidate format, so the question
     * can be asked in dates rather than in format strings.
     *
     * @return array<string, string> format => rendered date
     */
    public function dateFormatExamples(): array
    {
        $sample = $this->samples[0] ?? '';
        $examples = [];

        foreach ($this->dateFormatCandidates() as $format) {
            // A candidate fits every sample by definition, so this parses.
            $date = $this->dateFormatDetector->parse($sample, $format);
            Assert::isInstanceOf($date, CarbonImmutable::class);

            // A time is only worth showing when there is one.
            $examples[$format] = sprintf(
                '%s (%s)',
                $date->translatedFormat($date->format('H:i') === '00:00' ? 'j F Y' : 'j F Y H:i'),
                $this->dateFormatDetector->describe($format),
            );
        }

        return $examples;
    }

    /**
     * The transform the profile should carry for this column.
     */
    public function profileTransform(): MappingTransform
    {
        if ($this->isRelation || RelationKey::isLookup($this->target())) {
            return MappingTransform::Text;
        }

        $transform = $this->transform ?? MappingTransform::Text;

        return $transform === MappingTransform::Date && $this->needsTrueDate()
            ? MappingTransform::BooleanToDate
            : $transform;
    }

    private function confidence(): ?MappingConfidence
    {
        return MappingConfidence::tryFrom($this->settings['confidence'] ?? MappingConfidence::Manual->value);
    }
}
