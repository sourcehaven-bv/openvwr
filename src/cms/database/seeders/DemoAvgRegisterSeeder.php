<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CoreEntityDataCollectionSource;
use App\Enums\EntityNumberType;
use App\Enums\Snapshot\SnapshotApprovalLogMessageType;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Document;
use App\Models\FgRemark;
use App\Models\Organisation;
use App\Models\Remark;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\SnapshotApprovalLog;
use App\Models\Stakeholder;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\States\Snapshot\Obsolete;
use App\Models\User;
use App\Services\Snapshot\SnapshotDataFactory;
use Carbon\CarbonInterface;

use function count;
use function now;

/**
 * The AVG-verantwoordelijke register: the register a demo opens on, and the
 * only one carrying a full version history and approval trail.
 *
 * Split from DemoSeeder so that class stays about building an organisation
 * (users, lookup lists, shared entities) while the register content - records,
 * goals, stakeholders, remarks, versions and signatures - lives here.
 *
 * @phpstan-type AvgRecordDefinition array{name: string, service: string, goal: string, legal_base: string, retention: string, stakeholder: string, data_items: list<string>, special_data: bool, bsn: bool, systems: list<int>, processors: list<int>, receivers: list<int>, tags: list<int>, state: string, review_offset_months: int, dpia: bool, outside_eu: bool, description: string}
 */
final class DemoAvgRegisterSeeder
{
    use CreatesEntityNumbers;

    /**
     * @param array<string, User> $users
     */
    public function __construct(
        private readonly Organisation $organisation,
        private readonly DemoRelatedEntities $related,
        private readonly array $users,
        private readonly SnapshotDataFactory $snapshotDataFactory = new SnapshotDataFactory(),
    ) {
    }

    /**
     * @param list<Document> $documents
     */
    public function seed(array $documents, bool $isPrimary): void
    {
        $this->createAvgResponsibleRecords($this->organisation, $this->related, $documents, $this->users, $isPrimary);
    }

    /**
     * @param list<Document> $documents
     * @param array<string, User> $users
     */
    private function createAvgResponsibleRecords(
        Organisation $organisation,
        DemoRelatedEntities $related,
        array $documents,
        array $users,
        bool $isPrimary,
    ): void {
        // Secondary organisations get a shorter register: enough to prove the
        // tenant is populated, not so much that it competes for attention.
        $definitions = $isPrimary
            ? DemoContent::AVG_RECORDS
            : [DemoContent::AVG_RECORDS[0], DemoContent::AVG_RECORDS[2], DemoContent::AVG_RECORDS[4]];

        foreach ($definitions as $index => $definition) {
            $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->recycle($organisation)->create([
                'import_id' => null,
                'name' => $definition['name'],
                'data_collection_source' => CoreEntityDataCollectionSource::PRIMARY,
                'avg_responsible_processing_record_service_id' => $related->services[$definition['service']]->id,
                'entity_number_id' => $this->createEntityNumber($organisation, EntityNumberType::REGISTER),
                'responsibility_distribution' => $definition['description'],

                'has_pseudonymization' => $definition['special_data'],
                'pseudonymization' => $definition['special_data']
                    ? 'Gegevens worden gepseudonimiseerd voordat zij voor analyse worden gebruikt.'
                    : null,

                'outside_eu' => $definition['outside_eu'],
                'outside_eu_description' => $definition['outside_eu']
                    ? 'De leverancier verwerkt gegevens op servers in de Verenigde Staten.'
                    : null,
                'outside_eu_protection_level' => $definition['outside_eu'],
                'outside_eu_protection_level_description' => $definition['outside_eu']
                    ? 'Er zijn standaardcontractbepalingen (SCC\'s) afgesloten en er is een transfer '
                        . 'impact assessment uitgevoerd.'
                    : null,

                // No automated decision-making anywhere in the register: that
                // story belongs to the algorithm register, and claiming it here
                // too would invite the wrong question at the wrong moment.
                'decision_making' => false,
                'logic' => null,
                'importance_consequences' => null,

                'geb_dpia_executed' => $definition['dpia'],
                'geb_dpia_automated' => false,
                'geb_dpia_large_scale_processing' => $definition['special_data'],
                'geb_dpia_large_scale_monitoring' => $definition['name'] === 'Cameratoezicht toegangsbeveiliging',
                'geb_dpia_list_required' => $definition['dpia'],
                'geb_dpia_criteria_wp248' => $definition['dpia'],
                'geb_dpia_high_risk_freedoms' => $definition['special_data'],

                // Every demo record names at least one system; only the
                // processors list is genuinely empty for some (the inkoop
                // record is run entirely in-house).
                'has_processors' => $definition['processors'] !== [],
                'has_systems' => true,
                'has_security' => true,

                'review_at' => now()->addMonths($definition['review_offset_months'])->toDateString(),
                'public_from' => $definition['state'] === 'established' ? now()->subMonth() : null,
            ]);

            $this->attachRelated($record, $related, $definition);

            AvgGoal::factory()
                ->for($organisation)
                ->hasAttached($record)
                ->create([
                    'goal' => $definition['goal'],
                    'avg_goal_legal_base' => $definition['legal_base'],
                    'import_id' => null,
                    'remarks' => null,
                ]);

            $this->createStakeholder($organisation, $record, $definition);

            // Spread documents across records rather than attaching all of them
            // everywhere: the document register is more convincing when the
            // DPIA sits on the record it was written for.
            if ($documents !== []) {
                $record->documents()->attach($documents[$index % count($documents)]->id);
            }

            $this->createRemarks($record, $index, $users);
            $this->createSnapshots($organisation, $record, $definition, $users);
        }
    }

