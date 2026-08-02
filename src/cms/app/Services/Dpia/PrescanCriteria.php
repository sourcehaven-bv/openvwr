<?php

declare(strict_types=1);

namespace App\Services\Dpia;

use function __;

/**
 * The two external criteria lists the pre-scan checks against.
 *
 * AP: "Besluit lijst verwerkingen persoonsgegevens waarvoor een
 * gegevensbeschermingseffectbeoordeling (DPIA) verplicht is" (Stcrt. 27
 * november 2019), as summarised in paragraaf 1.2 of the Rijksmodel.
 *
 * EDPB: the criteria from Richtsnoeren WP248 (4 april 2017), also listed in
 * paragraaf 1.2.
 *
 * Only the keys are stored on the record, so the wording can be updated without
 * a migration. Keys must stay stable once used.
 *
 * Each criterion has a short label and a longer description. Both come from the
 * same source text; splitting them keeps the list scannable while leaving the
 * full wording in view, which matters because ticking a single AP-criterion
 * already makes a DPIA mandatory.
 */
final class PrescanCriteria
{
    /**
     * Criteria from the AP list. One hit already makes a DPIA mandatory.
     */
    public const AP = [
        'heimelijk_onderzoek',
        'zwarte_lijsten',
        'fraudebestrijding',
        'financiele_situatie',
        'samenwerkingsverbanden',
        'cameratoezicht',
        'controle_werknemers',
        'locatiegegevens',
        'communicatiegegevens',
        'profilering',
        'observatie_gedrag',
        'biometrische_gegevens',
        'genetische_gegevens',
        'gezondheidsgegevens',
        'creditscores',
        'flexibel_cameratoezicht',
        'internet_of_things',
    ];

    /**
     * Criteria from the EDPB list. Two hits make a DPIA mandatory, one hit
     * means it has to be assessed whether a high risk exists.
     */
    public const EDPB = [
        'beoordelen_persoonskenmerken',
        'geautomatiseerde_besluitvorming',
        'stelselmatige_monitoring',
        'bijzondere_gegevens',
        'grootschalige_verwerking',
        'kwetsbare_personen',
        'innovatieve_technologie',
        'blokkering_recht_dienst',
        'gekoppelde_datasets',
    ];

    /**
     * The hoog-risico categories of bijlage III bij de AI-verordening, as
     * referenced by artikel 27. Recognising a category is far easier than
     * reading the article, so the pre-scan asks about these instead.
     */
    public const HIGH_RISK_AI = [
        'biometrie',
        'kritieke_infrastructuur',
        'onderwijs',
        'werkgelegenheid',
        'essentiele_diensten',
        'rechtshandhaving',
        'migratie_asiel',
        'rechtsbedeling',
    ];

    /**
     * @return array<string, string>
     */
    public static function highRiskAiOptions(): array
    {
        return self::labelsFor(self::HIGH_RISK_AI, 'high_risk_ai_');
    }

    /**
     * @return array<string, string>
     */
    public static function highRiskAiDescriptions(): array
    {
        return self::descriptionsFor(self::HIGH_RISK_AI, 'high_risk_ai_');
    }

    /**
     * @return array<string, string>
     */
    public static function apOptions(): array
    {
        return self::labelsFor(self::AP, 'ap_criterion_');
    }

    /**
     * @return array<string, string>
     */
    public static function apDescriptions(): array
    {
        return self::descriptionsFor(self::AP, 'ap_criterion_');
    }

    /**
     * @return array<string, string>
     */
    public static function edpbOptions(): array
    {
        return self::labelsFor(self::EDPB, 'edpb_criterion_');
    }

    /**
     * @return array<string, string>
     */
    public static function edpbDescriptions(): array
    {
        return self::descriptionsFor(self::EDPB, 'edpb_criterion_');
    }

    /**
     * @param array<int, string> $keys
     *
     * @return array<string, string>
     */
    private static function labelsFor(array $keys, string $prefix): array
    {
        $labels = [];

        foreach ($keys as $key) {
            $labels[$key] = __('dpia_prescan_record.' . $prefix . $key);
        }

        return $labels;
    }

    /**
     * @param array<int, string> $keys
     *
     * @return array<string, string>
     */
    private static function descriptionsFor(array $keys, string $prefix): array
    {
        $descriptions = [];

        foreach ($keys as $key) {
            $descriptions[$key] = __('dpia_prescan_record.' . $prefix . $key . '_description');
        }

        return $descriptions;
    }
}
