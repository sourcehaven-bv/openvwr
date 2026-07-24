<?php

declare(strict_types=1);

use App\Enums\Media\MediaGroup;
use App\Models\Document;
use App\Models\Organisation;
use App\Transfer\Import\DocumentMediaImporter;
use App\Transfer\Import\MediaResolver;
use Illuminate\Support\Facades\Storage;

function nullMediaResolver(): MediaResolver
{
    return new class implements MediaResolver {
        /**
         * @param array<mixed> $mediaItem
         */
        public function resolve(array $mediaItem): ?string
        {
            return null;
        }
    };
}

it('clears the attachments collection on sync when the source media block is malformed', function (): void {
    Storage::fake('media-library');

    $document = Document::factory()->for(Organisation::factory())->create();
    $document->addMediaFromString('stale')
        ->usingFileName('old.txt')
        ->toMediaCollection(MediaGroup::ATTACHMENTS->value);

    // A non-array media block matches nothing (so sync proceeds) and yields no collection
    // names, so the fallback clears attachments — the stale file must not survive.
    app(DocumentMediaImporter::class)->sync($document, ['media' => 'nope'], nullMediaResolver());

    expect($document->refresh()->load('media')->media)->toHaveCount(0);
});
