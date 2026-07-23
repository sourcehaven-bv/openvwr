<?php

declare(strict_types=1);

use App\Enums\CoreEntityDataCollectionSource;
use App\Models\Address;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\EntityNumber;
use App\Models\Organisation;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Established;
use App\Models\Wpg\WpgProcessingRecord;
use App\Services\Snapshot\SnapshotDataFactory;
use App\ValueObjects\CalendarDate;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\App;

it('creates new snapshot data for a AvgResponsibleProcessingRecord', function (): void {
    $organisation = Organisation::factory()->create([
        'name' => 'Organisatie X',
    ]);
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'data_collection_source' => CoreEntityDataCollectionSource::SECONDARY,
            'import_id' => '52333',
            'entity_number_id' => EntityNumber::factory()->state([
                'number' => 'AVGR6018277448',
            ]),
            'name' => 'blanditiis',
            'decision_making' => true,
            'outside_eu' => true,
            'responsibility_distribution' => 'Optio doloribus culpa aspernatur.',
            'logic' => 'Voluptatem voluptatem quis unde accusamus iusto corporis.',
            'importance_consequences' => 'Ab dicta deleniti pariatur perferendis sed fugit magnam qui.',
            'has_security' => true,
            'has_pseudonymization' => true,
            'pseudonymization' => 'Distinctio dolor minus unde illo itaque dolorem expedita.',
            'geb_dpia_executed' => false,
            'geb_dpia_automated' => true,
            'geb_dpia_large_scale_processing' => true,
            'geb_dpia_large_scale_monitoring' => false,
            'geb_dpia_list_required' => true,
            'geb_dpia_criteria_wp248' => false,
            'geb_dpia_high_risk_freedoms' => false,
            'outside_eu_description' => null,
            'outside_eu_protection_level' => false,
            'outside_eu_protection_level_description' => 'Quaerat consectetur repellat iure aut.',
            'created_at' => CarbonImmutable::create(2024, 2, 5),
            'updated_at' => CarbonImmutable::create(2024, 2, 5),
            'review_at' => CalendarDate::createFromFormat('Y-m-d', '2024-2-5'),
            'public_from' => CarbonImmutable::create(2024, 2, 5),

            'avg_responsible_processing_record_service_id' => AvgResponsibleProcessingRecordService::factory()->state([
                'name' => 'Reiciendis rerum voluptas consequatur est animi.',
                'enabled' => true,
            ]),
        ]);

    AvgGoal::factory()
        ->hasAttached($avgResponsibleProcessingRecord)
        ->recycle($organisation)
        ->create([
            'goal' => 'Quos non maiores non.',
            'avg_goal_legal_base' => 'Toestemming betrokkene',
            'remarks' => null,
        ]);

    Receiver::factory()
        ->hasAttached($avgResponsibleProcessingRecord)
        ->recycle($organisation)
        ->create([
            'description' => 'Voluptatem debitis omnis praesentium eum animi iste velit.',
        ]);

    /** @var Responsible $responsible */
    $responsible = Responsible::factory()
        ->hasAttached($avgResponsibleProcessingRecord)
        ->recycle($organisation)
        ->create([
            'name' => 'Luca van Hoevel en van Zwindrecht',
        ]);

    Address::factory()
        ->recycle($organisation)
        ->create([
            'addressable_type' => Responsible::class,
            'addressable_id' => $responsible->id,
            'address' => 'Weylantring 9-0',
            'postal_code' => '2242PK',
            'city' => 'Een-West',
            'country' => 'Georgië',
            'postbox' => 'Wilmonthof 166',
            'postbox_postal_code' => '5839HY',
            'postbox_city' => 'Wageningen',
            'postbox_country' => 'Portugal',
        ]);

    /** @var Snapshot $snapshot */
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->recycle($organisation)
        ->create([
            'state' => Established::class,
        ]);

    /** @var SnapshotDataFactory $snapshotDataFactory */
    $snapshotDataFactory = App::make(SnapshotDataFactory::class);
    $snapshotData = $snapshotDataFactory->createDataForSnapshot($snapshot);

    expect($snapshotData->private_markdown)->toMatchSnapshot();
    expect($snapshotData->public_frontmatter)->toMatchSnapshot();
    expect($snapshotData->public_markdown)->toMatchSnapshot();
});

it('creates new snapshot data for a WpgProcessingRecord', function (): void {
    $organisation = Organisation::factory()->create();
    $wpgProcessingRecord = WpgProcessingRecord::factory()
        ->for($organisation)
        ->create([
            'name' => 'dolorem',
            'entity_number_id' => EntityNumber::factory(state: [
                'number' => 'OAFMQBH86V',
            ]),
        ]);

    $snapshot = Snapshot::factory()
        ->for($wpgProcessingRecord, 'snapshotSource')
        ->state([
            'organisation_id' => $organisation->id,
        ])->create();

    /** @var SnapshotDataFactory $snapshotDataFactory */
    $snapshotDataFactory = App::make(SnapshotDataFactory::class);
    $snapshotData = $snapshotDataFactory->createDataForSnapshot($snapshot);

    expect($snapshotData->private_markdown)->toMatchSnapshot();
    expect($snapshotData->public_markdown)->toBe(null);
    expect($snapshotData->public_frontmatter)->toBe([]);
});