    /**
     * @param AvgRecordDefinition $definition
     */
    private function attachRelated(
        AvgResponsibleProcessingRecord $record,
        DemoRelatedEntities $related,
        array $definition,
    ): void {
        foreach ($definition['systems'] as $key) {
            $record->systems()->attach($related->systems[$key]->id);
        }

        foreach ($definition['processors'] as $key) {
            $record->processors()->attach($related->processors[$key]->id);
        }

        foreach ($definition['receivers'] as $key) {
            $record->receivers()->attach($related->receivers[$key]->id);
        }

        foreach ($definition['tags'] as $key) {
            $record->tags()->attach($related->tags[$key]->id);
        }

        $record->responsibles()->attach($related->responsible->id);

        foreach ($related->contactPersons as $contactPerson) {
            $record->contactPersons()->attach($contactPerson->id);
        }
    }

    /**
     * @param AvgRecordDefinition $definition
     */
    private function createStakeholder(
        Organisation $organisation,
        AvgResponsibleProcessingRecord $record,
        array $definition,
    ): void {
        $stakeholder = Stakeholder::factory()
            ->for($organisation)
            ->hasAttached($record)
            ->create([
                'description' => $definition['stakeholder'],

                'health' => $definition['special_data'],
                'biometric' => false,
                'faith_or_belief' => false,
                'genetic' => false,
                'political_attitude' => false,
                'race_or_ethnicity' => false,
                'sexual_life' => false,
                'trade_association_membership' => false,
                'criminal_law' => false,
                'special_collected_data_explanation' => $definition['special_data']
                    ? 'Gezondheidsgegevens worden uitsluitend verwerkt voor zover noodzakelijk voor '
                        . 'de verzuimbegeleiding en zijn alleen toegankelijk voor de arbodienst.'
                    : '',
                'citizen_service_numbers' => $definition['bsn'],
            ]);

        // A data item carries its own purpose, retention period and source, so
        // fill all of it: this is the level of detail an auditor asks about,
        // and an empty retention period is the first thing they would spot.
        foreach ($definition['data_items'] as $sort => $item) {
            $stakeholder->stakeholderDataItems()->create([
                'organisation_id' => $organisation->id,
                'import_id' => null,
                'description' => $item,
                'collection_purpose' => $definition['goal'],
                'retention_period' => $definition['retention'],
                'is_source_stakeholder' => true,
                'source_description' => 'De gegevens worden verstrekt door de betrokkene zelf.',
                'is_stakeholder_mandatory' => $definition['legal_base'] !== DemoContent::LEGAL_BASE_CONSENT,
                'stakeholder_consequences' => $definition['legal_base'] === DemoContent::LEGAL_BASE_CONSENT
                    ? 'Zonder toestemming vindt geen verwerking plaats; deelname is vrijwillig.'
                    : 'Zonder deze gegevens kan de verwerking niet worden uitgevoerd.',
                'sort' => $sort,
            ]);
        }
    }

    /**
     * @param array<string, User> $users
     */
    private function createRemarks(
        AvgResponsibleProcessingRecord $record,
        int $index,
        array $users,
    ): void {
        $author = $users['privacy-officer'];

        Remark::create([
            'remark_relatable_id' => $record->id,
            'remark_relatable_type' => $record::class,
            'user_id' => $author->id,
            'body' => DemoContent::REMARKS[$index % count(DemoContent::REMARKS)],
        ]);

        // FG remarks are visible only to the Functionaris Gegevensbescherming,
        // so only some records carry one — a note on every record would make
        // the role's view indistinguishable from everyone else's.
        if ($index % 3 !== 0) {
            return;
        }

        FgRemark::create([
            'fg_remark_relatable_id' => $record->id,
            'fg_remark_relatable_type' => $record::class,
            'body' => DemoContent::FG_REMARKS[$index % count(DemoContent::FG_REMARKS)],
        ]);
    }

