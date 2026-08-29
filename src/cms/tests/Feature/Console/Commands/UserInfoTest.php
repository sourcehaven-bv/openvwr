<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Enums\Authorization\Role;
use App\Models\User;
use Tests\Helpers\Model\OrganisationTestHelper;

use function fake;
use function it;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

it('can get user info', function (): void {
    $organisation = OrganisationTestHelper::create();

    $globalRole = fake()->randomElement(Role::cases());
    $organisationRole = fake()->randomElement(Role::cases());

    $user = User::factory()
        ->hasGlobalRole($globalRole)
        ->hasOrganisationRole($organisationRole, $organisation)
        ->create();

    $expectedTableRows = [
        ['id', $user->id->toString()],
        ['name', $user->name],
        ['email', $user->email],
        ['created at', $user->created_at->toDateTimeString()],
        ['updated at', $user->updated_at->toDateTimeString()],
        ['otp confirmed at', $user->otp_confirmed_at ?? 'null'],
        ['global roles', $globalRole->value],
        ['organisation roles', sprintf('%s: %s', $organisation->name, $organisationRole->value)],
    ];

    $this->artisan('user:info', ['email' => $user->email])
        ->assertOk()
        ->expectsTable(['Key', 'Value'], $expectedTableRows);
});

it('can not get user info with unknown email', function (): void {
    $this->artisan('user:info', ['email' => fake()->safeEmail()])
        ->assertFailed();
});

it('can not get user info with unknown email as json', function (): void {
    $this->artisan('user:info', [
        'email' => fake()->safeEmail(),
        '--json' => true,
    ])
        ->assertFailed();
});

it('can get user info as json', function (): void {
    $organisation = OrganisationTestHelper::create();

    $globalRole = fake()->randomElement(Role::cases());
    $organisationRole = fake()->randomElement(Role::cases());

    $user = User::factory()
        ->hasGlobalRole($globalRole)
        ->hasOrganisationRole($organisationRole, $organisation)
        ->create();

    $expected = [
        'id' => $user->id->toString(),
        'name' => $user->name,
        'email' => $user->email,
        'created_at' => $user->created_at->toISOString(),
        'updated_at' => $user->updated_at->toISOString(),
        'otp_confirmed_at' => $user->otp_confirmed_at?->toISOString(),
        'global_roles' => [$globalRole->value],
        'organisation_roles' => [
            [
                'organisation' => $organisation->name,
                'roles' => [$organisationRole->value],
            ]],
    ];

    $this->artisan('user:info', [
        'email' => $user->email,
        '--json' => true,
    ])
        ->assertOk()
        ->expectsOutput(json_encode($expected, JSON_THROW_ON_ERROR));
});
