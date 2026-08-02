<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Components\Uuid\UuidInterface;
use App\Enums\Dpia\DpiaSubjectType;
use App\Filament\Resources\DpiaRecordResource;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Models\Dpia\DpiaRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Webmozart\Assert\Assert;

use function __;
use function redirect;

/**
 * Creates a DPIA from a pre-scan and opens it.
 *
 * The pre-scan already establishes the subject, the linked verwerkingen and
 * whether new legislation is involved; carrying that over means the DPIA does
 * not start from an empty page, and the link back records why the DPIA exists.
 */
class StartDpiaFromPrescanAction extends Action
{
    public static function make(?string $name = 'start_dpia'): static
    {
        return parent::make($name)
            ->name('start_dpia')
            ->label(__('dpia_prescan_record.start_dpia'))
            ->icon('heroicon-o-shield-exclamation')
            ->requiresConfirmation()
            ->modalHeading(__('dpia_prescan_record.start_dpia'))
            ->modalDescription(__('dpia_prescan_record.start_dpia_description'))
            ->modalSubmitActionLabel(__('dpia_prescan_record.start_dpia'))
            ->action(static function (Model $record): mixed {
                Assert::isInstanceOf($record, DpiaPrescanRecord::class);

                $dpiaRecord = self::createDpiaRecord($record);

                Notification::make()
                    ->title(__('dpia_prescan_record.start_dpia_success'))
                    ->success()
                    ->send();

                return redirect(DpiaRecordResource::getUrl('edit', ['record' => $dpiaRecord]));
            });
    }

    private static function createDpiaRecord(DpiaPrescanRecord $prescanRecord): DpiaRecord
    {
        return DB::transaction(static function () use ($prescanRecord): DpiaRecord {
            $dpiaRecord = new DpiaRecord();
            $dpiaRecord->organisation_id = $prescanRecord->organisation_id;
            $dpiaRecord->dpia_prescan_record_id = $prescanRecord->id;
            $dpiaRecord->name = $prescanRecord->name;
            $dpiaRecord->subject_type = $prescanRecord->new_legislation
                ? DpiaSubjectType::REGULATION
                : DpiaSubjectType::PROCESSING;
            $dpiaRecord->proposal_description = $prescanRecord->description;
            // The pre-scan already asked about transfers outside the EEA;
            // paragraaf 8 asks the same thing in more detail.
            $dpiaRecord->outside_eea = $prescanRecord->outside_eea;
            $dpiaRecord->save();

            // Carry the linked verwerkingen over, so the DPIA covers the same
            // scope the pre-scan was about.
            //
            // Cast to string first: the ids are Uuid objects because of the
            // model cast, and sync() uses them as array keys.
            $dpiaRecord->avgResponsibleProcessingRecords()->sync(
                $prescanRecord->avgResponsibleProcessingRecords()
                    ->pluck('avg_responsible_processing_records.id')
                    ->map(static function (mixed $id): string {
                        Assert::isInstanceOf($id, UuidInterface::class);

                        return $id->toString();
                    })
                    ->all(),
            );

            return $dpiaRecord;
        });
    }
}
