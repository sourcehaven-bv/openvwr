<?php

declare(strict_types=1);

namespace App\Services\ApReport;

use App\Facades\DateFormat;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\DataBreachRecord;
use App\Models\Stakeholder;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

use function __;
use function array_keys;
use function array_merge;
use function array_unique;
use function array_values;
use function sprintf;
use function trim;

/**
 * Builds the preparation for the AP notification form from a data breach record
 * and the content it is linked to.
 *
 * Values taken from a linked processing record describe that processing, not
 * necessarily this breach, so they are marked as derived: the officer confirms
 * them against what actually leaked before filing. See AnswerSource.
 */
class ApReportBuilder
{
    /**
     * The special categories a stakeholder can carry, mapped onto the wording of
     * question 6.2 of the AP form so a confirmed suggestion can be ticked
     * straight across into the online form.
     */
    private const SPECIAL_CATEGORY_QUESTIONS = [
        'race_or_ethnicity' => 'Persoonsgegevens waaruit iemands ras of etnische afkomst blijkt',
        'political_attitude' => 'Persoonsgegevens waaruit iemands politieke opvattingen blijken',
        'faith_or_belief' => 'Persoonsgegevens waaruit iemands religieuze of levensbeschouwelijke overtuigingen blijken',
        'trade_association_membership' => 'Persoonsgegevens waaruit iemands lidmaatschap van een vakbond blijkt',
        'sexual_life' => 'Gegevens met betrekking tot iemands seksueel gedrag of seksuele gerichtheid',
        'health' => 'Gegevens over iemands gezondheid',
        'genetic' => 'Genetische gegevens',
        'biometric' => 'Biometrische gegevens (bijvoorbeeld: vingerafdruk of irisscan)',
    ];

    /**
     * Categories from question 6.1 that the register tracks on the stakeholder
     * rather than on the breach.
     */
    private const GENERAL_CATEGORY_QUESTIONS = [
        'citizen_service_numbers' => 'Burgerservicenummer (BSN)',
        'criminal_law' => 'Persoonsgegevens betreffende strafrechtelijke veroordelingen en strafbare feiten'
            . ' of daarmee verband houdende veiligheidsmaatregelen',
    ];

    public function build(DataBreachRecord $dataBreachRecord): ApReport
    {
        return new ApReport($dataBreachRecord, [
            $this->introduction($dataBreachRecord),
            $this->international(),
            $this->controller($dataBreachRecord),
            $this->timeline($dataBreachRecord),
            $this->breach($dataBreachRecord),
            $this->personalData($dataBreachRecord),
            $this->affectedPeople($dataBreachRecord),
            $this->priorMeasures($dataBreachRecord),
            $this->consequences($dataBreachRecord),
            $this->followUp($dataBreachRecord),
        ]);
    }

    private function introduction(DataBreachRecord $dataBreachRecord): ApChapter
    {
        $regimes = [];
        $origins = [];

        // Which register a linked processing sits in tells us which notification
        // duty applies: the WPG register implies the Wpg, the AVG registers the AVG.
        foreach ($dataBreachRecord->wpgProcessingRecords as $wpgProcessingRecord) {
            $regimes[] = 'Wet politiegegevens (Wpg)';
            $origins[] = $this->originLabel($wpgProcessingRecord);
        }

        foreach ($dataBreachRecord->avgResponsibleProcessingRecords as $processingRecord) {
            $regimes[] = 'Algemene verordening gegevensbescherming (AVG)';
            $origins[] = $this->originLabel($processingRecord);
        }

        foreach ($dataBreachRecord->avgProcessorProcessingRecords as $processingRecord) {
            $regimes[] = 'Algemene verordening gegevensbescherming (AVG)';
            $origins[] = $this->originLabel($processingRecord);
        }

        return new ApChapter('1', __('ap_report.chapter.introduction'), [
            ApAnswer::recorded('1.1', __('ap_report.question.notification_kind'), 'Ik wil één inbreuk melden (reguliere melding)'),
            ApAnswer::derived(
                '1.2',
                __('ap_report.question.legal_basis'),
                array_values(array_unique($regimes)),
                array_values(array_unique($origins)),
            ),
            ApAnswer::missing('1.3', __('ap_report.question.other_supervisors')),
        ]);
    }

