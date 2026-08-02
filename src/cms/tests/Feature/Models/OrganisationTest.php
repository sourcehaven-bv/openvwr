<?php

declare(strict_types=1);

use App\Models\Algorithm\AlgorithmPublicationCategory;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Algorithm\AlgorithmStatus;
use App\Models\Algorithm\AlgorithmTheme;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgProcessorProcessingRecordService;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\ContactPerson;
use App\Models\ContactPersonPosition;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Models\Dpia\DpiaRecord;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\Snapshot;
use App\Models\Stakeholder;
use App\Models\StakeholderDataItem;
use App\Models\System;
use App\Models\Tag;
use App\Models\Wpg\WpgGoal;
use App\Models\Wpg\WpgProcessingRecord;
use App\Models\Wpg\WpgProcessingRecordService;

it('has algorithmPublicationCategories', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->algorithmPublicationCategories()->count())
        ->toBe(0);

    AlgorithmPublicationCategory::factory()
        ->for($organisation)
        ->create();

    expect($organisation->algorithmPublicationCategories()->count())
        ->toBe(1);
});

it('has algorithmRecords', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->algorithmRecords()->count())
        ->toBe(0);

    AlgorithmRecord::factory()
        ->for($organisation)
        ->create();

    expect($organisation->algorithmRecords()->count())
        ->toBe(1);
});

it('has algorithmStatuses', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->algorithmStatuses()->count())
        ->toBe(0);

    AlgorithmStatus::factory()
        ->for($organisation)
        ->create();

    expect($organisation->algorithmStatuses()->count())
        ->toBe(1);
});

it('has algorithmThemes', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->algorithmThemes()->count())
        ->toBe(0);

    AlgorithmTheme::factory()
        ->for($organisation)
        ->create();

    expect($organisation->algorithmThemes()->count())
        ->toBe(1);
});

it('has avg goals', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->avgGoals()->count())
        ->toBe(0);

    AvgGoal::factory()
        ->for($organisation)
        ->create();

    expect($organisation->avgGoals()->count())
        ->toBe(1);
});

it('has avgProcessorProcessingRecords', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->avgProcessorProcessingRecords()->count())
        ->toBe(0);

    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->for($organisation)
        ->create();

    expect($organisation->avgProcessorProcessingRecords()->count())
        ->toBe(1)
        ->and($organisation->avgProcessorProcessingRecords()->first())
        ->toBeInstanceOf(AvgProcessorProcessingRecord::class)
        ->and($organisation->avgProcessorProcessingRecords()->first()->id)
        ->toBe($avgProcessorProcessingRecord->id);
});

it('has avgProcessorProcessingRecordServices', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->avgProcessorProcessingRecordServices()->count())
        ->toBe(0);

    AvgProcessorProcessingRecordService::factory()
        ->for($organisation)
        ->create();

    expect($organisation->avgProcessorProcessingRecordServices()->count())
        ->toBe(1);
});

it('has avgResponsibleProcessingRecords', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->avgResponsibleProcessingRecords()->count())
        ->toBe(0);

    AvgResponsibleProcessingRecord::factory()
        ->for($organisation)
        ->create();

    expect($organisation->avgResponsibleProcessingRecords()->count())
        ->toBe(1);
});

it('has avgResponsibleProcessingRecordServices', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->avgResponsibleProcessingRecordServices()->count())
        ->toBe(0);

    AvgResponsibleProcessingRecordService::factory()
        ->for($organisation)
        ->create();

    expect($organisation->avgResponsibleProcessingRecordServices()->count())
        ->toBe(1);
});

it('has contact persons', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->contactPersons()->count())
        ->toBe(0);

    ContactPerson::factory()
        ->for($organisation)
        ->create();
    expect($organisation->contactPersons()->count())
        ->toBe(1);
});

it('has contact person positions', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->contactPersonPositions()->count())
        ->toBe(0);

    ContactPersonPosition::factory()
        ->for($organisation)
        ->create();
    expect($organisation->contactPersonPositions()->count())
        ->toBe(1);
});

it('has dataBreachRecords', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->dataBreachRecords()->count())
        ->toBe(0);

    DataBreachRecord::factory()
        ->for($organisation)
        ->create();

    expect($organisation->dataBreachRecords()->count())
        ->toBe(1);
});

it('has documents', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->documents()->count())
        ->toBe(0);

    Document::factory()
        ->for($organisation)
        ->create();
    expect($organisation->documents()->count())
        ->toBe(1);
});

it('has dpiaPrescanRecords', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->dpiaPrescanRecords()->count())
        ->toBe(0);

    DpiaPrescanRecord::factory()
        ->for($organisation)
        ->create();

    expect($organisation->dpiaPrescanRecords()->count())
        ->toBe(1);
});

it('has dpiaRecords', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->dpiaRecords()->count())
        ->toBe(0);

    DpiaRecord::factory()
        ->for($organisation)
        ->create();

    expect($organisation->dpiaRecords()->count())
        ->toBe(1);
});

it('has processors', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->processors()->count())
        ->toBe(0);

    Processor::factory()
        ->for($organisation)
        ->create();
    expect($organisation->processors()->count())
        ->toBe(1);
});

it('has receivers', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->receivers()->count())
        ->toBe(0);

    Receiver::factory()
        ->for($organisation)
        ->create();
    expect($organisation->receivers()->count())
        ->toBe(1);
});

it('has responsibles', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->responsibles()->count())
        ->toBe(0);

    Responsible::factory()
        ->for($organisation)
        ->create();

    expect($organisation->responsibles()->count())
        ->toBe(1);
});

it('has snapshots', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->snapshots()->count())
        ->toBe(0);

    Snapshot::factory()
        ->for($organisation)
        ->create();

    expect($organisation->snapshots()->count())
        ->toBe(1);
});

it('has stakeholder data items', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->stakeholderDataItems()->count())
        ->toBe(0);

    StakeholderDataItem::factory()
        ->for($organisation)
        ->create();
    expect($organisation->stakeholderDataItems()->count())
        ->toBe(1);
});

it('has stakeholders', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->stakeholders()->count())
        ->toBe(0);

    Stakeholder::factory()
        ->for($organisation)
        ->create();
    expect($organisation->stakeholders()->count())
        ->toBe(1);
});

it('has systems', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->systems()->count())
        ->toBe(0);

    System::factory()
        ->for($organisation)
        ->create();
    expect($organisation->systems()->count())
        ->toBe(1);
});

it('has tags', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->tags()->count())
        ->toBe(0);

    Tag::factory()
        ->for($organisation)
        ->create();
    expect($organisation->tags()->count())
        ->toBe(1);
});

it('has wpg goals', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->wpgGoals()->count())
        ->toBe(0);

    WpgGoal::factory()
        ->for($organisation)
        ->create();
    expect($organisation->wpgGoals()->count())
        ->toBe(1);
});

it('has wpg processing records', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->wpgProcessingRecords()->count())
        ->toBe(0);

    WpgProcessingRecord::factory()
        ->for($organisation)
        ->create();
    expect($organisation->wpgProcessingRecords()->count())
        ->toBe(1);
});

it('has wpgProcessingRecordServices', function (): void {
    $organisation = Organisation::factory()->create();
    expect($organisation->wpgProcessingRecordServices()->count())
        ->toBe(0);

    WpgProcessingRecordService::factory()
        ->for($organisation)
        ->create();

    expect($organisation->wpgProcessingRecordServices()->count())
        ->toBe(1);
});