    /**
     * Build the version history behind a record.
     *
     * The `state` in DemoContent describes where the *newest* version sits.
     * Anything past "draft" also gets a superseded established version, so the
     * versions tab shows a history rather than a single row — a register whose
     * every record has exactly one version does not look like one in use.
     *
     * @param AvgRecordDefinition $definition
     * @param array<string, User> $users
     */
    private function createSnapshots(
        Organisation $organisation,
        AvgResponsibleProcessingRecord $record,
        array $definition,
        array $users,
    ): void {
        if ($definition['state'] === 'draft') {
            // Never versioned: shows what a record looks like before it enters
            // the approval process at all.
            return;
        }

        $version = 1;

        if ($definition['state'] !== 'established' || $definition['review_offset_months'] < 12) {
            $this->createSnapshot($organisation, $record, $version, Obsolete::class, now()->subMonths(13));
            $version++;
        }

        $state = match ($definition['state']) {
            'in_review' => InReview::class,
            'approved' => Approved::class,
            'obsolete' => Obsolete::class,
            default => Established::class,
        };

        $snapshot = $this->createSnapshot(
            $organisation,
            $record,
            $version,
            $state,
            $definition['state'] === 'obsolete' ? now()->subMonths(2) : null,
        );

        $this->createApprovals($organisation, $snapshot, $definition, $users);
    }


    /**
     * @param array<string, mixed> $options
     */

    /**
     * Snapshot's $fillable covers only name, version and state: organisation_id,
     * snapshot_source_* and replaced_at are guarded and would be dropped
     * silently by create(). Force-fill them here so every snapshot in the demo
     * is complete, and so the omission cannot recur per call site.
     */
    private function makeSnapshot(
        Organisation $organisation,
        AvgResponsibleProcessingRecord $source,
        string $name,
        int $version,
        string $state,
        ?CarbonInterface $replacedAt = null,
    ): Snapshot {
        $snapshot = new Snapshot();

        $snapshot->forceFill([
            'organisation_id' => $organisation->id,
            'snapshot_source_id' => $source->id,
            'snapshot_source_type' => $source::class,
            'name' => $name,
            'version' => $version,
            'state' => $state,
            'replaced_at' => $replacedAt,
        ]);

        $snapshot->save();

        return $snapshot;
    }

    private function createSnapshot(
        Organisation $organisation,
        AvgResponsibleProcessingRecord $record,
        int $version,
        string $state,
        ?CarbonInterface $replacedAt = null,
    ): Snapshot {
        $snapshot = $this->makeSnapshot($organisation, $record, $record->name, $version, $state, $replacedAt);

        // Build the snapshot content with the same service the application
        // uses when a version is really created, rather than the faker-backed
        // SnapshotData factory. That factory writes lorem ipsum into
        // public_markdown, which surfaces verbatim on the version detail page
        // and on the public website - the two screens a demo is most likely to
        // put on a projector.
        $this->snapshotDataFactory->createDataForSnapshot($snapshot->refresh());

        return $snapshot;
    }

    /**
     * Approvals are where the goedkeuringsproces becomes visible, so all three
     * outcomes are represented across the register: signed off, still waiting,
     * and declined with a reason.
     *
     * @param AvgRecordDefinition $definition
     * @param array<string, User> $users
     */
    private function createApprovals(
        Organisation $organisation,
        Snapshot $snapshot,
        array $definition,
        array $users,
    ): void {
        $mandateHolder = $users['mandate-holder'];

        $status = match ($definition['state']) {
            // Approved but not yet established: this is the version sitting in
            // the mandate holder's inbox, and the one to open when showing the
            // "Akkoord geven" screen.
            'approved' => SnapshotApprovalStatus::UNKNOWN,
            'in_review' => SnapshotApprovalStatus::DECLINED,
            'established' => SnapshotApprovalStatus::APPROVED,
            default => null,
        };

        if ($status === null) {
            return;
        }

        $officer = $users['privacy-officer'];

        $approval = new SnapshotApproval();

        $approval->forceFill([
            'snapshot_id' => $snapshot->id,
            'requested_by' => $officer->id,
            'assigned_to' => $mandateHolder->id,
            'status' => $status,
            // An unsigned approval must look un-notified, otherwise the demo
            // account shows a task that the system believes it already chased.
            'notified_at' => $status === SnapshotApprovalStatus::UNKNOWN ? null : now()->subWeek(),
        ]);

        $approval->save();

        // The approval trail on the version page is built from these log
        // entries, and the decline reason lives here rather than on the
        // approval itself. Mirror the shape SnapshotApprovalService writes, so
        // the trail renders exactly as it would after a real sign-off.
        SnapshotApprovalLog::create([
            'snapshot_id' => $snapshot->id,
            'user_id' => $officer->id,
            'message' => [
                'type' => SnapshotApprovalLogMessageType::APPROVAL_REQUEST,
                'assigned_to' => $mandateHolder->logName,
            ],
        ]);

        if ($status === SnapshotApprovalStatus::UNKNOWN) {
            return;
        }

        SnapshotApprovalLog::create([
            'snapshot_id' => $snapshot->id,
            'user_id' => $mandateHolder->id,
            'message' => [
                'type' => SnapshotApprovalLogMessageType::APPROVAL_UPDATE,
                'assigned_to' => $mandateHolder->logName,
                'status' => $status->value,
                'notes' => $status === SnapshotApprovalStatus::DECLINED ? DemoContent::DECLINE_REASON : null,
            ],
        ]);
    }
}
