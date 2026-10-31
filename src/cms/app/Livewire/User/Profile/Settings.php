<?php

declare(strict_types=1);

namespace App\Livewire\User\Profile;

use App\Enums\Authorization\Permission;
use App\Enums\Authorization\Role;
use App\Enums\Notification\NotificationStream;
use App\Enums\RegisterLayout;
use App\Enums\Snapshot\MandateholderNotifyBatch;
use App\Enums\Snapshot\MandateholderNotifyDirectly;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\Forms\Components\CheckboxList;
use App\Models\OrganisationUserRole;
use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Exceptions\PropertyNotFoundException;
use Webmozart\Assert\Assert;

use function __;
use function array_diff;
use function array_values;
use function collect;
use function in_array;
use function sprintf;
use function view;

class Settings extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    /** @var ?array<array-key, string> $data */
    public ?array $data = [];

    /** @var array<array-key, string> $only */
    public array $only = [
        'mandateholder_notify_batch',
        'mandateholder_notify_directly',
        'register_layout',
    ];
    public User $user;
    protected string $view = 'livewire.settings';

    /**
     * @throws PropertyNotFoundException
     */
    public function mount(): void
    {
        $this->user = Authentication::user();

        $form = $this->getSettingsForm();

        $data = $this->user->only($this->only);
        $data['notification_streams'] = $this->getSubscribedStreamValues();

        $form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                $this->getRegisterLayoutGroup(),
                $this->getNotificationsComponent(),
                $this->getMandateHolderComponent(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.user.profile.settings');
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function submit(): void
    {
        $form = $this->getSettingsForm();

        $state = collect($form->getState());

        $data = $state->only($this->only)->all();

        if ($state->has('notification_streams')) {
            $subscribed = $state->get('notification_streams');
            Assert::isArray($subscribed);

            $data['notification_exclusions'] = $this->getExclusionsFromSubscribed($subscribed);
        }

        $this->user->update($data);

        Notification::make()
            ->success()
            ->title(__('user.profile.settings.notify'))
            ->send();
    }

    /**
     * @throws PropertyNotFoundException
     */
    private function getSettingsForm(): Schema
    {
        $form = $this->__get('form');
        Assert::isInstanceOf($form, Schema::class);

        return $form;
    }

    /**
     * The streams this user can receive, based on the roles they hold in any
     * organisation. Settings are per user, not per organisation.
     *
     * @return array<NotificationStream>
     */
    private function getAvailableStreams(): array
    {
        $roles = $this->user->organisationRoles
            ->map(static fn (OrganisationUserRole $organisationUserRole): Role => $organisationUserRole->role)
            ->unique()
            ->values()
            ->all();

        return NotificationStream::casesForRoles($roles);
    }

    /**
     * Checkboxes show what you receive, storage records what you opted out of.
     *
     * @return array<string>
     */
    private function getSubscribedStreamValues(): array
    {
        $subscribed = [];

        foreach ($this->getAvailableStreams() as $notificationStream) {
            if (!$this->user->receivesNotification($notificationStream)) {
                continue;
            }

            $subscribed[] = $notificationStream->value;
        }

        return $subscribed;
    }

    /**
     * Keep exclusions for streams that were not on offer: a user who loses a
     * role and regains it should not silently be resubscribed.
     *
     * @param array<array-key, mixed> $subscribed
     *
     * @return array<string>
     */
    private function getExclusionsFromSubscribed(array $subscribed): array
    {
        $exclusions = $this->user->notification_exclusions
            ->map(static fn (NotificationStream $notificationStream): string => $notificationStream->value)
            ->all();

        foreach ($this->getAvailableStreams() as $notificationStream) {
            $exclusions = array_values(array_diff($exclusions, [$notificationStream->value]));

            if (in_array($notificationStream->value, $subscribed, true)) {
                continue;
            }

            $exclusions[] = $notificationStream->value;
        }

        return $exclusions;
    }

    private function getNotificationsComponent(): Section
    {
        $availableStreams = $this->getAvailableStreams();

        $options = [];
        foreach ($availableStreams as $notificationStream) {
            $options[$notificationStream->value] = __(sprintf(
                'user.profile.settings.notification_streams_options.%s',
                $notificationStream->value,
            ));
        }

        return Section::make()
            ->visible($availableStreams !== [])
            ->heading(__('user.profile.settings.notifications'))
            ->schema([
                CheckboxList::makeWithValidatedOptions('notification_streams', $options)
                    ->label(__('user.profile.settings.notification_streams'))
                    ->helperText(__('user.profile.settings.notification_streams_helper')),
            ]);
    }

    private function getMandateHolderComponent(): Section
    {
        return Section::make()
            ->visible(Authorization::hasPermission(Permission::USER_PROFILE_SETTINGS_MANDATEHOLDER))
            ->heading(__('user.profile.settings.mandateholder'))
            ->schema([
                Radio::make('mandateholder_notify_directly')
                    ->required()
                    ->label(__('user.profile.settings.mandateholder_notify_directly'))
                    ->options(static function (): array {
                        $options = [];

                        foreach (MandateholderNotifyDirectly::cases() as $mandateholderNotifyDirectly) {
                            $options[$mandateholderNotifyDirectly->value] = __(sprintf(
                                'user.profile.settings.mandateholder_notify_directly_options.%s',
                                $mandateholderNotifyDirectly->value,
                            ));
                        }

                        return $options;
                    }),
                Radio::make('mandateholder_notify_batch')
                    ->required()
                    ->label(__('user.profile.settings.mandateholder_notify_batch'))
                    ->options(static function (): array {
                        $options = [];

                        foreach (MandateholderNotifyBatch::cases() as $mandateholderNotifyBatch) {
                            $options[$mandateholderNotifyBatch->value] = __(sprintf(
                                'user.profile.settings.mandateholder_notify_batch_options.%s',
                                $mandateholderNotifyBatch->value,
                            ));
                        }

                        return $options;
                    }),
            ]);
    }

    private function getRegisterLayoutGroup(): Section
    {
        return Section::make()
            ->heading(__('user.profile.settings.layout'))
            ->schema([
                Radio::make('register_layout')
                    ->required()
                    ->label(__('user.profile.settings.register_layout'))
                    ->options(static function (): array {
                        $options = [];

                        foreach (RegisterLayout::cases() as $registerLayout) {
                            $options[$registerLayout->value] = __(
                                sprintf('user.profile.settings.register_layout_options.%s', $registerLayout->value),
                            );
                        }

                        return $options;
                    }),
            ]);
    }
}
