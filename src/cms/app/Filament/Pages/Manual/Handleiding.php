<?php

declare(strict_types=1);

namespace App\Filament\Pages\Manual;

use App\Manual\Manual;
use App\Manual\Task;
use App\Manual\Topic;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

/**
 * The front door of the manual: "wat wilt u doen?".
 *
 * Tasks are the entry point, so the landing page is the task overview rather
 * than a table of contents. Search lives here too, because it is the one place
 * that has to look at both layers at once; a result is a link to the page of
 * that task or topic.
 */
class Handleiding extends Page
{
    use ManualPage;

    protected static ?string $slug = 'handleiding';
    protected static string $view = 'filament.manual.handleiding';
    protected static bool $shouldRegisterNavigation = false;

    #[Url(as: 'q', keep: false)]
    public string $search = '';

    public function boot(Manual $manual): void
    {
        $this->bootManualPage($manual);
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
        return $this->manual()->search($this->search);
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }
}
