<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CoreEntityDataCollectionSource;
use App\Enums\EntityNumberType;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\Organisation;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\Wpg\WpgProcessingRecord;
use App\Services\Snapshot\SnapshotDataFactory;
use Carbon\CarbonInterface;

use function count;
use function now;

/**
 * The registers beyond AVG-verantwoordelijke: processor-side processings, WPG,
 * algorithms and data breaches.
 *
 * Split from DemoSeeder because the two answer different questions. DemoSeeder
 * builds an organisation's foundation - users, lookup lists, shared entities,
 * and the main register with its version history. This class fills the
 * remaining registers on top of that foundation, and only for the primary demo
 * organisation.
 */
final class DemoRegisterSeeder
{
    use CreatesEntityNumbers;

    public function __construct(
        private readonly Organisation $organisation,
        private readonly DemoLookups $lookups,
        private readonly DemoRelatedEntities $related,
        private readonly SnapshotDataFactory $snapshotDataFactory = new SnapshotDataFactory(),
    ) {
    }

    /**
     * @param list<Document> $documents
     */
    public function seed(array $documents): void
    {
        $this->createAvgProcessorRecords($this->organisation, $this->related);
        $this->createWpgRecords($this->organisation);
        $this->createAlgorithmRecords($this->organisation, $this->lookups, $documents);
        $this->createDataBreachRecords($this->organisation, $this->related);
    }

    private function createAvgProcessorRecords(Organisation $organisation, DemoRelatedEntities $related): void
    {
        foreach (DemoContent::AVG_PROCESSOR_RECORDS as $definition) {
            $record = AvgProcessorProcessingRecord::factory()
                ->for($organisation)
                ->recycle($organisation)
                ->create([
                    'import_id' => null,
                    'name' => $definition['name'],
                    'entity_number_id' => $this->createEntityNumber($organisation, EntityNumberType::REGISTER),
                    'responsibility_distribution' => $definition['description'],
                    'outside_eu' => false,
                    'decision_making' => false,
                    // Due inside the three-month window, so the "verloopt
                    // binnenkort" filter has something to find.
                    'review_at' => now()->addMonths(2)->toDateString(),
                ]);

            $record->responsibles()->attach($related->responsible->id);

            $snapshot = $this->makeSnapshot(
                $organisation,
                $record,
                $record->name,
                1,
                $definition['state'] === 'established' ? Established::class : InReview::class,
            );

            $this->snapshotDataFactory->createDataForSnapshot($snapshot->refresh());
        }
    }

    private function createWpgRecords(Organisation $organisation): void
    {
        foreach (DemoContent::WPG_RECORDS as $definition) {
            $record = WpgProcessingRecord::factory()->for($organisation)->recycle($organisation)->create([
                'import_id' => null,
                'name' => $definition['name'],
                'data_collection_source' => CoreEntityDataCollectionSource::PRIMARY,
                'entity_number_id' => $this->createEntityNumber($organisation, EntityNumberType::REGISTER),

                'suspects' => true,
                'victims' => false,
                'convicts' => false,
                'police_justice' => true,
                'third_parties' => false,
                'third_party_explanation' => null,

                'pseudonymization' => null,
                'has_security' => true,
                'has_processors' => false,

                'decision_making' => false,
                'logic' => null,
                'consequences' => null,

                'explanation_available' => $definition['description'],
                'explanation_provisioning' => 'Verstrekking vindt uitsluitend plaats aan daartoe '
                    . 'bevoegde instanties op grond van de Wet politiegegevens.',
                'explanation_transfer' => 'Er vindt geen doorgifte plaats naar landen buiten de EER.',

                // Overdue on purpose: puts a Wpg record on the dashboard's
                // overdue list, so it shows more than one register type.
                'review_at' => now()->subMonths(3)->toDateString(),
            ]);

            $snapshot = $this->makeSnapshot(
                $organisation,
                $record,
                $record->name,
                1,
                $definition['state'] === 'established' ? Established::class : InReview::class,
            );

            $this->snapshotDataFactory->createDataForSnapshot($snapshot->refresh());
        }
    }

