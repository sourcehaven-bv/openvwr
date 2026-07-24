<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use App\Models\Document;
use App\Models\Organisation;
use App\Transfer\ConflictStrategy;
use App\Transfer\TransferEntityType;
use Illuminate\Database\Eloquent\Model;
use Webmozart\Assert\Assert;

use function is_string;

/**
 * Builds the per-entity preview rows shown before an import/copy runs. Beyond "does the
 * destination already have this?", it uses last_synced_at to decide the default conflict
 * strategy: an existing copy that has not been edited since it was last synced is safe to
 * overwrite silently, while an edited (or name-matched, non-synced) copy is flagged so the
 * user is asked what to do.
 */
readonly class PreviewBuilder
{
    public function __construct(
        private ImportMatcher $importMatcher,
        private EditDetector $editDetector,
        private MediaHashes $mediaHashes,
    ) {
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function build(TransferBundle $bundle, Organisation $organisation): array
    {
        $items = [];

        foreach ($bundle->entities as $id => $entity) {
            // BundleReader / BundleBuilder guarantee every entity carries a known type.
            $typeValue = $entity['type'] ?? null;
            Assert::string($typeValue);

            $type = TransferEntityType::from($typeValue);

            if ($type->isOwned() || $type->isLookup()) {
                continue;
            }

            $items[$id] = $this->buildItem($type, $id, $entity, $organisation);
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $entity
     *
     * @return array<string, mixed>
     */
    private function buildItem(TransferEntityType $type, string $id, array $entity, Organisation $organisation): array
    {
        $match = $this->importMatcher->match($type, $entity, $organisation);
        $name = $entity['name'] ?? null;

        $hasMatch = $match !== null;
        // A copy counts as changed when it was edited locally since sync, or when its
        // attachment no longer matches the source file (a document whose only difference is
        // its binary content). Either way the user should be asked rather than told "identiek".
        $edited = $match !== null
            && ($this->editDetector->isEditedSinceSync($match) || $this->mediaDiffersFromSource($match, $entity));
        // An existing copy that has not been edited since it was last synced is identical to
        // the source: copying it again would be a no-op, so it is skipped and not offered as a
        // choice. Only edited copies need a decision from the user.
        $unchanged = $hasMatch && !$edited;

        return [
            'type_label' => $type->label(),
            'name' => is_string($name) ? $name : $id,
            'selected' => true,
            'has_match' => $hasMatch,
            'unchanged' => $unchanged,
            'match_name' => $match === null ? null : $type->displayName($match),
            // No match: create (null strategy). Matched + edited: default to skip and prompt.
            // Matched + unchanged: skip, no prompt (nothing to copy).
            'needs_decision' => $edited,
            'strategy' => $this->defaultStrategy($hasMatch),
        ];
    }

    /**
     * True when the destination document's attachments no longer match the source's, by
     * content_hash. EditDetector only sees local edits; source-file drift is a separate
     * signal, checked here where both the source (the bundle entity) and the destination
     * document are in hand.
     *
     * @param array<string, mixed> $entity
     */
    private function mediaDiffersFromSource(Model $match, array $entity): bool
    {
        if (!$match instanceof Document) {
            return false;
        }

        return !$this->mediaHashes->match($match, $entity);
    }

    private function defaultStrategy(bool $hasMatch): ?string
    {
        // No match: create (null). Any match — edited or unchanged — defaults to skip: an
        // unchanged copy is a no-op, and an edited copy waits for the user's explicit choice.
        return $hasMatch ? ConflictStrategy::SKIP->value : null;
    }
}