    private function international(): ApChapter
    {
        // Cross-border reach is a property of the incident, not of the processing:
        // a processing that may involve other countries says nothing about whether
        // the people hit by this breach live there.
        return new ApChapter('2', __('ap_report.chapter.international'), [
            ApAnswer::missing('2.1.1', __('ap_report.question.cross_border')),
            ApAnswer::missing('2.2.1', __('ap_report.question.reported_other_dpas')),
        ]);
    }

    private function controller(DataBreachRecord $dataBreachRecord): ApChapter
    {
        $organisation = $dataBreachRecord->organisation;

        $names = [];
        $addresses = [];
        foreach ($dataBreachRecord->responsibles as $responsible) {
            $names[] = $responsible->name;

            $address = $responsible->address;
            if ($address === null) {
                continue;
            }

            $addresses[] = trim(sprintf(
                '%s, %s %s',
                (string) $address->address,
                (string) $address->postal_code,
                (string) $address->city,
            ), ' ,');
        }

        return new ApChapter('3', __('ap_report.chapter.controller'), [
            ApAnswer::recorded('3.1.1a', __('ap_report.question.organisation_name'), $organisation->responsibleLegalEntity->name),
            // The responsibles hang off the breach record itself, so they state
            // something about this breach rather than about a processing.
            ApAnswer::recorded('3.1.1b', __('ap_report.question.responsible'), $names),
            ApAnswer::recorded('3.1.1c', __('ap_report.question.address'), $addresses),
            ApAnswer::missing('3.1.1d', __('ap_report.question.fg_registration_number')),
            ApAnswer::missing('3.1.1e', __('ap_report.question.coc_number')),
            ApAnswer::missing('3.1.2', __('ap_report.question.sector')),
            ApAnswer::missing('3.2.1', __('ap_report.question.reporter')),
            ApAnswer::missing('3.2.2', __('ap_report.question.contact_person')),
            ApAnswer::derived(
                '3.3',
                __('ap_report.question.other_organisations'),
                $this->involvedOrganisations($dataBreachRecord),
                $this->processingOrigins($dataBreachRecord),
            ),
        ]);
    }

    private function timeline(DataBreachRecord $dataBreachRecord): ApChapter
    {
        return new ApChapter('4', __('ap_report.chapter.timeline'), [
            ApAnswer::recorded('4.1.1', __('ap_report.question.started_at'), $this->date($dataBreachRecord->started_at)),
            ApAnswer::recorded('4.1.2', __('ap_report.question.ended_at'), $this->date($dataBreachRecord->ended_at)),
            ApAnswer::recorded('4.2', __('ap_report.question.discovered_at'), $this->date($dataBreachRecord->discovered_at)),
            ApAnswer::missing('4.3', __('ap_report.question.how_discovered')),
            ApAnswer::missing('4.5', __('ap_report.question.late_notification_reason')),
        ]);
    }

    private function breach(DataBreachRecord $dataBreachRecord): ApChapter
    {
        $natureOfIncident = $dataBreachRecord->nature_of_incident;
        if ($natureOfIncident === 'Overig' && $dataBreachRecord->nature_of_incident_other !== null) {
            $natureOfIncident = sprintf('Overig, namelijk: %s', $dataBreachRecord->nature_of_incident_other);
        }

        $documents = [];
        foreach ($dataBreachRecord->documents as $document) {
            $documents[] = $document->name;
        }

        return new ApChapter('5', __('ap_report.chapter.breach'), [
            ApAnswer::missing('5.1', __('ap_report.question.nature_of_breach')),
            ApAnswer::recorded('5.2', __('ap_report.question.nature_of_incident'), $natureOfIncident),
            ApAnswer::recorded('5.3', __('ap_report.question.summary'), $dataBreachRecord->summary),
            ApAnswer::recorded('5.4', __('ap_report.question.attachments'), $documents),
        ]);
    }

