<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Organisation;
use Illuminate\Console\Command;
use Webmozart\Assert\Assert;

use function json_encode;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class OrganisationList extends Command
{
    protected $signature = 'org:list
        {--json : Output machine readable JSON on stdout instead of a table}
        {--with-trashed : Include soft-deleted organisations}';
    protected $description = 'List organisations with their user counts';

    public function handle(): int
    {
        $organisationQuery = Organisation::query()
            ->with(['responsibleLegalEntity'])
            ->withCount([
                'users',
                'avgResponsibleProcessingRecords',
                'avgProcessorProcessingRecords',
                'wpgProcessingRecords',
            ])
            ->orderBy('name');

        if ($this->option('with-trashed') === true) {
            $organisationQuery->withTrashed();
        }

        $organisations = $organisationQuery->get();

        if ($this->option('json') === true) {
            $this->line($this->encodeJson($organisations->map(function (Organisation $organisation): array {
                return $this->toJsonRow($organisation);
            })->values()->all()));

            return self::SUCCESS;
        }

        $this->table(
            ['Name', 'Slug', 'Users', 'Processings', 'Legal entity', 'Created at', 'Deleted at'],
            $organisations->map(function (Organisation $organisation): array {
                return $this->toTableRow($organisation);
            })->all(),
        );

        return self::SUCCESS;
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     slug: string,
     *     users_count: int,
     *     processing_records_count: int,
     *     responsible_legal_entity: string,
     *     created_at: ?string,
     *     deleted_at: ?string,
     * }
     */
    private function toJsonRow(Organisation $organisation): array
    {
        return [
            'id' => $organisation->id->toString(),
            'name' => $organisation->name,
            'slug' => $organisation->slug,
            'users_count' => $this->getUsersCount($organisation),
            'processing_records_count' => $this->getProcessingRecordsCount($organisation),
            'responsible_legal_entity' => $organisation->responsibleLegalEntity->name,
            'created_at' => $organisation->created_at->toISOString(),
            'deleted_at' => $organisation->deleted_at?->toISOString(),
        ];
    }

    /**
     * @return array<array-key, string>
     */
    private function toTableRow(Organisation $organisation): array
    {
        return [
            $organisation->name,
            $organisation->slug,
            (string) $this->getUsersCount($organisation),
            (string) $this->getProcessingRecordsCount($organisation),
            $organisation->responsibleLegalEntity->name,
            $organisation->created_at->toDateTimeString(),
            $organisation->deleted_at?->toDateTimeString() ?? '-',
        ];
    }

    private function getUsersCount(Organisation $organisation): int
    {
        return $this->getCount($organisation, 'users_count');
    }

    /**
     * The three processing record types together make up the "verwerkingen" a
     * devops operator cares about, so they are summed into a single column
     * instead of three near-empty ones.
     */
    private function getProcessingRecordsCount(Organisation $organisation): int
    {
        return $this->getCount($organisation, 'avg_responsible_processing_records_count')
            + $this->getCount($organisation, 'avg_processor_processing_records_count')
            + $this->getCount($organisation, 'wpg_processing_records_count');
    }

    /**
     * withCount() aliases are dynamic attributes, so the value is only known to
     * be an int at runtime.
     */
    private function getCount(Organisation $organisation, string $attribute): int
    {
        $count = $organisation->getAttribute($attribute);
        Assert::integer($count);

        return $count;
    }

    private function encodeJson(mixed $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
