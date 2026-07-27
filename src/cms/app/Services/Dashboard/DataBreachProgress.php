<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\DataBreachRecord;
use Carbon\CarbonImmutable;

use function __;

/**
 * How far along the handling of one data breach is.
 *
 * Deliberately does not judge whether the breach had to be reported to the
 * Autoriteit Persoonsgegevens. Article 33 only obliges notification when the
 * breach is likely to result in a risk to the rights and freedoms of natural
 * persons, and that assessment lives in estimated_risk as free text — the
 * register has no field saying "assessed as not notifiable". Deriving a missed
 * deadline from ap_reported alone would therefore mark every correctly
 * unreported low-risk breach as overdue, forever.
 *
 * What can honestly be said is how long a breach has been open, since
 * completed_at is the register's own record of the handling being finished.
 */
final readonly class DataBreachProgress
{
    /**
     * Article 33 GDPR: notification to the supervisory authority is due without
     * undue delay and, where feasible, within 72 hours of becoming aware. Used
     * only to draw attention to a breach that is both open and past that mark,
     * never to assert that a report was required.
     */
    public const int NOTIFICATION_WINDOW_IN_HOURS = 72;

    private function __construct(
        private ?CarbonImmutable $discoveredAt,
        private bool $apReported,
    ) {
    }

    public static function for(DataBreachRecord $dataBreachRecord): self
    {
        return new self($dataBreachRecord->discovered_at, $dataBreachRecord->ap_reported);
    }

    /**
     * Whether this row should stand out: still open, not yet reported to the AP,
     * and past the point where that decision should have been made.
     *
     * "Should have been decided by now" — not "is too late to report".
     */
    public function needsUrgentAttention(): bool
    {
        if ($this->apReported || $this->discoveredAt === null) {
            return false;
        }

        return $this->discoveredAt
            ->addHours(self::NOTIFICATION_WINDOW_IN_HOURS)
            ->isPast();
    }

    /**
     * How long the breach has been open, which is the thing a privacy officer
     * is weighing. Relative rather than an absolute date: the question is how
     * long this has been sitting, not which calendar day it happened.
     */
    public function discoveredLabel(): string
    {
        if ($this->discoveredAt === null) {
            return __('dashboard.breach.discovered_unknown');
        }

        return __('dashboard.breach.open_for', [
            'duration' => $this->discoveredAt->diffForHumans(syntax: CarbonImmutable::DIFF_ABSOLUTE),
        ]);
    }

    /**
     * What is still outstanding, phrased as a state of the record rather than as
     * a verdict on the reporting obligation.
     */
    public function statusLabel(): string
    {
        if ($this->discoveredAt === null) {
            return __('dashboard.breach.no_discovery_date');
        }

        if (!$this->apReported) {
            return __('dashboard.breach.ap_decision_open');
        }

        return __('dashboard.breach.in_progress');
    }
}
