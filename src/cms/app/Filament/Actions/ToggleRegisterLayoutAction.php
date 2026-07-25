<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Enums\RegisterLayout;
use App\Facades\Authentication;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

use function __;

/**
 * Switches the current user between the stepwise and one-page register layout.
 *
 * The preference still lives on the user (users.register_layout), so the choice
 * persists exactly as it does when set from the profile -- this only puts the
 * switch where the layout is actually being used, instead of several clicks
 * away in the profile settings.
 */
class ToggleRegisterLayoutAction extends Action
{
    public static function make(?string $name = 'toggle_register_layout'): static
    {
        return parent::make($name)
            ->label(static function (): string {
                return __(self::targetLayout() === RegisterLayout::ONE_PAGE
                    ? 'user.profile.settings.register_layout_switch_to_one_page'
                    : 'user.profile.settings.register_layout_switch_to_steps');
            })
            ->icon(static function (): string {
                return self::targetLayout() === RegisterLayout::ONE_PAGE
                    ? 'heroicon-o-bars-3'
                    : 'heroicon-o-queue-list';
            })
            ->color('gray')
            // The layout is chosen when the form schema is built, so switching
            // it has to reload the page rather than re-render in place.
            ->action(static function (Action $action, EditRecord|ViewRecord $livewire): void {
                $user = Authentication::user();
                $user->register_layout = self::targetLayout();
                $user->save();

                // Livewire's own URL is the AJAX endpoint, so rebuild the
                // record page URL from the page class instead of the referer.
                $action->redirect(
                    $livewire::getUrl(['record' => $livewire->getRecord()]),
                    navigate: false,
                );
            });
    }

    private static function targetLayout(): RegisterLayout
    {
        return Authentication::user()->register_layout === RegisterLayout::ONE_PAGE
            ? RegisterLayout::STEPS
            : RegisterLayout::ONE_PAGE;
    }
}
