<?php

declare(strict_types=1);

namespace App\Filament;

use App\Enums\RegisterLayout;
use App\Facades\Authentication;
use App\Filament\Pages\ProcessingRecordEditRecord;
use App\Filament\Pages\ProcessingRecordViewRecord;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Livewire\Component;

use function app;
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
     */
    private static function isOnePageRegisterPage(): bool
    {
        if (Authentication::user()->register_layout !== RegisterLayout::ONE_PAGE) {
            return false;
        }

        $page = self::getLivewireComponent();

        return $page instanceof ProcessingRecordEditRecord || $page instanceof ProcessingRecordViewRecord;
    }

    private static function getLivewireComponent(): ?Component
    {
        $component = app('livewire')->current();

        return $component instanceof Component ? $component : null;
    }
}
