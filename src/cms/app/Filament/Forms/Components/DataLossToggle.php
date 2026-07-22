<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Component;

use function __;
use function filled;
use function is_array;
use function method_exists;
use function sprintf;

/**
 * A toggle that asks for confirmation before it discards related data.
 *
 * Switching one of these off makes the observer delete the related records on save
 * (see AvgResponsibleProcessingRecordObserver::resetProcessors and friends). The
 * confirmation only appears when there is something to lose: toggling an empty
 * section off is silent, so the common case stays frictionless.
 *
 * Filament's modal cancel button cannot run a callback (it takes a StaticAction),
 * so the toggle is switched back on *before* the modal opens and only switched off
 * again when the user confirms. Dismissing the modal therefore leaves the toggle on,
 * which is the safe default.
 */
class DataLossToggle extends Toggle
{
    /**
     * @param array<string> $dependentFields fields whose contents the observer discards on save
     */
    public static function makeWithConfirmation(string $name, array $dependentFields, string $warning): static
    {
        $toggle = static::make($name);

        return $toggle
            ->live()
            ->afterStateUpdated(
                static function (
                    ?bool $state,
                    Get $get,
                    Set $set,
                    Component $livewire,
                    Toggle $component,
                ) use (
                    $name,
                    $dependentFields,
                ): void {
                    if ($state !== false || !self::hasDependentData($get, $dependentFields)) {
                        return;
                    }

                    // Revert first: dismissing the modal must not discard anything.
                    $set($name, true);

                    self::mountConfirmation(
                        $livewire,
                        $component->getStatePath(),
                        self::confirmActionName($name),
                    );
                },
            )
            ->registerActions([
                Action::make(self::confirmActionName($name))
                    ->modalHeading(__('general.data_loss_confirm_title'))
                    ->modalDescription($warning)
                    ->modalSubmitActionLabel(__('general.data_loss_confirm_submit'))
                    ->modalCancelActionLabel(__('general.data_loss_confirm_cancel'))
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->action(static function (Set $set) use ($name): void {
                        $set($name, false);
                    }),
            ]);
    }

    public static function confirmActionName(string $name): string
    {
        return sprintf('confirm_%s_data_loss', $name);
    }

    /**
     * Opens the confirmation modal.
     *
     * Filament mounts form component actions through HasFormComponentActions, a trait
     * on the Livewire page component rather than an interface, so there is no type to
     * hint against here.
     */
    private static function mountConfirmation(Component $livewire, string $statePath, string $actionName): void
    {
        // Every Filament page that renders a form has this trait, so this guard is
        // unreachable in practice; it only keeps the call type-safe.
        // @codeCoverageIgnoreStart
        if (!method_exists($livewire, 'mountFormComponentAction')) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $livewire->mountFormComponentAction($statePath, $actionName);
    }

    /**
     * @param array<string> $dependentFields
     */
    private static function hasDependentData(Get $get, array $dependentFields): bool
    {
        foreach ($dependentFields as $field) {
            $value = $get($field);

            if (is_array($value)) {
                if ($value !== []) {
                    return true;
                }

                continue;
            }

            // Only relationship fields (always arrays) are wired up today; this branch
            // keeps scalar dependent fields working when one is added.
            // @codeCoverageIgnoreStart
            if (filled($value)) {
                return true;
            }
            // @codeCoverageIgnoreEnd
        }

        return false;
    }
}
