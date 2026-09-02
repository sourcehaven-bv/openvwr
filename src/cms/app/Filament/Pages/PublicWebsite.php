<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use App\Config\Feature;
use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
use App\Filament\Forms\Components\MarkdownEditor\MarkdownEditor;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Models\PublicWebsite as PublicWebsiteModel;
use App\Repositories\PublicWebsiteRepository;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Webmozart\Assert\Assert;

use function __;

class PublicWebsite extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 6;
    protected static ?string $slug = 'public-website';
    protected string $view = 'filament.pages.public_website';

    /** @var ?array<array-key, mixed> $data */
    public ?array $data = [];
    private PublicWebsiteModel $publicWebsite;

    public static function canAccess(): bool
    {
        return Authorization::hasPermission(Permission::PUBLIC_WEBSITE_UPDATE);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Feature::publishingEnabled();
    }

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::FUNCTIONAL_MANAGEMENT->value);
    }

    public static function getNavigationLabel(): string
    {
        return __('public_website.public_website');
    }

    public function getTitle(): string
    {
        return __('public_website.public_website');
    }

    public function getHeading(): string
    {
        return __('public_website.public_website');
    }

    public function boot(PublicWebsiteRepository $publicWebsiteRepository): void
    {
        $this->publicWebsite = $publicWebsiteRepository->get();
    }

    public function mount(): void
    {
        $form = $this->getForm('form');
        Assert::isInstanceOf($form, Schema::class);

        $attributesToArray = $this->publicWebsite->attributesToArray();
        Assert::isMap($attributesToArray);

        $form->fill($attributesToArray);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                MarkdownEditor::make('home_content')
                    ->label(__('public_website.home_content')),
            ])
            ->statePath('data');
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $form = $this->getForm('form');
        Assert::isInstanceOf($form, Schema::class);

        $this->publicWebsite->update($form->getState());

        Notification::make()
            ->success()
            ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'))
            ->send();
    }
}
