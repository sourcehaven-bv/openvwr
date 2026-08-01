<?php

declare(strict_types=1);

namespace App\Filament\Notifications;

use App\Models\Dpia\DpiaRecord;
use App\Services\Dpia\DpiaQualityChecker;
use Filament\Notifications\Notification;

use function app;
use function array_slice;
use function count;
use function implode;
use function trans_choice;

/**
 * Reports the aandachtspunten of a DPIA after saving.
 *
 * Shown as a persistent warning rather than a modal that has to be dismissed
 * before saving: the save has already succeeded, and a DPIA that is still being
 * filled in is expected to have loose ends. The point is to make them visible,
 * not to stand in the way.
 */
final class DpiaQualityNotification
{
    /**
     * How many findings to spell out before falling back to a count. Enough to
     * be useful, few enough that the notification stays readable.
     */
    private const MAX_LISTED = 4;

    public static function sendFor(DpiaRecord $dpiaRecord): void
    {
        $findings = app(DpiaQualityChecker::class)->check($dpiaRecord);

        if ($findings === []) {
            return;
        }

        $lines = [];

        foreach (array_slice($findings, 0, self::MAX_LISTED) as $finding) {
            $lines[] = $finding->paragraphLabel() . ': ' . $finding->message();
        }

        $remaining = count($findings) - count($lines);

        if ($remaining > 0) {
            $lines[] = trans_choice('dpia_quality.and_more', $remaining, ['count' => $remaining]);
        }

        Notification::make()
            ->title(trans_choice('dpia_quality.count', count($findings), ['count' => count($findings)]))
            ->body(implode("\n\n", $lines))
            ->warning()
            ->persistent()
            ->send();
    }
}
