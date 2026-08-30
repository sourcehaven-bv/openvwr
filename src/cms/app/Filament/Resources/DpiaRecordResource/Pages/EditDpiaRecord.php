<?php

declare(strict_types=1);

namespace App\Filament\Resources\DpiaRecordResource\Pages;

use App\Filament\Actions\CloneAction;
use App\Filament\Actions\CreateSnapshotAction;
use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Forms\DraftableForm;
use App\Filament\Notifications\DpiaQualityNotification;
use App\Filament\Resources\DpiaRecordResource;
use App\Models\Dpia\DpiaRecord;
use App\Services\Dpia\DpiaMeasureRiskLinker;
use App\Services\Snapshot\DraftSave;
use Filament\Actions\DeleteAction;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Arrayable;

use function app;

class EditDpiaRecord extends EditRecord
{
    protected static string $resource = DpiaRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
            CloneAction::make(),
            CreateSnapshotAction::makeWithChangesCheck($this->data, $this->savedDataHash),
            DeleteAction::make(),
        ];
    }

    /**
     * The risk selections as they were before saving.
     *
     * The checkbox list is not dehydrated (its keys are repeater state keys,
     * not risk ids), and Filament clears such fields while writing the
     * repeaters. Capturing the selection here keeps it available for
     * {@see afterSave()}.
     *
     * @var array<mixed>
     */
    private array $riskSelectionState = [];

    /**
     * Captures the raw form state before Filament dehydrates it.
     *
     * The mutate hooks only see dehydrated data, which no longer contains the
     * risk selection, so the state is taken straight from the component here.
     */
    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $rawState = $this->form->getRawState();
        $this->riskSelectionState = $rawState instanceof Arrayable ? $rawState->toArray() : $rawState;

        // A DPIA in progress may be saved half-finished; required fields are enforced
        // when a version is created instead. See DraftableForm.
        DraftSave::whileSavingDraft(function () use ($shouldRedirect, $shouldSendSavedNotification): void {
            parent::save($shouldRedirect, $shouldSendSavedNotification);
        });
    }

    protected function makeForm(): Form
    {
        return DraftableForm::make($this);
    }

    /**
     * Links the measures to the risks they address (paragraaf 17).
     *
     * Done after saving because a risk added in the same session only gets its
     * id once the repeaters are written; see {@see DpiaMeasureRiskLinker}.
     */
    protected function afterSave(): void
    {
        $record = $this->getRecord();

        // Narrowing for static analysis: an EditRecord page for this resource
        // only ever holds a DpiaRecord, so this cannot happen at runtime.
        // @codeCoverageIgnoreStart
        if (!$record instanceof DpiaRecord) {
            return;
        }
        // @codeCoverageIgnoreEnd

        app(DpiaMeasureRiskLinker::class)->link($record, $this->riskSelectionState);

        // Advisory only: the save has already happened. A DPIA in progress is
        // allowed to be inconsistent, so this reports rather than intervenes.
        DpiaQualityNotification::sendFor($record);
    }
}
