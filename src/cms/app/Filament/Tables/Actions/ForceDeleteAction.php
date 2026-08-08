<?php

declare(strict_types=1);

namespace App\Filament\Tables\Actions;

use App\Services\Cleanup\SoftDeleteCleaner;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Throwable;

use function __;
use function app;

/**
 * Rij-actie: een verwijderd record definitief opruimen zonder de bewaartermijn
 * af te wachten (art. 17 AVG, recht op verwijdering).
 *
 * Loopt via SoftDeleteCleaner, zodat de bijbehorende bestanden in de
 * documentopslag net zo goed worden verwijderd als bij de geplande
 * opschoontaak -- een kale forceDelete() laat die bestanden achter.
 */
class ForceDeleteAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'force_delete';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('general.force_delete'));
        $this->icon('heroicon-o-trash');
        $this->color('danger');
        $this->hiddenLabel();
        $this->tooltip(static fn (Action $action) => $action->getLabel());
        $this->requiresConfirmation();
        $this->modalHeading(__('general.force_delete_confirm_title'));
        $this->modalDescription(__('general.force_delete_confirm_description'));
        $this->modalSubmitActionLabel(__('general.force_delete_confirm_submit'));

        // Alleen zinvol op een al verwijderd record: dit slaat de bewaartermijn
        // over, het vervangt de gewone verwijderactie niet.
        $this->visible(static fn (Model $record): bool => $record->getAttribute('deleted_at') !== null);

        // Autorisatie via de policy; BasePolicy::forceDelete() volgt delete().
        $this->authorize('forceDelete');

        $this->action(function (Model $record): void {
            try {
                app(SoftDeleteCleaner::class)->forceDeleteRecord($record);
                // @codeCoverageIgnoreStart
                // een query-exception afdwingen lukt niet binnen een
                // (test)transactie -- zelfde reden als bij
                // DeleteBulkActionWithRelationChecks
            } catch (Throwable) {
                Notification::make()
                    ->danger()
                    ->title(__('general.error'))
                    ->body(__('error.delete_abort_constraints_not_empty'))
                    ->send();

                $this->halt();
                // @codeCoverageIgnoreEnd
            }

            Notification::make()
                ->title(__('general.force_deleted'))
                ->success()
                ->send();
        });
    }
}
