<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Components\Uuid\UuidInterface;
use App\Models\ImportMappingProfile;
use Illuminate\Database\Eloquent\Model;

use function array_diff;
use function array_values;
use function is_numeric;

/**
 * Finds and stores reusable mappings.
 *
 * Every lookup is scoped to one organisation. A profile records which columns
 * an organisation decided to keep out of its register; another organisation
 * uploading a sheet with the same columns must not inherit those decisions.
 */
class MappingProfileRepository
{
    /**
     * An exact fingerprint match means the sheet has been seen before and the
     * saved mapping can be applied as-is.
     */
    public function findByFingerprint(string $fingerprint, UuidInterface $organisationId): ?ImportMappingProfile
    {
        return ImportMappingProfile::query()
            ->where('organisation_id', $organisationId)
            ->where('source_fingerprint', $fingerprint)
            ->orderByDesc('version')
            ->first();
    }

    /**
     * A sheet whose columns changed (a filter was adjusted at the source) still
     * matches a saved profile for the same target; the caller shows the diff.
     *
     * @return array<int, ImportMappingProfile>
     */
    public function findCandidates(string $target, UuidInterface $organisationId): array
    {
        return ImportMappingProfile::query()
            ->where('organisation_id', $organisationId)
            ->where('target', $target)
            ->orderByDesc('version')
            ->get()
            ->all();
    }

    /**
     * Columns present in the sheet but not in the profile, and the other way
     * round, so the user sees what changed instead of starting over.
     *
     * @param array<int, string> $headers
     *
     * @return array{added: array<int, string>, removed: array<int, string>}
     */
    public function diff(ImportMappingProfile $profile, array $headers): array
    {
        $mappingProfile = $profile->toMappingProfile();
        $known = [...$mappingProfile->mappedSources(), ...$mappingProfile->unmapped];

        return [
            'added' => array_values(array_diff($headers, $known)),
            'removed' => array_values(array_diff($known, $headers)),
        ];
    }

    /**
     * Profiles are immutable: saving under an existing name adds a version, so
     * an import can always be traced back to the exact mapping that made it.
     *
     * @param MappingProfile<Model> $mappingProfile
     * @param array<int, string> $headers
     */
    public function store(
        string $name,
        MappingProfile $mappingProfile,
        array $headers,
        UuidInterface $organisationId,
        ?UuidInterface $userId = null,
    ): ImportMappingProfile {
        $latest = ImportMappingProfile::query()
            ->where('organisation_id', $organisationId)
            ->where('name', $name)
            ->max('version');
        $version = is_numeric($latest) ? (int) $latest : 0;

        $profile = new ImportMappingProfile();
        $profile->organisation_id = $organisationId;
        $profile->name = $name;
        $profile->target = $mappingProfile->target;
        $profile->source_fingerprint = MappingProfile::fingerprint($headers);
        $profile->mapping = $mappingProfile->toArray();
        $profile->version = $version + 1;
        $profile->created_by = $userId;
        $profile->save();

        return $profile;
    }
}
