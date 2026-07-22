<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Algorithm\AlgorithmPublicationCategory;
use App\Models\Algorithm\AlgorithmStatus;
use App\Models\Algorithm\AlgorithmTheme;
use App\Models\ContactPersonPosition;
use App\Models\DocumentType;

/**
 * The per-organisation lookup lists DemoSeeder creates up front.
 *
 * A plain array<string, mixed> would do the same job at runtime, but it hides
 * every type from static analysis and turns each use site into an untyped
 * offset lookup. Naming the fields keeps the seeder honest about what exists.
 */
final class DemoLookups
{
    /**
     * @param array<string, DocumentType> $documentTypes keyed by name, so records can pick their type
     * @param list<ContactPersonPosition> $contactPersonPositions
     * @param array<string, AlgorithmTheme> $algorithmThemes
     * @param array<string, AlgorithmStatus> $algorithmStatuses
     * @param array<string, AlgorithmPublicationCategory> $algorithmCategories
     */
    public function __construct(
        public array $documentTypes,
        public array $contactPersonPositions,
        public array $algorithmThemes,
        public array $algorithmStatuses,
        public array $algorithmCategories,
    ) {
    }
}
