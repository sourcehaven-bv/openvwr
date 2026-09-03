<?php

declare(strict_types=1);

namespace App\Filament\Resources\DpiaPrescanRecordResource;

use App\Filament\Forms\Components\ProcessingRecordWizard;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

use function __;

class DpiaPrescanRecordResourceForm
{
    /**
     * @return array<string, array<Component>>
     */
    private static function sections(): array
    {
        return [
            'step_general' => DpiaPrescanRecordResourceFormSchemas::getGeneral(),
            'step_grounds' => DpiaPrescanRecordResourceFormSchemas::getGrounds(),
            'step_ap_criteria' => DpiaPrescanRecordResourceFormSchemas::getApCriteria(),
            'step_edpb_criteria' => DpiaPrescanRecordResourceFormSchemas::getEdpbCriteria(),
            'step_transfer' => DpiaPrescanRecordResourceFormSchemas::getTransfer(),
            'step_other' => DpiaPrescanRecordResourceFormSchemas::getOther(),
            'step_outcome' => DpiaPrescanRecordResourceFormSchemas::getOutcome(),
            'step_relations' => DpiaPrescanRecordResourceFormSchemas::getRelations(),
            'step_attachments' => DpiaPrescanRecordResourceFormSchemas::getAttachments(),
        ];
    }

    public static function stepsForm(Schema $form): Schema
    {
        $steps = [];

        foreach (self::sections() as $key => $schema) {
            $steps[] = Step::make(__('dpia_prescan_record.' . $key))->schema($schema);
        }

        return $form
            ->components([
                ProcessingRecordWizard::make()
                    ->schema($steps)
                    ->skippable()
                    ->persistStepInQueryString()
                    ->columnSpanFull(),
            ]);
    }

    public static function onePageForm(Schema $form): Schema
    {
        $sections = [];

        foreach (self::sections() as $key => $schema) {
            $sections[] = Section::make(__('dpia_prescan_record.' . $key))
                ->schema($schema)
                ->extraAttributes(['data-onepage-section' => $key]);
        }

        return $form->components($sections);
    }
}
