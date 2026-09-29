<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Throwable;

use function __;

class DeleteBulkActionWithRelationChecks extends DeleteBulkAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->action(function (): void {
            $this->process(static function (Collection $records) {
                return $records->each(static function (Model $record) {
                    // @codeCoverageIgnoreStart
                    // asserting query-exception in a (test)transaction will not work
                    try {
                        return $record->delete();
                    } catch (Throwable) {
                        Notification::make()
                            ->danger()
                            ->title(__('general.error'))
                            ->body(__('error.delete_abort_constraints_not_empty'))
                            ->send();
                    }
                    // @codeCoverageIgnoreEnd
                });
            });

            $this->success();
        });
    }
}