    private function personalData(DataBreachRecord $dataBreachRecord): ApChapter
    {
        $categories = $dataBreachRecord->personal_data_categories ?? [];
        if ($dataBreachRecord->personal_data_categories_other !== null) {
            $categories[] = sprintf('Anders, namelijk: %s', $dataBreachRecord->personal_data_categories_other);
        }

        $derivedGeneral = $this->stakeholderCategories($dataBreachRecord, self::GENERAL_CATEGORY_QUESTIONS);
        $derivedSpecial = $this->stakeholderCategories($dataBreachRecord, self::SPECIAL_CATEGORY_QUESTIONS);
        $origins = $this->processingOrigins($dataBreachRecord);

        // What the breach itself states about special categories outranks what the
        // linked processing suggests: the register describes this incident, the
        // processing only describes what it may involve.
        $recordedSpecial = $dataBreachRecord->personal_data_special_categories ?? [];
        $specialCategories = $recordedSpecial === []
            ? ApAnswer::derived('6.2', __('ap_report.question.special_categories'), $derivedSpecial, $origins)
            : ApAnswer::recorded('6.2', __('ap_report.question.special_categories'), array_values($recordedSpecial));

        return new ApChapter('6', __('ap_report.chapter.personal_data'), [
            ApAnswer::recorded('6.1', __('ap_report.question.personal_data_categories'), array_values($categories)),
            ApAnswer::derived(
                '6.1b',
                __('ap_report.question.personal_data_categories_from_processing'),
                $derivedGeneral,
                $origins,
            ),
            $specialCategories,
            ApAnswer::missing('6.3.1', __('ap_report.question.record_count')),
        ]);
    }

    private function affectedPeople(DataBreachRecord $dataBreachRecord): ApChapter
    {
        $descriptions = [];
        foreach ($this->stakeholders($dataBreachRecord) as $stakeholder) {
            if ($stakeholder->description === null) {
                continue;
            }

            $descriptions[] = $stakeholder->description;
        }

        return new ApChapter('7', __('ap_report.chapter.affected_people'), [
            ApAnswer::missing('7.1', __('ap_report.question.affected_groups')),
            ApAnswer::recorded('7.2', __('ap_report.question.affected_description'), $dataBreachRecord->involved_people),
            ApAnswer::derived(
                '7.2b',
                __('ap_report.question.stakeholders_from_processing'),
                $descriptions,
                $this->processingOrigins($dataBreachRecord),
            ),
            ApAnswer::missing('7.3', __('ap_report.question.affected_count')),
        ]);
    }

    private function priorMeasures(DataBreachRecord $dataBreachRecord): ApChapter
    {
        $pseudonymisation = [];
        foreach ($this->avgProcessingRecords($dataBreachRecord) as $processingRecord) {
            if (!$processingRecord instanceof AvgResponsibleProcessingRecord) {
                continue;
            }

            if ($processingRecord->pseudonymization === null) {
                continue;
            }

            $pseudonymisation[] = $processingRecord->pseudonymization;
        }

        return new ApChapter('8', __('ap_report.chapter.prior_measures'), [
            // Question 8.1 asks whether the data were unreadable to outsiders.
            // Pseudonymisation recorded on a processing is related but not the
            // same thing, so it is offered as context rather than as the answer.
            ApAnswer::missing('8.1', __('ap_report.question.encrypted_beforehand')),
            ApAnswer::derived(
                '8.1b',
                __('ap_report.question.pseudonymisation_from_processing'),
                $pseudonymisation,
                $this->processingOrigins($dataBreachRecord),
            ),
        ]);
    }

    private function consequences(DataBreachRecord $dataBreachRecord): ApChapter
    {
        return new ApChapter('9', __('ap_report.chapter.consequences'), [
            ApAnswer::missing('9.1', __('ap_report.question.consequences_controller')),
            ApAnswer::missing('9.2', __('ap_report.question.consequences_data_subjects')),
            // The AP wants one of four severity levels; the register holds free
            // text, which is useful to base the choice on but is not the choice.
            ApAnswer::missing('9.3', __('ap_report.question.risk_severity')),
            ApAnswer::recorded('9.3b', __('ap_report.question.estimated_risk'), $dataBreachRecord->estimated_risk),
        ]);
    }

