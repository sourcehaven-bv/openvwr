<?php

declare(strict_types=1);

namespace App\Services\Dpia;

use App\Enums\Dpia\PrescanOutcome;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;

use function __;
use function count;
use function e;
use function is_array;
use function is_string;
use function sprintf;
use function trans_choice;

/**
 * Live verdicts shown next to the questions that produce them.
 *
 * The counting rules of the AP and EDPB lists are not something the invuller
 * should have to apply by hand: one AP-criterion already makes a DPIA
 * mandatory, two EDPB-criteria do the same, and a single EDPB-criterion means
 * a high-risk assessment is needed. Stating those rules in a helper text asks
 * the reader to count and compare; showing what the current answers add up to
 * does the work for them.
 */
final class PrescanLiveStatus
{
    /**
     * Paragraaf 1.2: a single item from the AP list is already decisive.
     */
    public static function apCriteria(Get $get): HtmlString
    {
        $count = self::countSelected($get('ap_criteria'));

        if ($count === 0) {
            return self::neutral(__('dpia_prescan_record.status_ap_none'));
        }

        return self::verdict(
            PrescanOutcome::REQUIRED,
            trans_choice('dpia_prescan_record.status_ap_selected', $count, ['count' => $count]),
        );
    }

    /**
     * Paragraaf 1.2: two EDPB criteria make a DPIA mandatory, one means the
     * high-risk question has to be answered explicitly.
     */
    public static function edpbCriteria(Get $get): HtmlString
    {
        $count = self::countSelected($get('edpb_criteria'));

        if ($count === 0) {
            return self::neutral(__('dpia_prescan_record.status_edpb_none'));
        }

        if ($count === 1) {
            return self::verdict(
                PrescanOutcome::RECOMMENDED,
                __('dpia_prescan_record.status_edpb_one'),
            );
        }

        return self::verdict(
            PrescanOutcome::REQUIRED,
            __('dpia_prescan_record.status_edpb_two', ['count' => $count]),
        );
    }

    /**
     * Whether the chosen doorgiftemechanisme calls for a DTIA.
     */
    public static function transfer(Get $get): ?HtmlString
    {
        if (!(bool) $get('international_transfer') || !(bool) $get('outside_eea')) {
            return null;
        }

        $mechanism = $get('transfer_mechanism');

        if (!is_string($mechanism) || $mechanism === '') {
            return self::neutral(__('dpia_prescan_record.status_transfer_unknown'));
        }

        if ($mechanism === 'adequaatheidsbesluit') {
            return self::verdict(
                PrescanOutcome::NOT_REQUIRED,
                __('dpia_prescan_record.status_transfer_adequacy'),
            );
        }

        return self::verdict(
            PrescanOutcome::REQUIRED,
            __('dpia_prescan_record.status_transfer_dtia'),
        );
    }

    /**
     * Whether any artikel 27 category was recognised.
     */
    public static function highRiskAi(Get $get): ?HtmlString
    {
        if (!(bool) $get('algorithm')) {
            return null;
        }

        $count = self::countSelected($get('high_risk_ai_categories'));

        if ($count === 0) {
            return self::neutral(__('dpia_prescan_record.status_high_risk_ai_none'));
        }

        return self::verdict(
            PrescanOutcome::RECOMMENDED,
            trans_choice('dpia_prescan_record.status_high_risk_ai', $count, ['count' => $count]),
        );
    }

    private static function verdict(PrescanOutcome $outcome, string $message): HtmlString
    {
        $class = match ($outcome) {
            PrescanOutcome::REQUIRED => 'text-danger-600 dark:text-danger-400',
            PrescanOutcome::RECOMMENDED => 'text-warning-600 dark:text-warning-400',
            PrescanOutcome::NOT_REQUIRED => 'text-success-600 dark:text-success-400',
        };

        return new HtmlString(
            sprintf('<p class="text-sm font-medium %s">%s</p>', $class, e($message)),
        );
    }

    private static function neutral(string $message): HtmlString
    {
        return new HtmlString(
            sprintf('<p class="text-sm text-gray-500">%s</p>', e($message)),
        );
    }

    private static function countSelected(mixed $criteria): int
    {
        if (!is_array($criteria)) {
            return 0;
        }

        $selected = [];

        foreach ($criteria as $criterion) {
            if (is_string($criterion) && $criterion !== '') {
                $selected[] = $criterion;
            }
        }

        return count($selected);
    }
}
