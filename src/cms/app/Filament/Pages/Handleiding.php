<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Authorization\Role;
use App\Facades\Authorization;
use App\Manual\Manual;
use App\Manual\Task;
use App\Manual\TaskCapability;
use App\Manual\Topic;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

use function __;
use function array_filter;
use function array_values;

/**
 * The user manual, in the application itself.
 *
 * It reads from App\Manual, which has already dropped everything behind a
 * switched-off feature flag, so this page can never describe a feature the
 * tenant does not have.
 */
class Handleiding extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $slug = 'handleiding';
    protected static string $view = 'filament.pages.handleiding';
    protected static bool $shouldRegisterNavigation = false;

    #[Url(as: 'q', keep: false)]
    public string $search = '';

    private Manual $manual;

    public function boot(Manual $manual): void
    {
        $this->manual = $manual;
    }

    public function getTitle(): string
    {
        return __('general.manual');
    }

    public function getHeading(): string
    {
        return __('general.manual');
    }

    public function manual(): Manual
    {
        return $this->manual;
    }

    public function isSearching(): bool
    {
        return $this->search !== '';
    }

    /**
     * @return array{tasks: array<Task>, topics: array<Topic>}
     */
    public function results(): array
    {
        return $this->manual->search($this->search);
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }

    /**
     * The roles the current user holds in this organisation. Roles never hide a
     * task - knowing who does what is part of understanding the process - they
     * only change the wording around it.
     *
     * @return array<Role>
     */
    public function roles(): array
    {
        return array_values(array_filter(
            Role::cases(),
            static fn (Role $role): bool => Authorization::hasRole($role),
        ));
    }

    public function capabilityFor(Task $task): TaskCapability
    {
        return $task->capabilityFor($this->roles());
    }
}