    private function followUp(DataBreachRecord $dataBreachRecord): ApChapter
    {
        $communication = $dataBreachRecord->reported_to_involved_communication ?? [];
        if ($dataBreachRecord->reported_to_involved_communication_other !== null) {
            $communication[] = sprintf('Anders, namelijk: %s', $dataBreachRecord->reported_to_involved_communication_other);
        }

        return new ApChapter('10', __('ap_report.chapter.follow_up'), [
            ApAnswer::recorded(
                '10.1.1',
                __('ap_report.question.reported_to_involved'),
                $this->boolean($dataBreachRecord->reported_to_involved),
            ),
            ApAnswer::recorded('10.1.7', __('ap_report.question.reported_to_involved_communication'), array_values($communication)),
            ApAnswer::missing('10.1.3', __('ap_report.question.reported_to_involved_count')),
            ApAnswer::recorded('10.2', __('ap_report.question.measures'), $dataBreachRecord->measures),
        ]);
    }

    /**
     * @param array<string, string> $questions
     *
     * @return array<int, string>
     */
    private function stakeholderCategories(DataBreachRecord $dataBreachRecord, array $questions): array
    {
        $categories = [];
        foreach ($this->stakeholders($dataBreachRecord) as $stakeholder) {
            foreach (array_keys($questions) as $attribute) {
                if ($stakeholder->getAttribute($attribute) !== true) {
                    continue;
                }

                $categories[] = $questions[$attribute];
            }
        }

        return array_values(array_unique($categories));
    }

    /**
     * The stakeholders of every AVG processing this breach is linked to. WPG
     * records carry no stakeholders, so they contribute nothing here.
     *
     * @return array<int, Stakeholder>
     */
    private function stakeholders(DataBreachRecord $dataBreachRecord): array
    {
        $stakeholders = [];
        foreach ($this->avgProcessingRecords($dataBreachRecord) as $processingRecord) {
            foreach ($processingRecord->stakeholders as $stakeholder) {
                $stakeholders[$stakeholder->id->toString()] = $stakeholder;
            }
        }

        return array_values($stakeholders);
    }

    /**
     * @return array<int, AvgProcessorProcessingRecord|AvgResponsibleProcessingRecord>
     */
    private function avgProcessingRecords(DataBreachRecord $dataBreachRecord): array
    {
        return array_values(array_merge(
            $dataBreachRecord->avgResponsibleProcessingRecords->all(),
            $dataBreachRecord->avgProcessorProcessingRecords->all(),
        ));
    }

    /**
     * @return array<int, string>
     */
    private function involvedOrganisations(DataBreachRecord $dataBreachRecord): array
    {
        $organisations = [];
        foreach ($this->avgProcessingRecords($dataBreachRecord) as $processingRecord) {
            foreach ($processingRecord->processors as $processor) {
                $organisations[] = sprintf('%s (verwerker)', $processor->name);
            }
        }

        foreach ($dataBreachRecord->avgResponsibleProcessingRecords as $processingRecord) {
            foreach ($processingRecord->receivers as $receiver) {
                if ($receiver->description === null) {
                    continue;
                }

                $organisations[] = sprintf('%s (ontvanger)', $receiver->description);
            }
        }

        return array_values(array_unique($organisations));
    }

    /**
     * @return array<int, string>
     */
    private function processingOrigins(DataBreachRecord $dataBreachRecord): array
    {
        $origins = [];
        foreach ($this->avgProcessingRecords($dataBreachRecord) as $processingRecord) {
            $origins[] = $this->originLabel($processingRecord);
        }

        foreach ($dataBreachRecord->wpgProcessingRecords as $processingRecord) {
            $origins[] = $this->originLabel($processingRecord);
        }

        return array_values(array_unique($origins));
    }

    private function originLabel(Model $model): string
    {
        /** @var string $name */
        $name = $model->getAttribute('name');

        return $name;
    }

    private function date(?CarbonImmutable $date): ?string
    {
        if ($date === null) {
            return null;
        }

        return DateFormat::toDate($date);
    }

    private function boolean(bool $value): string
    {
        return $value ? __('general.yes') : __('general.no');
    }
}
