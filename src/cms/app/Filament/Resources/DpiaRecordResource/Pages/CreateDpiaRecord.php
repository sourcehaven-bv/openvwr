<?php

declare(strict_types=1);

namespace App\Filament\Resources\DpiaRecordResource\Pages;

use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Pages\ConceptEntityNumberCreateRecord;
use App\Filament\Resources\DpiaRecordResource;
use App\Models\Dpia\DpiaRecord;
use App\Services\Dpia\DpiaMeasureRiskLinker;
use Illuminate\Contracts\Support\Arrayable;

use function app;

class CreateDpiaRecord extends ConceptEntityNumberCreateRecord
{
    protected static string $resource = DpiaRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
        ];
    }

    /**
     * The risk selections as they were before saving.
     *
     * @see EditDpiaRecord::$riskSelectionState
     *
     * @var array<mixed>
     */
    private array $riskSelectionState = [];

    public function create(bool $another = false): void
    {
        $rawState = $this->form->getRawState();
        $this->riskSelectionState = $rawState instanceof Arrayable ? $rawState->toArray() : $rawState;

        parent::create($another);
    }

    /**
     * Links the measures to the risks they address (paragraaf 17).
     *
     * @see EditDpiaRecord::afterSave()
     */
    protected function afterCreate(): void
    {
        $record = $this->getRecord();

        // Narrowing for static analysis: a CreateRecord page for this resource
        // only ever holds a DpiaRecord, so this cannot happen at runtime.
        // @codeCoverageIgnoreStart
        if (!$record instanceof DpiaRecord) {
            return;
        }
        // @codeCoverageIgnoreEnd

        app(DpiaMeasureRiskLinker::class)->link($record, $this->riskSelectionState);
    }
}
