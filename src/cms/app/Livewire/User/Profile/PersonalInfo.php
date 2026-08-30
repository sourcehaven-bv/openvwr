<?php

declare(strict_types=1);

namespace App\Livewire\User\Profile;

use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use App\Facades\Authentication;
use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Exceptions\PropertyNotFoundException;
use Webmozart\Assert\Assert;

use function __;
use function collect;
use function view;

class PersonalInfo extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    /** @var ?array<array-key, string> $data */
    public ?array $data = [];

    /** @var array<array-key, string> $only */
    public array $only = ['name', 'email'];
    public User $user;
    protected string $view = 'livewire.personal-info';

    /**
     * @throws PropertyNotFoundException
     */
    public function mount(): void
    {
        $this->user = Authentication::user();

        $form = $this->getPersonalInfoForm();

        $form->fill($this->user->only($this->only));
    }

    public function render(): View
    {
        return view('livewire.user.profile.personal-info');
    }

    /**
     * @return array<array-key, \Filament\Schemas\Components\Group>
     */
    protected function getPersonalInfoFormSchema(): array
    {
        $groupFields = Group::make([
            $this->getNameComponent(),
            $this->getEmailComponent(),
        ])->columnSpan(2);

        return [$groupFields];
    }

    protected function getNameComponent(): TextInput
    {
        return TextInput::make('name')
            ->required()
            ->label(__('user.name'));
    }

    protected function getEmailComponent(): TextInput
    {
        return TextInput::make('email')
            ->disabled()
            ->email()
            ->label(__('user.email'));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->getPersonalInfoFormSchema())->columns(3)
            ->statePath('data');
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function submit(): void
    {
        $form = $this->getPersonalInfoForm();

        $data = collect($form->getState())->only($this->only)->all();
        $this->user->update($data);
        $this->sendNotification();
    }

    protected function sendNotification(): void
    {
        Notification::make()
            ->success()
            ->title(__('user.profile.personal_info.notify'))
            ->send();
    }

    /**
     * @throws PropertyNotFoundException
     */
    private function getPersonalInfoForm(): Schema
    {
        $form = $this->__get('form');
        Assert::isInstanceOf($form, Schema::class);

        return $form;
    }
}
