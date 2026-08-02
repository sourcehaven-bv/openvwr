<?php

declare(strict_types=1);

namespace App\Services\Dpia;

use App\Models\Dpia\DpiaMeasure;
use App\Models\Dpia\DpiaRecord;
use App\Models\Dpia\DpiaRisk;

use function is_array;
use function is_string;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * Writes the "welke maatregel pakt welk risico aan" links from the form state
 * into the dpia_measure_risk pivot.
 *
 * This runs after saving rather than from the checkbox list itself. Inside a
 * relationship-backed Repeater the nested checkbox list works on the array
 * state of the repeater: a risk that already existed is keyed "record-<uuid>",
 * while a risk added in the same session has no database id yet. Only once the
 * parent save has run do all risks have ids, so that is the moment the links
 * can be resolved reliably.
 */
final class DpiaMeasureRiskLinker
{
    /**
     * Prefix Filament gives repeater items that are backed by a saved record.
     */
    private const RECORD_KEY_PREFIX = 'record-';

    /**
     * The repeater state key a saved record appears under.
     *
     * Exposed so the measures repeater can hydrate the risk checkboxes in the
     * same keys this class reads back, without repeating the prefix.
     */
    public static function stateKeyFor(string $id): string
    {
        return self::RECORD_KEY_PREFIX . $id;
    }

    /**
     * @param array<mixed> $formData
     */
    public function link(DpiaRecord $dpiaRecord, array $formData): void
    {
        if (!is_array($formData['measures'] ?? null)) {
            return;
        }

        $risks = $dpiaRecord->risks()->get();
        $measures = $dpiaRecord->measures()->get();

        /** @var array<string, string> $riskIdByKey */
        $riskIdByKey = $this->riskIdByStateKey($formData, $risks);

        foreach ($formData['measures'] as $measureKey => $measureState) {
            if (!is_array($measureState)) {
                continue;
            }

            $measure = $this->resolveMeasure($measureKey, $measureState, $measures);

            if (!$measure instanceof DpiaMeasure) {
                continue;
            }

            $measure->risks()->sync($this->selectedRiskIds($measureState, $riskIdByKey));
        }
    }

    /**
     * Maps each risk key in the form state onto the id that risk now has.
     *
     * A key of the form "record-<uuid>" already carries its id. A risk added in
     * this session does not, and is matched on its title -- the only thing that
     * identifies it across the save. Titles are required, so a blank one
     * cannot match.
     *
     * @param array<mixed> $formData
     * @param iterable<int, DpiaRisk> $risks
     *
     * @return array<string, string>
     */
    private function riskIdByStateKey(array $formData, iterable $risks): array
    {
        $risksState = $formData['risks'] ?? null;

        if (!is_array($risksState)) {
            return [];
        }

        $idByDescription = self::riskIdsByDescription($risks);
        $riskIdByKey = [];

        foreach ($risksState as $riskKey => $riskState) {
            if (!is_string($riskKey)) {
                continue;
            }

            $recordId = $this->recordIdFromKey($riskKey);

            if ($recordId !== null) {
                $riskIdByKey[$riskKey] = $recordId;

                continue;
            }

            $description = is_array($riskState) ? ($riskState['title'] ?? null) : null;

            if (is_string($description) && isset($idByDescription[$description])) {
                $riskIdByKey[$riskKey] = $idByDescription[$description];
            }
        }

        return $riskIdByKey;
    }

    /**
     * Saved risks keyed by description, used to match a risk that was added in
     * the same session and therefore has no id in the form state yet.
     *
     * @param iterable<int, DpiaRisk> $risks
     *
     * @return array<string, string>
     */
    private static function riskIdsByDescription(iterable $risks): array
    {
        $idByDescription = [];

        foreach ($risks as $risk) {
            $title = $risk->title;

            if (is_string($title) && $title !== '') {
                $idByDescription[$title] = $risk->id->toString();
            }
        }

        return $idByDescription;
    }

    /**
     * @param array<mixed> $measureState
     * @param iterable<int, DpiaMeasure> $measures
     */
    private function resolveMeasure(mixed $measureKey, array $measureState, iterable $measures): ?DpiaMeasure
    {
        $measureId = $this->recordIdFromKey($measureKey);
        $description = $measureState['description'] ?? null;

        foreach ($measures as $measure) {
            if ($measureId !== null && $measure->id->toString() === $measureId) {
                return $measure;
            }

            if ($measureId === null && is_string($description) && $measure->description === $description) {
                return $measure;
            }
        }

        return null;
    }

    /**
     * @param array<mixed> $measureState
     * @param array<string, string> $riskIdByKey
     *
     * @return array<int, string>
     */
    private function selectedRiskIds(array $measureState, array $riskIdByKey): array
    {
        $selected = $measureState['risks'] ?? null;

        if (!is_array($selected)) {
            return [];
        }

        $ids = [];

        foreach ($selected as $riskKey) {
            if (!is_string($riskKey)) {
                continue;
            }

            $id = $this->recordIdFromKey($riskKey) ?? ($riskIdByKey[$riskKey] ?? null);

            if ($id === null) {
                continue;
            }

            $ids[] = $id;
        }

        return $ids;
    }

    private function recordIdFromKey(mixed $key): ?string
    {
        if (!is_string($key) || !str_starts_with($key, self::RECORD_KEY_PREFIX)) {
            return null;
        }

        return substr($key, strlen(self::RECORD_KEY_PREFIX));
    }
}
