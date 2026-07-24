<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use App\Models\Organisation;
use App\Transfer\ConflictStrategy;
use App\Transfer\TransferEntityType;
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

        $edited = $match !== null && $this->editDetector->isEditedSinceSync($match);

        return [
            'type_label' => $type->label(),
            'name' => is_string($name) ? $name : $id,
            'selected' => true,
            'has_match' => $match !== null,
            'match_name' => $match === null ? null : $type->displayName($match),
            // No match: create (null strategy). Matched + edited: default to skip and prompt.
            // Matched + untouched since sync: overwrite silently.
            'needs_decision' => $edited,
            'strategy' => $this->defaultStrategy($match !== null, $edited),
        ];
    }

    private function defaultStrategy(bool $hasMatch, bool $edited): ?string
    {
        if (!$hasMatch) {
            return null;
        }

        return $edited ? ConflictStrategy::SKIP->value : ConflictStrategy::OVERWRITE->value;
    }
}
