<?php

declare(strict_types=1);

use App\Filament\Resources\DpiaRecordResource\Pages\CreateDpiaRecord;
use App\Filament\Resources\DpiaRecordResource\Pages\EditDpiaRecord;
use App\Models\Dpia\DpiaMeasure;
use App\Models\Dpia\DpiaRecord;
use App\Models\Dpia\DpiaRisk;
use App\Services\Dpia\DpiaMeasureRiskLinker;
use Tests\Helpers\Model\OrganisationTestHelper;

it('links a measure to the risk it addresses when saving', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => 'Onterechte identificatie van bezoekers',
    ]);
    $measure = DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Bordjes plaatsen',
    ]);

    // The shape Filament hands to the page: repeater items backed by a saved
    // record are keyed "record-<uuid>".
    $formData = [
        'risks' => [
            'record-' . $risk->id->toString() => ['title' => $risk->title],
        ],
        'measures' => [
            'record-' . $measure->id->toString() => [
                'description' => $measure->description,
                'risks' => ['record-' . $risk->id->toString()],
            ],
        ],
    ];

    app(DpiaMeasureRiskLinker::class)->link($dpiaRecord, $formData);

    expect($measure->fresh()->risks->pluck('title')->all())
        ->toBe(['Onterechte identificatie van bezoekers']);
});

it('links a measure to a risk that was added in the same session', function (): void {
    $organisation = OrganisationTestHelper::create();

    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    // A risk added in the same session already exists in the database by the
    // time afterSave runs, but the form state still has its temporary key.
    DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => 'Nieuw risico uit dezelfde sessie',
    ]);
    $measure = DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Nieuwe maatregel',
    ]);

    $temporaryRiskKey = 'a1b2c3d4-0000-0000-0000-000000000000';

    $formData = [
        'risks' => [
            $temporaryRiskKey => ['title' => 'Nieuw risico uit dezelfde sessie'],
        ],
        'measures' => [
            'record-' . $measure->id->toString() => [
                'description' => $measure->description,
                'risks' => [$temporaryRiskKey],
            ],
        ],
    ];

    app(DpiaMeasureRiskLinker::class)->link($dpiaRecord, $formData);

    expect($measure->fresh()->risks->pluck('title')->all())
        ->toBe(['Nieuw risico uit dezelfde sessie']);
});

it('removes a link when the risk is deselected', function (): void {
    $organisation = OrganisationTestHelper::create();

    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
    ]);
    $measure = DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
    ]);
    $measure->risks()->sync([$risk->id->toString()]);

    $formData = [
        'risks' => [
            'record-' . $risk->id->toString() => ['title' => $risk->title],
        ],
        'measures' => [
            'record-' . $measure->id->toString() => [
                'description' => $measure->description,
                'risks' => [],
            ],
        ],
    ];

    app(DpiaMeasureRiskLinker::class)->link($dpiaRecord, $formData);

    expect($measure->fresh()->risks)->toBeEmpty();
});

it('shows the already linked risks when the form is opened', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $linked = DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => 'Gekoppeld risico',
    ]);
    DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => 'Ongekoppeld risico',
    ]);
    $measure = DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
    ]);
    $measure->risks()->sync([$linked->id->toString()]);

    $this->asFilamentOrganisationUser($organisation);

    $page = $this->createLivewireTestable(EditDpiaRecord::class, ['record' => $dpiaRecord->id->toString()]);

    expect($page->get('data.measures.record-' . $measure->id->toString() . '.risks'))
        ->toBe(['record-' . $linked->id->toString()]);
});

it('keeps the links when an unchanged form is saved', function (): void {
    // The checkbox list is not dehydrated, so nothing carries the pivot through
    // a save on its own. If opening the form does not restore the selection,
    // saving without touching anything syncs every link away.
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => 'Risico dat gekoppeld moet blijven',
    ]);
    $measure = DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
    ]);
    $measure->risks()->sync([$risk->id->toString()]);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(EditDpiaRecord::class, ['record' => $dpiaRecord->id->toString()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($measure->fresh()->risks->pluck('title')->all())
        ->toBe(['Risico dat gekoppeld moet blijven']);
});

it('links measures to risks through the edit page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => 'Risico via de pagina',
    ]);
    $measure = DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Maatregel via de pagina',
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $page = $this->createLivewireTestable(EditDpiaRecord::class, ['record' => $dpiaRecord->id->toString()]);

    $page->set('data.measures.record-' . $measure->id->toString() . '.risks', [
        'record-' . $risk->id->toString(),
    ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($measure->fresh()->risks->pluck('title')->all())
        ->toBe(['Risico via de pagina']);
});

