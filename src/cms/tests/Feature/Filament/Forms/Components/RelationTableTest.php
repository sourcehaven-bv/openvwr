<?php

declare(strict_types=1);

use App\Enums\Media\MediaGroup;
use App\Filament\Forms\Components\RelationTable;
use App\Filament\Resources\AlgorithmRecordResource\Pages\EditAlgorithmRecord;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Tests\Helpers\Model\OrganisationTestHelper;

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

it('links a document name to its file when the document has an attachment', function (): void {
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
        ->assertSeeHtml('href="' . $downloadUrl . '"')
        ->assertDontSeeHtml('>' . $withoutFile->name . '</a>');
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
