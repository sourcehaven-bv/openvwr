<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use Closure;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\HtmlString;

use function __;
use function e;
use function is_array;
use function is_string;

/**
 * Shared logic for the "is a GEB (DPIA) mandatory?" pre-screen questionnaire on
 * the AVG responsible processing record.
 *
 * The six criteria are OR-ed and progressive: the first "ja" concludes that a
 * GEB is mandatory and the remaining questions are skipped. If a GEB was
 * already carried out (geb_dpia_executed) the questionnaire is moot and hidden.
 *
 * Keeping the visibility, outcome and reset-on-save rules here means the form,
 * the infolist and the model observer stay in agreement.
 */
final class GebDpiaQuestionnaire
{
    public const EXECUTED_FIELD = 'geb_dpia_executed';

    /**
     * The criteria in the order they are asked. Order matters: a criterion is
     * only shown while every earlier criterion is unanswered ("nee").
     */
    public const CRITERIA = [
        'geb_dpia_automated',
        'geb_dpia_large_scale_processing',
        'geb_dpia_large_scale_monitoring',
        'geb_dpia_list_required',
        'geb_dpia_criteria_wp248',
        'geb_dpia_high_risk_freedoms',
    ];

    /**
     * Build one criterion toggle: labelled, live, and progressively visible.
     * The WP248 criterion gets an info button that opens a modal listing the
     * nine criteria (a hover tooltip cannot format a list, so it is a popup).
     */
    public static function criterionToggle(string $field): Toggle
    {
        $toggle = Toggle::make($field)
            ->label(__('avg_responsible_processing_record.' . $field))
            ->live()
            ->visible(self::criterionVisible($field));

        if ($field === 'geb_dpia_criteria_wp248') {
            $toggle->hintAction(
                Action::make('geb_dpia_criteria_wp248_info')
                    ->label(__('avg_responsible_processing_record.geb_dpia_criteria_wp248_info_label'))
                    ->icon('heroicon-o-information-circle')
                    ->color('gray')
                    ->link()
                    ->modalHeading(__('avg_responsible_processing_record.geb_dpia_criteria_wp248'))
                    ->modalContent(self::wp248ModalContent())
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('general.close')),
            );
        }

        return $toggle;
    }

    private static function wp248ModalContent(): HtmlString
    {
        $intro = __('avg_responsible_processing_record.help_geb_dpia_criteria_wp248');

        $criteria = __('avg_responsible_processing_record.geb_dpia_criteria_wp248_items');

        $listItems = '';
        if (is_array($criteria)) {
            foreach ($criteria as $item) {
                if (is_string($item)) {
                    $listItems .= '<li style="margin-bottom:0.25rem;">' . e($item) . '</li>';
                }
            }
        }

        return new HtmlString(
            '<p class="text-sm text-gray-600 dark:text-gray-400" style="margin-bottom:0.75rem;">' . e($intro) . '</p>'
            . '<ol class="text-sm text-gray-950 dark:text-gray-200" '
            . 'style="list-style:decimal;padding-left:1.5rem;">'
            . $listItems
            . '</ol>',
        );
    }

    /**
     * A criterion is visible while no GEB was executed and every earlier
     * criterion is still "nee" (false). The first "ja" therefore hides the rest.
     */
    public static function criterionVisible(string $field): Closure
    {
        return static function (Get $get) use ($field): bool {
            if ($get(self::EXECUTED_FIELD) === true) {
                return false;
            }

            foreach (self::CRITERIA as $criterion) {
                if ($criterion === $field) {
                    return true;
                }

                if ($get($criterion) === true) {
                    return false;
                }
            }

            // Unreachable for a known criterion: the loop always matches $field
            // above. Guards against an unlisted field name.
            return true; // @codeCoverageIgnore
        };
    }

    public static function outcomeContent(): Closure
    {
        return static function (Get $get): HtmlString {
            $outcome = self::resolveOutcome($get);
            $text = __('avg_responsible_processing_record.geb_dpia_outcome_' . $outcome);

            if ($outcome !== 'mandatory') {
                return new HtmlString('<span class="text-sm text-gray-600 dark:text-gray-400">' . e($text) . '</span>');
            }

            // A mandatory GEB means the user still has work to do, so make it a
            // prominent warning banner rather than a subtle line of text.
            return new HtmlString(
                '<div class="flex items-start gap-2 rounded-lg border border-warning-300 bg-warning-50 '
                . 'p-3 text-warning-800 dark:border-warning-500/40 dark:bg-warning-500/10 dark:text-warning-300">'
                . '<svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" '
                . 'stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" '
                . 'd="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 '
                . '1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />'
                . '</svg>'
                . '<span class="text-sm font-semibold">' . e($text) . '</span>'
                . '</div>',
            );
        };
    }

    /**
     * The stored answers for criteria the questionnaire never reached are not
     * meaningful, so reset them: all criteria when a GEB was executed, or every
     * criterion after the first "ja". Returns the values to persist.
     *
     * @param array<string, bool> $answers keyed by criterion field name
     *
     * @return array<string, bool>
     */
    public static function resetUnreached(bool $executed, array $answers): array
    {
        if ($executed) {
            return self::allFalse();
        }

        $reached = true;
        $result = [];
        foreach (self::CRITERIA as $criterion) {
            $value = $reached && ($answers[$criterion] ?? false) === true;
            $result[$criterion] = $value;

            // Everything after the first "ja" is unreached.
            if ($value) {
                $reached = false;
            }
        }

        return $result;
    }

    /**
     * The Ja/Nee toggles have no "unanswered" state (they default to nee), so
     * an outcome is always available: executed wins, then the first "ja" makes
     * it mandatory, otherwise it is not mandatory.
     *
     * @param array<string, bool> $answers keyed by criterion field name
     *
     * @return 'executed'|'mandatory'|'not_mandatory'
     */
    public static function outcomeFor(bool $executed, array $answers): string
    {
        if ($executed) {
            return 'executed';
        }

        foreach (self::CRITERIA as $criterion) {
            if (($answers[$criterion] ?? false) === true) {
                return 'mandatory';
            }
        }

        return 'not_mandatory';
    }

    private static function resolveOutcome(Get $get): string
    {
        $answers = [];
        foreach (self::CRITERIA as $criterion) {
            $answers[$criterion] = $get($criterion) === true;
        }

        return self::outcomeFor($get(self::EXECUTED_FIELD) === true, $answers);
    }

    /**
     * @return array<string, bool>
     */
    private static function allFalse(): array
    {
        $result = [];
        foreach (self::CRITERIA as $criterion) {
            $result[$criterion] = false;
        }

        return $result;
    }
}
