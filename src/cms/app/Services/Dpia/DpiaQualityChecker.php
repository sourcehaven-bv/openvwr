<?php

declare(strict_types=1);

namespace App\Services\Dpia;

use App\Enums\Dpia\RiskLevel;
use App\Models\Dpia\DpiaMeasure;
use App\Models\Dpia\DpiaPersonalData;
use App\Models\Dpia\DpiaRecord;
use App\Models\Dpia\DpiaRisk;

use function __;
use function mb_strlen;
use function mb_substr;
use function trim;

/**
 * Looks for internal inconsistencies in a DPIA.
 *
 * This deliberately does not check for empty fields. A half-filled DPIA is a
 * DPIA in progress, which is normal and not worth nagging about. What it does
 * check is whether the answers contradict each other or leave an obligation
 * from the AVG unanswered -- the kind of thing an FG would send back.
 *
 * Every finding is advisory. Nothing here blocks saving.
 */
final class DpiaQualityChecker
{
    /**
     * @return array<int, DpiaQualityFinding>
     */
    public function check(DpiaRecord $dpiaRecord): array
    {
        $dpiaRecord->loadMissing(['personalData', 'risks.measures', 'measures.risks']);

        return [
            ...$this->checkPersonalData($dpiaRecord),
            ...$this->checkTransfers($dpiaRecord),
            ...$this->checkRisks($dpiaRecord),
            ...$this->checkMeasures($dpiaRecord),
        ];
    }

    public function hasFindings(DpiaRecord $dpiaRecord): bool
    {
        return $this->check($dpiaRecord) !== [];
    }

    /**
     * Bijzondere, strafrechtelijke en identificatienummers mogen in beginsel
     * niet worden verwerkt; zonder uitzonderingsgrond ontbreekt de
     * rechtvaardiging (paragraaf 12).
     *
     * @return array<int, DpiaQualityFinding>
     */
    private function checkPersonalData(DpiaRecord $dpiaRecord): array
    {
        $findings = [];

        foreach ($dpiaRecord->personalData as $personalData) {
            if (!$personalData->missesExceptionGround()) {
                continue;
            }

            $findings[] = new DpiaQualityFinding(
                'personal_data_without_exception_ground',
                '12',
                [
                    'gegeven' => $this->label($personalData),
                    'type' => $personalData->type?->label() ?? '',
                ],
            );
        }

        return $findings;
    }

    /**
     * Doorgifte buiten de EER vraagt om een doorgiftemechanisme (paragraaf 8).
     *
     * @return array<int, DpiaQualityFinding>
     */
    private function checkTransfers(DpiaRecord $dpiaRecord): array
    {
        if (!$dpiaRecord->outside_eea) {
            return [];
        }

        if ($this->filled($dpiaRecord->transfer_mechanism)) {
            return [];
        }

        return [new DpiaQualityFinding('transfer_without_mechanism', '8')];
    }

    /**
     * @return array<int, DpiaQualityFinding>
     */
    private function checkRisks(DpiaRecord $dpiaRecord): array
    {
        $findings = [];

        foreach ($dpiaRecord->risks as $risk) {
            // A risk nobody addresses is either an accepted risk that should
            // say so, or an oversight.
            if ($risk->measures->isEmpty()) {
                $findings[] = new DpiaQualityFinding(
                    'risk_without_measure',
                    '17',
                    ['risico' => $risk->label()],
                );
            }

            // The matrix is illustrative, so deviating is allowed -- but the
            // model asks for the reasoning, and that is exactly what makes the
            // deviation defensible.
            if (!$risk->deviatesFromMatrix() || $this->filled($risk->level_motivation)) {
                continue;
            }

            $findings[] = new DpiaQualityFinding(
                'risk_deviates_without_motivation',
                '16',
                [
                    'risico' => $this->label($risk),
                    'niveau' => $risk->suggestedLevel()?->label() ?? '',
                ],
            );
        }

        return $findings;
    }

    /**
     * @return array<int, DpiaQualityFinding>
     */
    private function checkMeasures(DpiaRecord $dpiaRecord): array
    {
        $findings = [];
        $hasHighResidualRisk = false;

        foreach ($dpiaRecord->measures as $measure) {
            if ($measure->risks->isEmpty()) {
                $findings[] = new DpiaQualityFinding(
                    'measure_without_risk',
                    '17',
                    ['maatregel' => $this->label($measure)],
                );
            }

            if ($measure->residual_level === RiskLevel::HIGH) {
                $hasHighResidualRisk = true;
            }
        }

        // Artikel 36 AVG: a high residual risk that cannot be reduced means the
        // AP has to be consulted before the processing starts.
        if ($hasHighResidualRisk && !$dpiaRecord->ap_consultation_required) {
            $findings[] = new DpiaQualityFinding('high_residual_risk_without_ap', '17');
        }

        if ($hasHighResidualRisk && !$this->filled($dpiaRecord->residual_risk_acceptance)) {
            $findings[] = new DpiaQualityFinding('high_residual_risk_without_acceptance', '17');
        }

        return $findings;
    }

    private function label(DpiaPersonalData|DpiaRisk|DpiaMeasure $model): string
    {
        $description = $model->description;

        if ($description === null || trim($description) === '') {
            return __('dpia_quality.unnamed');
        }

        $description = trim($description);

        return mb_strlen($description) > 60
            ? mb_substr($description, 0, 57) . '...'
            : $description;
    }

    private function filled(?string $value): bool
    {
        return $value !== null && trim($value) !== '';
    }
}