    /**
     * @param list<Document> $documents
     */
    private function createAlgorithmRecords(Organisation $organisation, DemoLookups $lookups, array $documents): void
    {
        foreach (DemoContent::ALGORITHM_RECORDS as $index => $definition) {
            $record = AlgorithmRecord::factory()->for($organisation)->recycle($organisation)->create([
                'import_id' => null,
                'name' => $definition['name'],
                'description' => $definition['description'],
                'entity_number_id' => $this->createEntityNumber($organisation, EntityNumberType::REGISTER),

                'algorithm_theme_id' => $lookups->algorithmThemes[$definition['theme']]->id,
                'algorithm_status_id' => $lookups->algorithmStatuses[$definition['status']]->id,
                'algorithm_publication_category_id' => $lookups->algorithmCategories[$definition['category']]->id,

                'start_date' => now()->subYear(),
                'end_date' => null,
                'contact_data' => 'privacy@example.com',
                'source_link' => null,
                'public_page_link' => null,
                'public_from' => $definition['published'] ? now()->subMonth() : null,

                'resp_goal_and_impact' => $definition['goal_and_impact'],
                'resp_considerations' => $definition['considerations'],
                'resp_human_intervention' => $definition['human_intervention'],
                'resp_risk_analysis' => $definition['risk_analysis'],
                'resp_legal_base_title' => 'Algemene wet bestuursrecht',
                'resp_legal_base' => 'De beoordeling van aanvragen vindt plaats op grond van de '
                    . 'toepasselijke regelgeving en de daarop gebaseerde beleidsregels.',
                'resp_legal_base_link' => null,
                'resp_processor_registry_link' => null,
                'resp_impact_tests' => $definition['published']
                    ? 'DPIA en IAMA uitgevoerd en vastgesteld.'
                    : 'DPIA in concept.',
                'resp_impact_test_links' => null,
                'resp_impact_tests_description' => null,

                'oper_data_title' => 'Gebruikte gegevens',
                'oper_data' => 'Het model gebruikt uitsluitend gegevens die de aanvrager zelf heeft '
                    . 'verstrekt in het aanvraagformulier.',
                'oper_links' => null,
                'oper_technical_operation' => 'Het model past een vaste set beslisregels toe en kent '
                    . 'op basis daarvan een prioriteit toe.',
                'oper_supplier' => $definition['supplier'],
                'oper_source_code_link' => null,

                'meta_national_id' => null,
                'meta_source_id' => null,
                'meta_tags' => null,
                'meta_date_of_development' => now()->subYears(2),
                'meta_owner_algorithm' => 'Directie Informatievoorziening',
                'meta_product_owner_algorithm' => 'Joost Bakker',

                'impact_with_consequences' => false,
                'impact_more_algorithms_applied' => false,
                'impact_effect_on_outcome' => false,
                'validation_answers_checked_by_product_owner' => $definition['published'],
            ]);

            if ($documents !== []) {
                $record->documents()->attach($documents[$index % count($documents)]->id);
            }

            if ($definition['state'] === 'draft') {
                continue;
            }

            $snapshot = $this->makeSnapshot($organisation, $record, $record->name, 1, Established::class);

            $this->snapshotDataFactory->createDataForSnapshot($snapshot->refresh());
        }
    }

    private function createDataBreachRecords(Organisation $organisation, DemoRelatedEntities $related): void
    {
        foreach (DemoContent::DATA_BREACH_RECORDS as $definition) {
            $discoveredAt = now()->subDays($definition['discovered_offset_days']);

            $record = DataBreachRecord::factory()->for($organisation)->recycle($organisation)->create([
                'entity_number_id' => $this->createEntityNumber($organisation, EntityNumberType::DATABREACH),

                'name' => $definition['name'],
                'type' => $definition['type'],
                'summary' => $definition['summary'],
                'involved_people' => $definition['involved_people'],
                'estimated_risk' => $definition['estimated_risk'],
                'measures' => $definition['measures'],

                'discovered_at' => $discoveredAt->toDateString(),
                'started_at' => $discoveredAt->copy()->subDays(1)->toDateString(),
                'ended_at' => $definition['closed'] ? $discoveredAt->copy()->addDays(1)->toDateString() : null,

                'reported_at' => $discoveredAt->copy()->addDay()->toDateString(),
                'ap_reported' => $definition['ap_reported'],
                'ap_reported_at' => $definition['ap_reported']
                    ? $discoveredAt->copy()->addDays(2)->toDateString()
                    : null,
                'fg_reported' => $definition['fg_reported'],
                'reported_to_involved' => $definition['reported_to_involved'],

                'completed_at' => $definition['closed']
                    ? $discoveredAt->copy()->addDays(10)->toDateString()
                    : null,
            ]);

            $record->responsibles()->attach($related->responsible->id);
        }
    }

    /**
     * Snapshot's $fillable covers only name, version and state; the rest is
     * guarded and would be dropped silently by create(). Mirrors the helper in
     * DemoSeeder so both classes produce identical, complete snapshots.
     */
    private function makeSnapshot(
        Organisation $organisation,
        AvgProcessorProcessingRecord|WpgProcessingRecord|AlgorithmRecord $source,
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
}
