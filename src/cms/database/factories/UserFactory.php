<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Authorization\Role;
use App\Enums\RegisterLayout;
use App\Enums\Snapshot\MandateholderNotifyBatch;
use App\Enums\Snapshot\MandateholderNotifyDirectly;
use App\Models\Organisation;
use App\Models\OrganisationUserRole;
use App\Models\User;
use App\Models\UserGlobalRole;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'mandateholder_notify_batch' => $this->faker->randomElement(MandateholderNotifyBatch::class),
            'mandateholder_notify_directly' => $this->faker->randomElement(MandateholderNotifyDirectly::class),
            'name' => $this->faker->name(),
            // Not randomised: a random exclusion would make every mail test flaky.
            'notification_exclusions' => [],
            'otp_secret' => $this->faker->regexify('[A-Z]{16}'),
            'otp_confirmed_at' => $this->faker->optional()->dateTime(),
            'otp_timestamp' => $this->faker->numberBetween(),
            'register_layout' => $this->faker->randomElement(RegisterLayout::cases()),
            'remember_token' => Str::random(10),
        ];
    }

    public function hasGlobalRole(Role $role): self
    {
        $userGlobalRoleFactory = UserGlobalRole::factory(['role' => $role]);

        return $this->has($userGlobalRoleFactory, 'globalRoles');
    }

    public function hasOrganisationRole(Role $role, Organisation $organisation): self
    {
        $organisationUserRoleFactory = OrganisationUserRole::factory(['role' => $role])
            ->recycle($organisation);

        return $this->has($organisationUserRoleFactory, 'organisationRoles');
    }

    public function withOrganisation(): self
    {
        return $this->afterCreating(static function (User $user): void {
            Organisation::factory()
                ->hasAttached($user)
                ->create();
        });
    }

    public function withValidOtpRegistration(): self
    {
        return $this->afterMaking(function (User $user): void {
            $user->otp_secret = $this->faker->regexify('[A-Z]{16}');
            $user->otp_confirmed_at = CarbonImmutable::now()->subHour();
        });
    }
}
