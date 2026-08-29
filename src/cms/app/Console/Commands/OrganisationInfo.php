<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Organisation;
use App\Models\OrganisationUserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

use function implode;
use function json_encode;
use function sort;
use function sprintf;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class OrganisationInfo extends Command
{
    protected $signature = 'org:info
        {organisation : Slug or UUID of the organisation}
        {--json : Output machine readable JSON on stdout instead of a table}
        {--with-trashed : Also look up soft-deleted organisations}';
    protected $description = 'Show details for a single organisation';

    public function handle(): int
    {
        $identifier = $this->argument('organisation');

        $organisation = $this->findOrganisation($identifier);
        if ($organisation === null) {
            $this->writeError(sprintf('organisation not found: %s', $identifier));

            return self::FAILURE;
        }

        $roles = $this->getRolesByUserId($organisation);

        if ($this->option('json') === true) {
            $this->line($this->encodeJson($this->toJson($organisation, $roles)));

            return self::SUCCESS;
        }

        $this->table(['Key', 'Value'], $this->toDetailRows($organisation));
        $this->table(['User', 'Email', 'Organisation roles'], $this->toUserRows($organisation, $roles));

        return self::SUCCESS;
    }

    private function findOrganisation(string $identifier): ?Organisation
    {
        $organisationQuery = Organisation::query()
            ->with(['responsibleLegalEntity', 'users'])
            ->withCount([
                'users',
                'avgResponsibleProcessingRecords',
                'avgProcessorProcessingRecords',
                'wpgProcessingRecords',
                'dataBreachRecords',
                'algorithmRecords',
                'documents',
            ]);

        if ($this->option('with-trashed') === true) {
            $organisationQuery->withTrashed();
        }

        // The argument doubles as slug and UUID so a wrapper can pass whatever
        // identifier it happens to hold without a lookup round-trip.
        $column = Str::isUuid($identifier) ? 'id' : 'slug';
        $organisationQuery->where($column, $identifier);

        return $organisationQuery->first();
    }

    /**
     * Roles live on organisation_user_role rows rather than on the pivot, so
     * they are fetched in one query and grouped in memory to avoid an N+1 over
     * the organisation's users.
     *
     * @return array<string, list<string>>
     */
    private function getRolesByUserId(Organisation $organisation): array
    {
        $organisationUserRoles = OrganisationUserRole::query()
            ->where('organisation_id', $organisation->id->toString())
            ->get();

        $rolesByUserId = [];
        foreach ($organisationUserRoles as $organisationUserRole) {
            $rolesByUserId[$organisationUserRole->user_id->toString()][] = $organisationUserRole->role->value;
        }

        foreach ($rolesByUserId as $userId => $userRoles) {
            sort($userRoles);
            $rolesByUserId[$userId] = $userRoles;
        }

        return $rolesByUserId;
    }

    /**
     * @param array<string, list<string>> $roles
     *
     * @return array<string, mixed>
     */
    private function toJson(Organisation $organisation, array $roles): array
    {
        return [
            'id' => $organisation->id->toString(),
            'name' => $organisation->name,
            'slug' => $organisation->slug,
            'responsible_legal_entity' => $organisation->responsibleLegalEntity->name,
            'coc_number' => $organisation->coc_number,
            'sector' => $organisation->sector,
            'created_at' => $organisation->created_at->toISOString(),
            'updated_at' => $organisation->updated_at->toISOString(),
            'deleted_at' => $organisation->deleted_at?->toISOString(),
            'counts' => $this->getCounts($organisation),
            'users' => $this->toJsonUsers($organisation, $roles),
        ];
    }

    /**
     * @param array<string, list<string>> $roles
     *
     * @return list<array<string, mixed>>
     */
    private function toJsonUsers(Organisation $organisation, array $roles): array
    {
        $users = [];
        foreach ($organisation->users as $user) {
            $users[] = [
                'id' => $user->id->toString(),
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $this->getRolesForUser($roles, $user),
            ];
        }

        return $users;
    }

    /**
     * @return array<string, int>
     */
    private function getCounts(Organisation $organisation): array
    {
        return [
            'users' => $this->getCount($organisation, 'users_count'),
            'avg_responsible_processing_records' => $this->getCount($organisation, 'avg_responsible_processing_records_count'),
            'avg_processor_processing_records' => $this->getCount($organisation, 'avg_processor_processing_records_count'),
            'wpg_processing_records' => $this->getCount($organisation, 'wpg_processing_records_count'),
            'data_breach_records' => $this->getCount($organisation, 'data_breach_records_count'),
            'algorithm_records' => $this->getCount($organisation, 'algorithm_records_count'),
            'documents' => $this->getCount($organisation, 'documents_count'),
        ];
    }

    /**
     * @return list<array<array-key, string>>
     */
    private function toDetailRows(Organisation $organisation): array
    {
        $rows = [
            ['id', $organisation->id->toString()],
            ['name', $organisation->name],
            ['slug', $organisation->slug],
            ['responsible legal entity', $organisation->responsibleLegalEntity->name],
            ['coc number', $organisation->coc_number ?? 'null'],
            ['sector', $organisation->sector ?? 'null'],
            ['created at', $organisation->created_at->toDateTimeString()],
            ['updated at', $organisation->updated_at->toDateTimeString()],
            ['deleted at', $organisation->deleted_at?->toDateTimeString() ?? 'null'],
        ];

        foreach ($this->getCounts($organisation) as $countName => $count) {
            $rows[] = [$countName, (string) $count];
        }

        return $rows;
    }

    /**
     * @param array<string, list<string>> $roles
     *
     * @return list<array<array-key, string>>
     */
    private function toUserRows(Organisation $organisation, array $roles): array
    {
        $rows = [];
        foreach ($organisation->users as $user) {
            $rows[] = [
                $user->name,
                $user->email,
                implode(', ', $this->getRolesForUser($roles, $user)),
            ];
        }

        return $rows;
    }

    /**
     * @param array<string, list<string>> $roles
     *
     * @return list<string>
     */
    private function getRolesForUser(array $roles, User $user): array
    {
        return $roles[$user->id->toString()] ?? [];
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

    private function writeError(string $message): void
    {
        // Plain error() writes to stdout, which would corrupt the JSON a
        // wrapper is parsing, so failures always go to stderr.
        $this->output->getErrorStyle()->error($message);
    }

    private function encodeJson(mixed $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
