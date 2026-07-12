<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Authorization\Role;
use App\Enums\EntityNumberType;
use App\Models\EntityNumberCounter;
use App\Models\Organisation;
use App\Models\ResponsibleLegalEntity;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

use function filter_var;
use function Laravel\Prompts\text;

use const FILTER_VALIDATE_EMAIL;

class UserCreateAdmin extends Command
{
    protected $signature = 'user:create-admin
        {--name= : Name for the new admin (skips the prompt when set)}
        {--email= : Email for the new admin (skips the prompt when set)}
        {--organisation= : Organisation name to create or reuse (skips the prompt when set)}';
    protected $description = 'Create a new admin user';

    public function handle(): int
    {
        try {
            $inputData = $this->getInputData();
        } catch (Throwable $throwable) {
            $this->output->error($throwable->getMessage());

            return self::FAILURE;
        }

        try {
            $organisation = $this->createOrGetOrganisation($inputData['organisation']);
            $this->createUser($inputData['name'], $inputData['email'], $organisation);
        } catch (Throwable $throwable) {
            $this->output->error($throwable->getMessage());

            return self::FAILURE;
        }

        $this->output->success('User created');

        return self::SUCCESS;
    }

    /**
     * Collect inputs, preferring CLI flags for unattended use (Ansible /
     * bootstrap scripts). Any flag left out falls back to the interactive
     * prompt so `php artisan user:create-admin` on a support shell keeps
     * behaving as before.
     *
     * @return array{'name': string, 'email': string, 'organisation': string}
     */
    private function getInputData(): array
    {
        return [
            'name' => $this->resolveName(),
            'email' => $this->resolveEmail(),
            'organisation' => $this->resolveOrganisation(),
        ];
    }

    private function resolveName(): string
    {
        $name = $this->option('name');
        if (is_string($name) && $name !== '') {
            return $name;
        }

        return text(label: 'Name', default: 'admin', required: true);
    }

    private function resolveEmail(): string
    {
        $email = $this->option('email');
        if (is_string($email) && $email !== '') {
            // Non-interactive path: same validation as the prompt, but as
            // an exception so the command exits FAILURE instead of
            // re-prompting into a closed stdin.
            if ($this->isInvalidEmail($email)) {
                throw new Exception('The email address must be valid.');
            }
            if ($this->userWithEmailExists($email)) {
                throw new Exception('A user with this email address already exists');
            }

            return $email;
        }

        return text(
            label: 'Email address',
            default: 'admin@example.com',
            required: true,
            validate: function (string $email): ?string {
                return match (true) {
                    $this->isInvalidEmail($email) => 'The email address must be valid.',
                    $this->userWithEmailExists($email) => 'A user with this email address already exists',
                    default => null,
                };
            },
        );
    }

    private function resolveOrganisation(): string
    {
        $organisation = $this->option('organisation');
        if (is_string($organisation) && $organisation !== '') {
            return $organisation;
        }

        return text(label: 'Organisation', default: 'Example Organization', required: true);
    }

    private function isInvalidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== $email;
    }

    private function userWithEmailExists(string $email): bool
    {
        return User::where('email', $email)
            ->exists();
    }

    private function createOrGetOrganisation(string $organisationName): Organisation
    {
        $responsibleLegalEntity = ResponsibleLegalEntity::firstOrFail();

        $organisation = Organisation::firstOrNew(['slug' => Str::slug($organisationName)]);

        if ($organisation->exists) {
            return $organisation;
        }

        $databreachEntityNumberPrefix = $this->createEntityNumberCounter(EntityNumberType::DATABREACH, $organisationName);
        $registerEntityNumberPrefix = $this->createEntityNumberCounter(EntityNumberType::REGISTER, $organisationName);

        return Organisation::firstOrCreate([
            'slug' => Str::slug($organisationName),
        ], [
            'name' => $organisationName,
            'allowed_ips' => '*.*.*.*',
            'responsible_legal_entity_id' => $responsibleLegalEntity->id,
            'databreach_entity_number_counter_id' => $databreachEntityNumberPrefix->id,
            'register_entity_number_counter_id' => $registerEntityNumberPrefix->id,
        ]);
    }

    private function createUser(string $name, string $email, Organisation $organisation): void
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
        ]);
        $user->organisations()->attach($organisation);

        foreach (Role::globalRoles() as $globalRole) {
            $user->assignGlobalRole($globalRole);
        }
        foreach (Role::organisationRoles() as $organisationRole) {
            $user->assignOrganisationRole($organisationRole, $organisation);
        }
    }

    private function createEntityNumberCounter(EntityNumberType $entityNumberType, string $organisationName): EntityNumberCounter
    {
        $exists = EntityNumberCounter::where('prefix', $organisationName)
            ->where('type', $entityNumberType)
            ->exists();

        if ($exists) {
            throw new Exception('Orgnaisation-prefix already exists');
        }

        return EntityNumberCounter::create([
            'type' => $entityNumberType,
            'prefix' => $organisationName,
        ]);
    }
}
