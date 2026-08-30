<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Schema;
use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
use App\Models\FgRemark;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Livewire\Exceptions\PropertyNotFoundException;
use Webmozart\Assert\Assert;

use function __;

class FgRemarksWidget extends Widget implements HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public Model $record;

    /** @var array<string, mixed>|null $data */
    public ?array $data = [];

    protected int|string|array $columnSpan = 'full';
    protected string $view = 'filament.widgets.fg-remarks';

    public static function canView(): bool
    {
        return Authorization::hasPermission(Permission::CORE_ENTITY_FG_REMARKS);
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function mount(Model $record): void
    {
        $form = $this->getWidgetForm();

        $fgRemark = $record->getAttribute('fgRemark');

        /** @var array<string, mixed>|null $state */
        $state = $fgRemark instanceof FgRemark ? $fgRemark->toArray() : null;

        $form->fill($state);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Textarea::make('body')
                    ->label(__('general.fg_remarks'))
                    ->columnSpanFull()
                    ->autosize(),
            ]);
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function submit(): void
    {
        $this->updateRecord($this->getWidgetForm()->getState());

        Notification::make()
            ->success()
            ->title(__('general.saved'))
            ->send();
    }

    /**
     * @throws PropertyNotFoundException
     */
    private function getWidgetForm(): Schema
    {
        $form = $this->__get('form');
        Assert::isInstanceOf($form, Schema::class);

        return $form;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function updateRecord(array $attributes): void
    {
        $model = $this->record;
        Assert::methodExists($model, 'fgRemark');

        /** @var MorphOne<FgRemark, Model> $fgRemarkRelation */
        $fgRemarkRelation = $model->fgRemark();

        /** @var FgRemark $fgRemark */
        $fgRemark = $fgRemarkRelation->firstOrCreate();
        $fgRemark->fill($attributes);

        $fgRemarkRelation->save($fgRemark);
    }
}
