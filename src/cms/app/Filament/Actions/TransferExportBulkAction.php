<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Actions\BulkAction;
use App\Enums\Authorization\Permission;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Jobs\TransferExportJob;
use App\Transfer\Export\RelatedItemCollector;
use App\Transfer\ModelGraph;
use App\Transfer\TransferEntityType;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Webmozart\Assert\Assert;

use function __;
use function app;
use function array_keys;
use function count;
use function is_array;
use function is_string;
use function sprintf;

class TransferExportBulkAction extends BulkAction
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'transfer_export')
            ->label(__('transfer.export_action'))
            ->icon('heroicon-o-archive-box-arrow-down')
            ->color('gray')
            ->visible(static fn (): bool => Authorization::hasPermission(Permission::TRANSFER_EXPORT))
            ->modalHeading(__('transfer.export_modal_heading'))
            ->modalDescription(__('transfer.export_modal_description'))
            ->modalSubmitActionLabel(__('transfer.export_submit'))
            ->form(static function (Collection $records): array {
                $groups = app(RelatedItemCollector::class)->collect($records);

                $schema = [
                    Placeholder::make('summary')
                        ->label('')
                        ->content(__('transfer.export_summary', ['count' => $records->count()])),
                ];

                foreach ($groups as $relationName => $group) {
                    $schema[] = CheckboxList::make(sprintf('related.%s', $relationName))
                        ->label($group['type']->label())
                        ->options($group['options'])
                        ->default(array_keys($group['options']))
                        ->bulkToggleable()
                        ->columns(2);
                }

                return $schema;
            })
            ->action(static function (Collection $records, array $data): void {
                $first = $records->first();
                Assert::isInstanceOf($first, Model::class);

                TransferExportJob::dispatch(
                    TransferEntityType::fromModel($first),
                    self::recordIds($records),
                    self::selectedRelated($data['related'] ?? []),
                    Authentication::organisation()->id,
                    Authentication::user()->id,
                );

                Notification::make()
                    ->title(__('transfer.export_started'))
                    ->body(__('transfer.export_started_body', ['count' => count($records)]))
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    /**
     * @param Collection<int|string, Model> $records
     *
     * @return list<string>
     */
    public static function recordIds(Collection $records): array
    {
        $recordIds = [];

        foreach ($records as $record) {
            $recordIds[] = ModelGraph::id($record);
        }

        return $recordIds;
    }

    /**
     * Normalise the form's `related` payload into selected ids keyed by relation name,
     * discarding any malformed entries.
     *
     * @return array<string, list<string>>
     */
    public static function selectedRelated(mixed $related): array
    {
        if (!is_array($related)) {
            return [];
        }

        $selectedRelated = [];

        foreach ($related as $relationName => $ids) {
            if (!is_string($relationName) || !is_array($ids)) {
                continue;
            }

            $selectedIds = [];
            foreach ($ids as $id) {
                if (is_string($id) && $id !== '') {
                    $selectedIds[] = $id;
                }
            }

            $selectedRelated[$relationName] = $selectedIds;
        }

        return $selectedRelated;
    }
}
