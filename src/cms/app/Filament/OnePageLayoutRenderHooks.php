<?php

declare(strict_types=1);

namespace App\Filament;

use App\Enums\RegisterLayout;
use App\Facades\Authentication;
use App\Filament\Resources\AlgorithmRecordResource;
use App\Filament\Resources\AvgProcessorProcessingRecordResource;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use App\Filament\Resources\DataBreachRecordResource;
use App\Filament\Resources\WpgProcessingRecordResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Livewire\Component;

use function app;
use function in_array;
use function view;

/**
 * Wraps the one-page register layout in a grid so the quick-jump navigation
 * can sit beside the form, and renders that navigation.
 *
 * This is done with render hooks rather than by overriding Filament's
 * edit-record/view-record views: those views are non-trivial, and copying them
 * into the project means re-reconciling them on every Filament upgrade. The
 * hooks either side of the page slot give the same wrapping for free.
 */
class OnePageLayoutRenderHooks
{
    /**
     * The registers that render their record pages with the one-page layout.
     */
    private const ONE_PAGE_RESOURCES = [
        AlgorithmRecordResource::class,
        AvgProcessorProcessingRecordResource::class,
        AvgResponsibleProcessingRecordResource::class,
        DataBreachRecordResource::class,
        WpgProcessingRecordResource::class,
    ];

    public static function register(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_HEADER_WIDGETS_AFTER,
            static function (): string {
                if (!self::isOnePageRegisterPage()) {
                    return '';
                }

                return '<div class="onepage-layout">';
            },
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_FOOTER_WIDGETS_BEFORE,
            static function (): string {
                if (!self::isOnePageRegisterPage()) {
                    return '';
                }

                return '<div class="onepage-nav-ctn">'
                    . view('filament.forms.components.onepage-nav')->render()
                    . '</div></div>';
            },
        );
    }

    /**
     * The navigation is only useful where the one-page layout actually renders:
     * a register record page, for a user who selected that layout.
     *
     * Matching on the record-page contract rather than on the project's own
     * base classes: not every register page extends those, and the layout is
     * chosen per resource, not per base class. Create pages count too: the
     * resource builds the same one-page schema there, so without this the new
     * record form loses the section styling the edit form has.
     */
    private static function isOnePageRegisterPage(): bool
    {
        if (Authentication::user()->register_layout !== RegisterLayout::ONE_PAGE) {
            return false;
        }

        $page = self::getLivewireComponent();

        if (!$page instanceof EditRecord && !$page instanceof ViewRecord && !$page instanceof CreateRecord) {
            return false;
        }

        return self::rendersOnePageLayout($page);
    }

    /**
     * A resource opts in by exposing sections carrying the one-page anchor,
     * which is what the navigation reads. Resources without them (users,
     * organisations, lookup lists) are left untouched.
     */
    private static function rendersOnePageLayout(CreateRecord|EditRecord|ViewRecord $page): bool
    {
        return in_array($page::getResource(), self::ONE_PAGE_RESOURCES, true);
    }

    private static function getLivewireComponent(): ?Component
    {
        $component = app('livewire')->current();

        return $component instanceof Component ? $component : null;
    }
}
