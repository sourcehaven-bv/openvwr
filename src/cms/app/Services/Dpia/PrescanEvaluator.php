<?php

declare(strict_types=1);

namespace App\Services\Dpia;

use App\Enums\Dpia\PrescanOutcome;
use App\Models\Dpia\DpiaPrescanRecord;

use function __;
use function count;
use function implode;
use function is_array;
use function is_string;
use function trans_choice;

/**
 * Turns the pre-scan answers into verdicts for DPIA, DTIA, KIA and IAMA.
 *
 * The DPIA rules follow paragraaf 1.2 of the Rijksmodel:
 *  - nieuwe wet- of regelgeving, departementaal beleid of een publieke
 *    cloudvoorziening maken een DPIA verplicht;
 *  - een enkel item van de AP-lijst maakt een DPIA verplicht;
 *  - twee of meer EDPB-criteria maken een DPIA verplicht, bij precies een
 *    criterium moet beoordeeld worden of sprake is van een hoog risico -- dat
 *    is hier "aanbevolen", zodat het een bewuste afweging blijft.
 *
 * Every rule contributes a sentence explaining itself, so the outcome can be
 * defended afterwards.
 */
final class PrescanEvaluator
{
    public const TYPE_DPIA = 'dpia';
    public const TYPE_DTIA = 'dtia';
    public const TYPE_KIA = 'kia';
    public const TYPE_IAMA = 'iama';

    /**
     * @return array<int, PrescanAssessment>
     */
    public function evaluate(DpiaPrescanRecord $record): array
    {
        return [
            $this->assessDpia($record),
            $this->assessDtia($record),
            $this->assessKia($record),
            $this->assessIama($record),
        ];
    }

    public function dpiaOutcome(DpiaPrescanRecord $record): PrescanOutcome
    {
        return $this->assessDpia($record)->outcome;
    }

    /**
     * A single line per assessment, suitable for storing as the archived
     * motivation of this pre-scan.
     */
    public function motivation(DpiaPrescanRecord $record): string
    {
        $lines = [];

        foreach ($this->evaluate($record) as $assessment) {
            $lines[] = $assessment->summary();
        }

        return implode("\n", $lines);
    }

    private function assessDpia(DpiaPrescanRecord $record): PrescanAssessment
    {
        $reasons = [];

        if ($record->new_legislation) {
            $reasons[] = __('dpia_prescan_record.reason_new_legislation');
        }

        if ($record->departmental_policy) {
            $reasons[] = __('dpia_prescan_record.reason_departmental_policy');
        }

        if ($record->public_cloud) {
            $reasons[] = __('dpia_prescan_record.reason_public_cloud');
        }

        $apCount = count($this->selected($record->ap_criteria));

        if ($apCount >= 1) {
            $reasons[] = trans_choice('dpia_prescan_record.reason_ap_list', $apCount, ['count' => $apCount]);
        }

        $edpbCount = count($this->selected($record->edpb_criteria));

        if ($edpbCount >= 2) {
            $reasons[] = __('dpia_prescan_record.reason_edpb_list', ['count' => $edpbCount]);
        }

        if ($reasons !== []) {
            return new PrescanAssessment(self::TYPE_DPIA, PrescanOutcome::REQUIRED, $reasons);
        }

        // Exactly one EDPB criterion: the Rijksmodel asks for an explicit
        // high-risk assessment rather than a straight yes or no.
        if ($edpbCount === 1) {
            return new PrescanAssessment(
                self::TYPE_DPIA,
                PrescanOutcome::RECOMMENDED,
                [__('dpia_prescan_record.reason_edpb_single')],
            );
        }

        return new PrescanAssessment(self::TYPE_DPIA, PrescanOutcome::NOT_REQUIRED, []);
    }

    /**
     * A transfer impact assessment is expected when personal data leaves the
     * EEA on the basis of standard contractual clauses or another mechanism
     * (rather than an adequacy decision).
     */
    private function assessDtia(DpiaPrescanRecord $record): PrescanAssessment
    {
        if (!$record->international_transfer || !$record->outside_eea) {
            return new PrescanAssessment(self::TYPE_DTIA, PrescanOutcome::NOT_REQUIRED, []);
        }

        $mechanism = $record->transfer_mechanism;

        if ($mechanism === 'adequaatheidsbesluit') {
            return new PrescanAssessment(
                self::TYPE_DTIA,
                PrescanOutcome::NOT_REQUIRED,
                [__('dpia_prescan_record.reason_adequacy_decision')],
            );
        }

        if ($mechanism === null || $mechanism === '') {
            return new PrescanAssessment(
                self::TYPE_DTIA,
                PrescanOutcome::RECOMMENDED,
                [__('dpia_prescan_record.reason_transfer_unknown')],
            );
        }

        return new PrescanAssessment(
            self::TYPE_DTIA,
            PrescanOutcome::REQUIRED,
            [__('dpia_prescan_record.reason_transfer_mechanism')],
        );
    }

    private function assessKia(DpiaPrescanRecord $record): PrescanAssessment
    {
        if ($record->digital_service && $record->minors) {
            return new PrescanAssessment(
                self::TYPE_KIA,
                PrescanOutcome::RECOMMENDED,
                [__('dpia_prescan_record.reason_minors')],
            );
        }

        return new PrescanAssessment(self::TYPE_KIA, PrescanOutcome::NOT_REQUIRED, []);
    }

    /**
     * IAMA is not mandatory by law, so it never returns "verplicht" here. It is
     * strongly advised for high-risk AI, and it does not replace a DPIA: the
     * IAMA itself states "Een DPIA kan de toetsing aan het IAMA dan ook niet
     * vervangen".
     */
    private function assessIama(DpiaPrescanRecord $record): PrescanAssessment
    {
        if ($record->high_risk_ai) {
            return new PrescanAssessment(
                self::TYPE_IAMA,
                PrescanOutcome::RECOMMENDED,
                [__('dpia_prescan_record.reason_high_risk_ai')],
            );
        }

        if ($record->algorithm) {
            return new PrescanAssessment(
                self::TYPE_IAMA,
                PrescanOutcome::RECOMMENDED,
                [__('dpia_prescan_record.reason_algorithm')],
            );
        }

        return new PrescanAssessment(self::TYPE_IAMA, PrescanOutcome::NOT_REQUIRED, []);
    }

    /**
     * @return array<int, string>
     */
    private function selected(mixed $criteria): array
    {
        if (!is_array($criteria)) {
            return [];
        }

        $selected = [];

        foreach ($criteria as $criterion) {
            if (!is_string($criterion) || $criterion === '') {
                continue;
            }

            $selected[] = $criterion;
        }

        return $selected;
    }
}
