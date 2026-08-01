<?php

declare(strict_types=1);

namespace App\Services\Dpia;

use App\Enums\Dpia\PrescanOutcome;
use App\Models\Dpia\DpiaPrescanRecord;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;

use function __;
use function app;
use function e;
use function implode;
use function is_array;
use function is_string;
use function sprintf;

/**
 * Renders the four verdicts of a pre-scan as a readable block, from the answers
 * currently on the form.
 *
 * Every verdict is shown with its reasons. That is not decoration: paragraaf
 * 1.2 of the Rijksmodel requires a substantiated written record when it is
 * decided not to carry out a DPIA, and a verdict without a reason cannot serve
 * as that record.
 */
final class PrescanOutcomeSummary
{
    public static function render(Get $get): HtmlString
    {
        $record = self::recordFromForm($get);
        $assessments = app(PrescanEvaluator::class)->evaluate($record);

        $rows = [];

        foreach ($assessments as $assessment) {
            $rows[] = self::row(
                $assessment->label(),
                $assessment->outcome,
                $assessment->summary(),
            );
        }

        $note = '';

        if ($record->algorithm || $record->high_risk_ai) {
            $note = sprintf(
                '<p class="text-sm text-gray-500 mt-3">%s</p>',
                e(__('dpia_prescan_record.iama_note')),
            );
        }

        return new HtmlString('<div class="space-y-2">' . implode('', $rows) . '</div>' . $note);
    }

    private static function row(string $label, PrescanOutcome $outcome, string $summary): string
    {
        $colour = match ($outcome) {
            PrescanOutcome::REQUIRED => 'text-danger-600 dark:text-danger-400',
            PrescanOutcome::RECOMMENDED => 'text-warning-600 dark:text-warning-400',
            PrescanOutcome::NOT_REQUIRED => 'text-gray-500',
        };

        return sprintf(
            '<div><span class="font-semibold %s">%s: %s</span>'
            . '<p class="text-sm text-gray-500">%s</p></div>',
            $colour,
            e($label),
            e($outcome->label()),
            e($summary),
        );
    }

    /**
     * An unsaved model carrying the current form state, so the evaluator can
     * work on one shape regardless of whether the record exists yet.
     */
    private static function recordFromForm(Get $get): DpiaPrescanRecord
    {
        $record = new DpiaPrescanRecord();

        $record->new_legislation = (bool) $get('new_legislation');
        $record->departmental_policy = (bool) $get('departmental_policy');
        $record->public_cloud = (bool) $get('public_cloud');
        $record->ap_criteria = self::toArray($get('ap_criteria'));
        $record->edpb_criteria = self::toArray($get('edpb_criteria'));
        $record->international_transfer = (bool) $get('international_transfer');
        $record->outside_eea = (bool) $get('outside_eea');
        $record->transfer_mechanism = self::toStringOrNull($get('transfer_mechanism'));
        $record->digital_service = (bool) $get('digital_service');
        $record->minors = (bool) $get('minors');
        $record->algorithm = (bool) $get('algorithm');
        $record->high_risk_ai = (bool) $get('high_risk_ai');

        return $record;
    }

    /**
     * @return array<int, string>
     */
    private static function toArray(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $values = [];

        foreach ($value as $item) {
            $item = self::toStringOrNull($item);

            if ($item === null) {
                continue;
            }

            $values[] = $item;
        }

        return $values;
    }

    private static function toStringOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