// The same linking on the create page: a risk and the maatregel that addresses
// it are both new, so neither has an id until the page has saved.
it('links measures to risks created in the same session', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(CreateDpiaRecord::class)
        ->fillForm([
            'name' => 'Cameratoezicht parkeergarage',
            'risks' => [
                'nieuw-risico' => [
                    'title' => 'Onbevoegde inzage in camerabeelden',
                    'organisation_id' => $organisation->id->toString(),
                ],
            ],
            'measures' => [
                'nieuwe-maatregel' => [
                    'description' => 'Toegang beperken tot twee medewerkers',
                    'organisation_id' => $organisation->id->toString(),
                    'risks' => ['nieuw-risico'],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $dpiaRecord = DpiaRecord::query()->where('organisation_id', $organisation->id)->sole();
    $measure = $dpiaRecord->measures->sole();

    expect($measure->risks->pluck('title')->all())
        ->toBe(['Onbevoegde inzage in camerabeelden']);
});

// The linker reads raw form state, so it has to survive anything the form can
// hand it without touching the links it cannot account for.
it('leaves the links alone when the form state makes no sense', function (array $formData): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => 'Bestaand risico',
    ]);
    $measure = DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Bestaande maatregel',
    ]);
    $measure->risks()->sync([$risk->id->toString()]);

    app(DpiaMeasureRiskLinker::class)->link($dpiaRecord, $formData);

    expect($measure->fresh()->risks->pluck('title')->all())->toBe(['Bestaand risico']);
})->with([
    'no measures at all' => [[]],
    'measures is not an array' => [['measures' => 'geen array']],
    'a measure that is not an array' => [['measures' => ['record-1' => 'geen array']]],
    'risks is not an array' => [['risks' => 'geen array', 'measures' => []]],
]);

it('skips a measure the form state cannot be matched to', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => 'Bestaand risico',
    ]);

    app(DpiaMeasureRiskLinker::class)->link($dpiaRecord, [
        'risks' => ['record-' . $risk->id->toString() => ['title' => $risk->title]],
        'measures' => [
            // Neither an existing id nor a description that matches one.
            'record-00000000-0000-0000-0000-000000000000' => [
                'description' => 'Bestaat niet',
                'risks' => ['record-' . $risk->id->toString()],
            ],
        ],
    ]);

    expect($dpiaRecord->fresh()->measures)->toBeEmpty();
});

it('ignores risk keys and titles it cannot use', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $measure = DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Maatregel zonder bruikbare risicos',
    ]);

    app(DpiaMeasureRiskLinker::class)->link($dpiaRecord, [
        'risks' => [
            // A numeric key, a risk that is not an array and a title that
            // matches no saved risk: none of these resolve to an id.
            7 => ['title' => 'Numerieke sleutel'],
            'tijdelijk-1' => 'geen array',
            'tijdelijk-2' => ['title' => 'Bestaat niet in de database'],
        ],
        'measures' => [
            'record-' . $measure->id->toString() => [
                'description' => $measure->description,
                'risks' => ['tijdelijk-1', 'tijdelijk-2', 123, 'onbekend'],
            ],
        ],
    ]);

    expect($measure->fresh()->risks)->toBeEmpty();
});

// A risk with a blank title cannot be matched across the save, because the
// title is the only thing identifying it before it has an id.
it('cannot match a risk that has no title', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => '',
    ]);
    $measure = DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Maatregel',
    ]);

    app(DpiaMeasureRiskLinker::class)->link($dpiaRecord, [
        'risks' => ['tijdelijk' => ['title' => '']],
        'measures' => [
            'record-' . $measure->id->toString() => [
                'description' => $measure->description,
                'risks' => ['tijdelijk'],
            ],
        ],
    ]);

    expect($measure->fresh()->risks)->toBeEmpty();
});

it('clears the links when a measure selects nothing usable', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => 'Bestaand risico',
    ]);
    $measure = DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Bestaande maatregel',
    ]);
    $measure->risks()->sync([$risk->id->toString()]);

    app(DpiaMeasureRiskLinker::class)->link($dpiaRecord, [
        'risks' => ['record-' . $risk->id->toString() => ['title' => $risk->title]],
        'measures' => [
            'record-' . $measure->id->toString() => [
                'description' => $measure->description,
                'risks' => 'geen array',
            ],
        ],
    ]);

    expect($measure->fresh()->risks)->toBeEmpty();
});

// A maatregel added but not yet saved has no pivot to restore, so hydrating
// its checkboxes has to be a no-op rather than an error.
it('leaves the checkboxes empty for a maatregel that is not saved yet', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => 'Bestaand risico',
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $page = $this->createLivewireTestable(EditDpiaRecord::class, ['record' => $dpiaRecord->id->toString()]);

    $page->call('mountFormComponentAction', 'data.measures', 'add')
        ->assertHasNoFormErrors();

    $measures = $page->get('data.measures');

    expect($measures)->toHaveCount(1);

    foreach ($measures as $measure) {
        expect($measure['risks'] ?? [])->toBe([]);
    }
});
