<?php

declare(strict_types=1);

use App\Enums\Dpia\PersonalDataType;
use App\Enums\Dpia\RiskLevel;
use App\Events\StaticWebsite\BuildEvent;
use App\Models\Dpia\DpiaMeasure;
use App\Models\Dpia\DpiaPersonalData;
use App\Models\Dpia\DpiaRecord;
use App\Models\Dpia\DpiaRisk;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\User;
use App\Services\Snapshot\SnapshotFactory;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Model\OrganisationTestHelper;

it('captures the whole DPIA in a snapshot', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->be(User::factory()->create());

    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create([
        'name' => 'Cameratoezicht parkeergarage',
    ]);
    DpiaPersonalData::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Camerabeelden van bezoekers',
        'type' => PersonalDataType::SPECIAL,
    ]);
    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => 'Onterechte identificatie',
    ]);
    $measure = DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Bordjes plaatsen',
        'residual_level' => RiskLevel::LOW,
    ]);
    $measure->risks()->sync([$risk->id->toString()]);

    $snapshot = app(SnapshotFactory::class)->fromSnapshotSource($dpiaRecord->fresh());

    $markdown = $snapshot->snapshotData->private_markdown->toString();

    expect($markdown)->toContain('Cameratoezicht parkeergarage')
        ->toContain('Camerabeelden van bezoekers')
        ->toContain('Onterechte identificatie')
        ->toContain('Bordjes plaatsen')
        // The measure records which risk it addresses.
        ->toContain('16. Risico')
        ->toContain('17. Maatregelen');
});

it('starts a DPIA snapshot as a concept', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->be(User::factory()->create());

    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();

    $snapshot = app(SnapshotFactory::class)->fromSnapshotSource($dpiaRecord);

    expect($snapshot->state)->toBeInstanceOf(Concept::class);
});

// A DPIA can be vastgesteld like a register, which is what lets an FG or CPO
// see a fixed version and compare it with the next one.
it('can be established so it is vastgesteld', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->be(User::factory()->create());

    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $snapshot = app(SnapshotFactory::class)->fromSnapshotSource($dpiaRecord, InReview::class);

    $snapshot->state->transitionTo(Established::class);

    expect($snapshot->fresh()->state)->toBeInstanceOf(Established::class)
        ->and($snapshot->fresh()->established_at)->not->toBeNull();
});

// A DPIA never appears on the static website, so establishing one should not
// trigger a rebuild.
it('does not rebuild the static website when a DPIA is established', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->be(User::factory()->create());

    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $snapshot = app(SnapshotFactory::class)->fromSnapshotSource($dpiaRecord, InReview::class);

    Event::fake(BuildEvent::class);

    $snapshot->state->transitionTo(Established::class);

    Event::assertNotDispatched(BuildEvent::class);
});
