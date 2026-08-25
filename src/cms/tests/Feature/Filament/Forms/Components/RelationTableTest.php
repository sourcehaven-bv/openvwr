<?php

declare(strict_types=1);

use App\Enums\Media\MediaGroup;
use App\Filament\Forms\Components\RecentFirstOptions;
use App\Filament\Forms\Components\RelationTable;
use App\Filament\Resources\AlgorithmRecordResource\Pages\EditAlgorithmRecord;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\SystemResource;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Document;
use App\Models\System;
use Illuminate\Support\Facades\Storage;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('shows the linked documents as table rows', function (): void {
    $organisation = OrganisationTestHelper::create();
    $documents = Document::factory()->recycle($organisation)->count(2)->create();

    $algorithmRecord = AlgorithmRecord::factory()->recycle($organisation)->create();
    $algorithmRecord->documents()->attach($documents);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAlgorithmRecord::class, [
            'record' => $algorithmRecord->getRouteKey(),
        ])
        ->assertFormSet(function (array $state) use ($documents): bool {
            /** @var array<int, string> $linked */
            $linked = $state['document_id'] ?? [];

            return count($linked) === 2
                && in_array($documents[0]->getKey()->toString(), $linked, true)
                && in_array($documents[1]->getKey()->toString(), $linked, true);
        });
});

it('links a document name to its screen and adds a download icon when it has an attachment', function (): void {
    Storage::fake('filament');
    Storage::fake('media-library');

    $organisation = OrganisationTestHelper::create();

    $withFile = Document::factory()->for($organisation)->create();
    $withFile->addMediaFromString('file bytes')
        ->usingFileName('dpia.txt')
        ->toMediaCollection(MediaGroup::ATTACHMENTS->value);

    $withoutFile = Document::factory()->for($organisation)->create();

    $algorithmRecord = AlgorithmRecord::factory()->recycle($organisation)->create();
    $algorithmRecord->documents()->attach([$withFile, $withoutFile]);

    $withFile->refresh();
    $downloadUrl = $withFile->getFirstMedia(MediaGroup::ATTACHMENTS->value)?->getFullUrl();
    expect($downloadUrl)->not->toBeNull();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAlgorithmRecord::class, [
            'record' => $algorithmRecord->getRouteKey(),
        ])
        // Both names link to the document's own screen; the user may edit, so
        // the link goes straight to the edit page.
        ->assertSeeHtml('href="' . DocumentResource::getUrl('edit', ['record' => $withFile, 'tenant' => $organisation]) . '"')
        ->assertSeeHtml('href="' . DocumentResource::getUrl('edit', ['record' => $withoutFile, 'tenant' => $organisation]) . '"')
        // Only the document with an attachment gets the direct-download icon.
        ->assertSeeHtml('href="' . $downloadUrl . '"');
});

it('links a document when it is selected and saved', function (): void {
    $organisation = OrganisationTestHelper::create();
    $document = Document::factory()->recycle($organisation)->create();

    $algorithmRecord = AlgorithmRecord::factory()->recycle($organisation)->create();

    expect($algorithmRecord->documents)->toBeEmpty();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAlgorithmRecord::class, [
            'record' => $algorithmRecord->getRouteKey(),
        ])
        ->fillForm([
            'document_id' => [$document->getKey()->toString()],
            'meta_owner_algorithm' => fake()->name(),
            'meta_product_owner_algorithm' => fake()->name(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $algorithmRecord->refresh();

    expect($algorithmRecord->documents->pluck('id')->toArray())
        ->toEqual([$document->getKey()]);
});

it('unlinks a document through the remove action', function (): void {
    $organisation = OrganisationTestHelper::create();
    [$keep, $remove] = Document::factory()->recycle($organisation)->count(2)->create()->all();

    $algorithmRecord = AlgorithmRecord::factory()->recycle($organisation)->create();
    $algorithmRecord->documents()->attach([$keep, $remove]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAlgorithmRecord::class, [
            'record' => $algorithmRecord->getRouteKey(),
        ])
        ->fillForm([
            'meta_owner_algorithm' => fake()->name(),
            'meta_product_owner_algorithm' => fake()->name(),
        ])
        ->callFormComponentAction(
            'document_id',
            RelationTable::REMOVE_ACTION,
            arguments: ['id' => $remove->getKey()->toString()],
        )
        ->assertFormSet(['document_id' => [$keep->getKey()->toString()]])
        ->call('save')
        ->assertHasNoFormErrors();

    $algorithmRecord->refresh();

    expect($algorithmRecord->documents->pluck('id')->toArray())
        ->toEqual([$keep->getKey()]);
});

it('ignores the remove action when no id is given', function (): void {
    $organisation = OrganisationTestHelper::create();
    $document = Document::factory()->recycle($organisation)->create();

    $algorithmRecord = AlgorithmRecord::factory()->recycle($organisation)->create();
    $algorithmRecord->documents()->attach($document);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAlgorithmRecord::class, [
            'record' => $algorithmRecord->getRouteKey(),
        ])
        ->fillForm([
            'meta_owner_algorithm' => fake()->name(),
            'meta_product_owner_algorithm' => fake()->name(),
        ])
        ->callFormComponentAction('document_id', RelationTable::REMOVE_ACTION, arguments: [])
        ->assertFormSet(['document_id' => [$document->getKey()->toString()]]);
});

it('links a linked system to its own screen', function (): void {
    $organisation = OrganisationTestHelper::create();
    $system = System::factory()->recycle($organisation)->create();

    $processingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['has_systems' => true]);
    $processingRecord->systems()->attach($system);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $processingRecord->getRouteKey(),
        ])
        ->assertSeeHtml('href="' . SystemResource::getUrl('edit', [
            'record' => $system,
            'tenant' => $organisation,
        ]) . '"');
});

