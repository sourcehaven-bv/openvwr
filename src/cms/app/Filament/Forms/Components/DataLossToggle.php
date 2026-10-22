<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Livewire\Component;
use Webmozart\Assert\Assert;

use function __;
use function filled;
use function is_array;
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

                    $componentKey = $component->getKey();
                    Assert::string($componentKey);

                    self::mountConfirmation(
                        $livewire,
                        $componentKey,
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
     * Filament v5 identifies field actions by schema key, rather than state path.
     */
    private static function mountConfirmation(Component $livewire, string $componentKey, string $actionName): void
    {
        // Every Filament page that renders this field implements HasActions; this guard is
        // unreachable in practice; it only keeps the call type-safe.
        // @codeCoverageIgnoreStart
        if (!$livewire instanceof HasActions) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $livewire->mountAction($actionName, context: ['schemaComponent' => $componentKey]);
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
