<?php

declare(strict_types=1);

use App\Models\Algorithm\AlgorithmPublicationCategory;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Algorithm\AlgorithmStatus;
use App\Models\Algorithm\AlgorithmTheme;
use App\Models\EntityNumber;
use App\Models\Snapshot;
use App\Services\Snapshot\SnapshotSource\AlgorithmRecordDataFactory;

it('can generate private markdown', function (): void {
    $algorithmRecord = AlgorithmRecord::factory()
        ->create([
            'entity_number_id' => EntityNumber::factory()->state([
                'number' => 'ALG000001',
            ]),
            'name' => 'a5c2f0f4-1d1e-4a4a-9d5c-1f6a1b2c3d4e',
            'description' => 'Beschrijving van het algoritme.',
            'algorithm_theme_id' => AlgorithmTheme::factory()->create(['name' => 'Verkeer']),
            'algorithm_status_id' => AlgorithmStatus::factory()->create(['name' => 'In gebruik']),
            'algorithm_publication_category_id' => AlgorithmPublicationCategory::factory()
                ->create(['name' => 'Impactvolle algoritmes']),
            'start_date' => '2024-01-01',
            'end_date' => null,
            'contact_data' => 'contact@example.com',
            'public_page_link' => 'https://example.com/algoritme',
            'source_link' => 'https://example.com/bron',

            'resp_goal_and_impact' => 'Doel en impact.',
            'resp_considerations' => 'Afwegingen.',
            'resp_human_intervention' => 'Menselijke tussenkomst.',
            'resp_risk_analysis' => 'Risicobeheer.',
            'resp_legal_base_title' => 'Wegenverkeerswet 1994',
            'resp_legal_base' => 'Wettelijke basis.',
            'resp_legal_base_link' => 'https://example.com/wet',
            'resp_processor_registry_link' => 'https://example.com/verwerkingsregister',
            'resp_impact_tests' => 'DPIA',
            'resp_impact_test_links' => 'https://example.com/dpia',
            'resp_impact_tests_description' => 'Toelichting op impacttoetsen.',

            'oper_data_title' => 'Titel van gegevensbron',
            'oper_data' => 'Gegevens.',
            'oper_links' => 'https://example.com/gegevensbron',
            'oper_technical_operation' => 'Technische werking.',
            'oper_supplier' => 'Intern ontwikkeld',
            'oper_source_code_link' => 'https://example.com/broncode',

            'meta_date_of_development' => '2023-06-01',
            'meta_owner_algorithm' => 'Afdelingshoofd',
            'meta_product_owner_algorithm' => 'Product owner',
            'meta_national_id' => 'REG-001',
            'meta_source_id' => 'BRON-001',
            'meta_tags' => 'verkeer, sensoren',
            'impact_with_consequences' => true,
            'impact_more_algorithms_applied' => false,
            'impact_effect_on_outcome' => null,
            'validation_answers_checked_by_product_owner' => true,
        ]);

    $snapshot = Snapshot::factory()
        ->for($algorithmRecord, 'snapshotSource')
        ->create();

    $algorithmRecordDataFactory = new AlgorithmRecordDataFactory();
    expect($algorithmRecordDataFactory->generatePrivateMarkdown($snapshot))
        ->toMatchSnapshot();
});

it('can generate public frontmatter', function (): void {
    $snapshot = Snapshot::factory()
        ->create();

    $algorithmRecordDataFactory = new AlgorithmRecordDataFactory();
    expect($algorithmRecordDataFactory->generatePublicFrontmatter($snapshot))
        ->toBe([]);
});

it('can generate public markdown', function (): void {
    $snapshot = Snapshot::factory()
        ->create();

    $algorithmRecordDataFactory = new AlgorithmRecordDataFactory();
    expect($algorithmRecordDataFactory->generatePublicMarkdown($snapshot))
        ->toBeNull();
});