it('shows the linked users with name and email as table rows', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $processingRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();
    $processingRecord->users()->attach($user);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $processingRecord->getRouteKey(),
        ])
        ->assertFormSet(['users' => [$user->getKey()->toString()]])
        ->assertSee($user->name)
        ->assertSee($user->email);
});

it('does not render users from another organisation injected into the state', function (): void {
    $organisation = OrganisationTestHelper::create();

    // The pivot-scoped user table must not render users that are only
    // attached to a different organisation.
    $otherOrganisation = OrganisationTestHelper::create();
    $foreignUser = UserTestHelper::createForOrganisation($otherOrganisation, [
        'name' => 'geheime-andere-gebruiker',
    ]);

    $processingRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $processingRecord->getRouteKey(),
        ])
        ->fillForm([
            'users' => [$foreignUser->getKey()->toString()],
        ])
        ->assertDontSee($foreignUser->name);
});

it('does not render records from another organisation injected into the state', function (): void {
    $organisation = OrganisationTestHelper::create();
    $ownDocument = Document::factory()->recycle($organisation)->create();

    // A record belonging to a different tenant. Its id is only ever reachable
    // by tampering with the (client-influenced) live form state.
    $otherOrganisation = OrganisationTestHelper::create();
    $foreignDocument = Document::factory()
        ->recycle($otherOrganisation)
        ->create(['name' => 'geheime-andere-organisatie']);

    $algorithmRecord = AlgorithmRecord::factory()->recycle($organisation)->create();
    $algorithmRecord->documents()->attach($ownDocument);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAlgorithmRecord::class, [
            'record' => $algorithmRecord->getRouteKey(),
        ])
        ->fillForm([
            'document_id' => [
                $ownDocument->getKey()->toString(),
                $foreignDocument->getKey()->toString(),
            ],
        ])
        ->assertSee($ownDocument->name)
        ->assertDontSee($foreignDocument->name);
});

it('offers the options straight away instead of an empty dropdown', function (): void {
    $organisation = OrganisationTestHelper::create();
    $system = System::factory()->recycle($organisation)->create();

    $processingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['has_systems' => true]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $processingRecord->getRouteKey(),
        ])
        ->assertFormFieldExists('systems', static function (RelationTable $field) use ($system): bool {
            // One group, holding the option, rather than an empty dropdown.
            return $field->getOptions() === [
                __('general.picker_recent') => [
                    $system->getKey()->toString() => $system->description,
                ],
            ];
        });
});

it('offers the most recently edited records first, capped at the group size', function (): void {
    $organisation = OrganisationTestHelper::create();

    // More records than the dropdown shows, created oldest first.
    $systems = [];
    foreach (range(1, RecentFirstOptions::RECENT_COUNT + 5) as $index) {
        $systems[] = System::factory()->recycle($organisation)->create([
            'description' => 'Systeem ' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            'updated_at' => now()->subDays(RecentFirstOptions::RECENT_COUNT + 5 - $index),
        ]);
    }

    $mostRecent = end($systems);
    $oldest = $systems[0];

    $processingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['has_systems' => true]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $processingRecord->getRouteKey(),
        ])
        ->assertFormFieldExists(
            'systems',
            static function (RelationTable $field) use ($mostRecent, $oldest): bool {
                /** @var array<string, string> $options */
                $options = $field->getOptions()[__('general.picker_recent')] ?? [];

                // The record edited last heads the list; the oldest ones fall
                // outside it and are reached by typing instead.
                return array_key_first($options) === $mostRecent->getKey()->toString()
                    && count($options) === RecentFirstOptions::RECENT_COUNT
                    && !array_key_exists($oldest->getKey()->toString(), $options);
            },
        );
});

it('does not offer records from another organisation in the preloaded options', function (): void {
    $organisation = OrganisationTestHelper::create();
    $ownSystem = System::factory()->recycle($organisation)->create();

    $otherOrganisation = OrganisationTestHelper::create();
    $foreignSystem = System::factory()->recycle($otherOrganisation)->create();

    $processingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['has_systems' => true]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $processingRecord->getRouteKey(),
        ])
        ->assertFormFieldExists(
            'systems',
            static function (RelationTable $field) use ($ownSystem, $foreignSystem): bool {
                /** @var array<string, string> $options */
                $options = $field->getOptions()[__('general.picker_recent')] ?? [];

                return array_key_exists($ownSystem->getKey()->toString(), $options)
                    && !array_key_exists($foreignSystem->getKey()->toString(), $options);
            },
        );
});

it('sends the heading and the recent records to the javascript control', function (): void {
    $organisation = OrganisationTestHelper::create();
    System::factory()->recycle($organisation)->count(RecentFirstOptions::RECENT_COUNT + 2)->create();

    // Nothing linked yet: the case where the dropdown used to open empty.
    $processingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['has_systems' => true]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $processingRecord->getRouteKey(),
        ])
        ->assertFormFieldExists('systems', static function (RelationTable $field): bool {
            // choices.js draws the heading from this shape; without the
            // nesting the ordering would look arbitrary to the user.
            $forJs = $field->getOptionsForJs();

            return count($forJs) === 1
                && ($forJs[0]['label'] ?? null) === __('general.picker_recent')
                && count($forJs[0]['choices'] ?? []) === RecentFirstOptions::RECENT_COUNT;
        });
});
