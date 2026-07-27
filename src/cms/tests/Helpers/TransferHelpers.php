<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\System;
use App\Models\Tag;
use App\Models\User;
use App\Transfer\Export\BundleExporter;
use App\Transfer\TransferEntityType;

/**
 * @return array{0: AvgResponsibleProcessingRecord, 1: Processor, 2: System, 3: Tag}
 */
function seedCopyableRecord(Organisation $organisation): array
{
    $service = AvgResponsibleProcessingRecordService::factory()
        ->for($organisation)
        ->create(['name' => 'Burgerzaken']);

    $record = AvgResponsibleProcessingRecord::factory()
        ->for($organisation)
        ->create([
            'name' => 'Bijstandsuitkeringen',
            'avg_responsible_processing_record_service_id' => $service->id,
            'has_processors' => true,
            'has_systems' => true,
        ]);

    $processor = Processor::factory()->for($organisation)->create(['name' => 'KoboToolbox']);
    $system = System::factory()->for($organisation)->create(['description' => 'BRP Koppeling']);
    $tag = Tag::factory()->for($organisation)->create(['name' => 'AVG-kritisch']);
    $goal = AvgGoal::factory()->for($organisation)->create(['goal' => 'Participatiewet']);

    $record->processors()->attach($processor);
    $record->systems()->attach($system);
    $record->tags()->attach($tag);
    $record->avgGoals()->attach($goal);

    return [$record, $processor, $system, $tag];
}

function copyableUser(Organisation ...$organisations): User
{
    $user = User::factory()->hasAttached(collect($organisations))->create();

    foreach ($organisations as $organisation) {
        $user->organisationRoles()->create([
            'organisation_id' => $organisation->id,
            'role' => Role::CHIEF_PRIVACY_OFFICER,
        ]);
    }

    return $user;
}

/**
 * @return array{0: string, 1: array<string, array<string, mixed>>, 2: AvgResponsibleProcessingRecord, 3: Processor}
 */
function createExportedBundle(Organisation $sourceOrganisation, User $user): array
{
    $service = AvgResponsibleProcessingRecordService::factory()
        ->for($sourceOrganisation)
        ->create(['name' => 'Dienst X']);

    $record = AvgResponsibleProcessingRecord::factory()
        ->for($sourceOrganisation)
        ->create([
            'name' => 'Verwerking A',
            'avg_responsible_processing_record_service_id' => $service->id,
            // the record observer deletes attached processors/systems when these flags are false
            'has_processors' => true,
            'has_systems' => true,
        ]);

    $processor = Processor::factory()->for($sourceOrganisation)->create(['name' => 'Verwerker 1']);
    $processor->address()->create(['city' => 'Amsterdam']);

    $tag = Tag::factory()->for($sourceOrganisation)->create(['name' => 'Label 1']);
    $avgGoal = AvgGoal::factory()->for($sourceOrganisation)->create(['goal' => 'Doel 1']);

    $record->processors()->attach($processor);
    $record->tags()->attach($tag);
    $record->avgGoals()->attach($avgGoal);
    $record->fgRemark()->create(['body' => 'FG opmerking']);
    $record->remarks()->create(['body' => 'Notitie', 'user_id' => $user->id]);

    $path = app(BundleExporter::class)->export(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        [
            'processors' => [$processor->id->toString()],
            'tags' => [$tag->id->toString()],
            'avgGoals' => [$avgGoal->id->toString()],
        ],
        $sourceOrganisation,
    );

    $plan = [
        $record->id->toString() => ['selected' => true, 'strategy' => null],
        $processor->id->toString() => ['selected' => true, 'strategy' => null],
        $tag->id->toString() => ['selected' => true, 'strategy' => null],
        $avgGoal->id->toString() => ['selected' => true, 'strategy' => null],
    ];

    return [$path, $plan, $record, $processor];
}
