<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Enums\Authorization\Role;
use App\Enums\Notification\NotificationStream;
use App\Enums\RegisterLayout;
use App\Enums\Snapshot\MandateholderNotifyBatch;
use App\Enums\Snapshot\MandateholderNotifyDirectly;
use App\Livewire\User\Profile\Settings;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('can mount the component', function (): void {
    $user = UserTestHelper::create();

    $this->asFilamentUser()
        ->createLivewireTestable(Settings::class, [
            'user' => $user,
        ])
        ->assertSee(__('user.profile.settings.heading'));
});

it('can submit the form if no mandateholder-permission', function (): void {
    $registerLayout = fake()->randomElement(RegisterLayout::cases());

    $user = UserTestHelper::create();

    $this->asFilamentUser($user)
        ->createLivewireTestable(Settings::class, [
            'user' => $user,
        ])
        ->set('data.register_layout', $registerLayout->value)
        ->call('submit')
        ->assertNotified(__('user.profile.settings.notify'));

    $user->refresh();

    expect($user->register_layout)
        ->toBe($registerLayout);
});

it('can submit the form with the mandateholder-permissions', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [
        Permission::USER_PROFILE_SETTINGS_MANDATEHOLDER,
    ]);

    $mandateholderNotifyBatch = fake()->randomElement(MandateholderNotifyBatch::cases());
    $mandateholderNotifyDirectly = fake()->randomElement(MandateholderNotifyDirectly::cases());
    $registerLayout = fake()->randomElement(RegisterLayout::cases());

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(Settings::class, [
            'user' => $user,
        ])
        ->set('data.mandateholder_notify_batch', $mandateholderNotifyBatch->value)
        ->set('data.mandateholder_notify_directly', $mandateholderNotifyDirectly->value)
        ->set('data.register_layout', $registerLayout->value)
        ->call('submit')
        ->assertNotified(__('user.profile.settings.notify'));

    $user->refresh();

    expect($user->mandateholder_notify_batch)->toBe($mandateholderNotifyBatch)
        ->and($user->mandateholder_notify_directly)->toBe($mandateholderNotifyDirectly)
        ->and($user->register_layout)->toBe($registerLayout);
});

it('hides the notifications section for a role without streams', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $user->assignOrganisationRole(Role::INPUT_PROCESSOR, $organisation);

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(Settings::class, [
            'user' => $user,
        ])
        ->assertDontSee(__('user.profile.settings.notifications'));
});

it('ticks the streams a privacy officer still receives', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, [
        'notification_exclusions' => [NotificationStream::SNAPSHOT_CREATED],
    ]);
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $organisation);

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(Settings::class, [
            'user' => $user,
        ])
        ->assertSee(__('user.profile.settings.notifications'))
        ->assertSet('data.notification_streams', [
            NotificationStream::DOCUMENT_NOTIFY_DATE_REACHED->value,
            NotificationStream::SNAPSHOT_APPROVAL_UPDATED->value,
        ]);
});

it('stores unticked streams as exclusions', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $organisation);

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(Settings::class, [
            'user' => $user,
        ])
        ->set('data.notification_streams', [NotificationStream::SNAPSHOT_CREATED->value])
        ->call('submit')
        ->assertNotified(__('user.profile.settings.notify'));

    $user->refresh();

    expect($user->receivesNotification(NotificationStream::SNAPSHOT_CREATED))->toBeTrue()
        ->and($user->receivesNotification(NotificationStream::DOCUMENT_NOTIFY_DATE_REACHED))->toBeFalse()
        ->and($user->receivesNotification(NotificationStream::SNAPSHOT_APPROVAL_UPDATED))->toBeFalse();
});

it('keeps exclusions for streams the user was not offered', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, [
        'notification_exclusions' => [NotificationStream::DATA_BREACH_AP_REPORTED],
    ]);
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $organisation);

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(Settings::class, [
            'user' => $user,
        ])
        ->call('submit')
        ->assertNotified(__('user.profile.settings.notify'));

    $user->refresh();

    expect($user->receivesNotification(NotificationStream::DATA_BREACH_AP_REPORTED))->toBeFalse();
});
