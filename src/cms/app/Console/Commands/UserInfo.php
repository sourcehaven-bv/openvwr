<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Collections\OrganisationUserRoleCollection;
use App\Models\OrganisationUserRole;
use App\Models\User;
use App\Models\UserGlobalRole;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

use function array_values;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

class UserInfo extends Command
{
    protected $signature = 'user:info {email} {--json}';
    protected $description = 'List current users';

    public function handle(): int
    {
        try {
            /** @var User $user */
            $user = User::with(['globalRoles', 'organisationRoles.organisation'])
                ->where('email', $this->argument('email'))
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            // stderr, so that --json consumers can parse stdout as JSON even
            // when the lookup fails.
            $this->output->getErrorStyle()->error('user not found');

            return self::FAILURE;
        }

        if ($this->option('json')) {
            $this->line(json_encode($this->getJsonData($user), JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $userData = [
            ['id', $user->id->toString()],
            ['name', $user->name],
            ['email', $user->email],
            ['created at', $user->created_at->toDateTimeString()],
            ['updated at', $user->updated_at->toDateTimeString()],
            ['otp confirmed at', $user->otp_confirmed_at ?? 'null'],
            ['global roles', $this->getGlobalRoles($user)->implode(', ')],
            ['organisation roles', $this->getOrganisationRoles($user)->implode("\n")],
        ];

        $this->table(['Key', 'Value'], $userData);

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function getJsonData(User $user): array
    {
        $organisationRoles = [];
        foreach ($user->organisationRoles as $role) {
            $organisationName = $role->organisation->name;
            $organisationRoles[$organisationName] ??= [
                'organisation' => $organisationName,
                'roles' => [],
            ];
            $organisationRoles[$organisationName]['roles'][] = $role->role->value;
        }

        return [
            'id' => $user->id->toString(),
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->toISOString(),
            'updated_at' => $user->updated_at->toISOString(),
            'otp_confirmed_at' => $user->otp_confirmed_at?->toISOString(),
            'global_roles' => $this->getGlobalRoles($user)->values()->all(),
            'organisation_roles' => array_values($organisationRoles),
        ];
    }

    /**
     * @return Collection<array-key, string>
     */
    private function getGlobalRoles(User $user): Collection
    {
        return $user->globalRoles
            ->map(static function (UserGlobalRole $userGlobalRole): string {
                return $userGlobalRole->role->value;
            });
    }

    /**
     * @return Collection<array-key, non-falsy-string>
     */
    private function getOrganisationRoles(User $user): Collection
    {
        return $user->organisationRoles
            ->groupBy(static function (OrganisationUserRole $userOrganisationRole): string {
                return $userOrganisationRole->organisation->name;
            })
            ->map(static function (OrganisationUserRoleCollection $userOrganisationRoleCollection, string $organisationName): string {
                $organisationRoles = $userOrganisationRoleCollection
                    ->map(static function (OrganisationUserRole $userOrganisationRole): string {
                        return $userOrganisationRole->role->value;
                    })
                    ->implode(', ');

                return sprintf('%s: %s', $organisationName, $organisationRoles);
            });
    }
}
