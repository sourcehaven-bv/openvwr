<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Snapshot;

use App\Filament\Forms\DraftableForm;
use App\Filament\Pages\Concerns\EnforcesRequiredFieldsWhenSubmitting;
use App\Filament\Pages\Contracts\SavesConcepts;
use App\Services\Snapshot\SnapshotReadinessService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Tests\Helpers\LivewireTestHelper;
use Tests\TestCase;

use function __;
use function count;
use function expect;
use function it;
use function sprintf;
use function uses;

uses(TestCase::class);

/**
 * A wizard mirroring the register forms: skippable, with a required field on more
 * than one step, so a missing field can be traced back to the step it belongs to.
 *
 * @param array<string, mixed> $state
 */
$readinessForm = static function (array $state = []): Schema {
    $livewire = LivewireTestHelper::createTestFormComponent();
    $livewire->data = $state;

    return DraftableForm::make($livewire)
        ->statePath('data')
        ->schema([
            Wizard::make()
                ->schema([
                    Step::make('Naam verwerking')
                        ->schema([
                            TextInput::make('name')
                                ->label('Naam')
                                ->required(),
                        ]),
                    Step::make('Verwerkingsdoel')
                        ->schema([
                            TextInput::make('goal')
                                ->label('Doel')
                                ->required(),
                            TextInput::make('note')
                                ->label('Toelichting'),
                        ]),
                ])
                ->skippable(),
        ]);
};

/**
 * A form on a page that saves concepts, so the required rule is dropped.
 *
 * @param array<int, mixed> $schema
 */
$conceptForm = static function (array $schema): Schema {
    $livewire = new class extends Component implements HasForms, SavesConcepts
    {
        use EnforcesRequiredFieldsWhenSubmitting;
        use InteractsWithForms;

        /** @var array<string, mixed> */
        public array $data = [];
    };

    return DraftableForm::make($livewire)
        ->statePath('data')
        ->schema($schema);
};

it('reports no missing fields when every required field is filled', function () use ($readinessForm): void {
    $form = $readinessForm(['name' => 'Verwerking A', 'goal' => 'Doel A']);

    expect((new SnapshotReadinessService())->isReadyForSnapshot($form))
        ->toBeTrue();
});

it('names the missing required field and the step it lives on', function () use ($readinessForm): void {
    $form = $readinessForm(['name' => 'Verwerking A', 'goal' => null]);

    $missingRequiredFields = (new SnapshotReadinessService())->getMissingRequiredFields($form);

    expect(count($missingRequiredFields))
        ->toBe(1)
        ->and($missingRequiredFields[0]->label)
        ->toBe('Doel')
        ->and($missingRequiredFields[0]->stepLabel)
        ->toBe('Verwerkingsdoel');
});

it('treats a blank string as a missing required field', function () use ($readinessForm): void {
    $form = $readinessForm(['name' => '   ', 'goal' => 'Doel A']);

    $missingRequiredFields = (new SnapshotReadinessService())->getMissingRequiredFields($form);

    expect(count($missingRequiredFields))
        ->toBe(1)
        ->and($missingRequiredFields[0]->label)
        ->toBe('Naam');
});

it('does not report optional fields as missing', function () use ($readinessForm): void {
    $form = $readinessForm(['name' => 'Verwerking A', 'goal' => 'Doel A', 'note' => null]);

    expect((new SnapshotReadinessService())->getMissingRequiredFields($form))
        ->toBe([]);
});

it('builds a message that names each missing field with its step', function () use ($readinessForm): void {
    $form = $readinessForm([]);

    $snapshotReadinessService = new SnapshotReadinessService();
    $message = $snapshotReadinessService->buildMissingRequiredFieldsMessage(
        $snapshotReadinessService->getMissingRequiredFields($form),
    );

    expect($message)
        ->toContain('Naam')
        ->toContain('Naam verwerking')
        ->toContain('Doel')
        ->toContain('Verwerkingsdoel');
});

it('drops the required rule on a page that saves concepts', function () use ($conceptForm): void {
    $form = $conceptForm([
        TextInput::make('name')
            ->required(),
    ]);

    expect($form->getValidationRules()['data.name'] ?? [])
        ->not->toContain('required');
});

it('keeps the required rule on a page that does not save concepts', function (): void {
    $livewire = LivewireTestHelper::createTestFormComponent();
    $livewire->data = [];

    $form = DraftableForm::make($livewire)
        ->statePath('data')
        ->schema([
            TextInput::make('name')
                ->required(),
        ]);

    expect($form->getValidationRules()['data.name'] ?? [])
        ->toContain('required');
});

it('keeps rules other than required on a page that saves concepts', function () use ($conceptForm): void {
    $form = $conceptForm([
        TextInput::make('name')
            ->required()
            ->maxLength(10),
    ]);

    expect($form->getValidationRules()['data.name'] ?? [])
        ->toContain('max:10')
        ->not->toContain('required');
});

it('names a field without a step by its label alone', function (): void {
    $livewire = LivewireTestHelper::createTestFormComponent();
    $livewire->data = [];

    $form = DraftableForm::make($livewire)
        ->statePath('data')
        ->schema([
            TextInput::make('name')
                ->label('Naam')
                ->required(),
        ]);

    $snapshotReadinessService = new SnapshotReadinessService();
    $missingRequiredFields = $snapshotReadinessService->getMissingRequiredFields($form);

    expect($missingRequiredFields[0]->stepLabel)
        ->toBeNull()
        ->and($snapshotReadinessService->buildMissingRequiredFieldsMessage($missingRequiredFields))
        ->toBe('Naam');
});

it('reads a label that renders as html', function (): void {
    $livewire = LivewireTestHelper::createTestFormComponent();
    $livewire->data = [];

    $form = DraftableForm::make($livewire)
        ->statePath('data')
        ->schema([
            TextInput::make('name')
                ->label(new HtmlString('<b>Naam</b>'))
                ->required(),
        ]);

    $missingRequiredFields = (new SnapshotReadinessService())->getMissingRequiredFields($form);

    expect($missingRequiredFields[0]->label)
        ->toBe('Naam');
});

it('falls back to the generated label when the field has none', function (): void {
    $livewire = LivewireTestHelper::createTestFormComponent();
    $livewire->data = [];

    $form = DraftableForm::make($livewire)
        ->statePath('data')
        ->schema([
            TextInput::make('name')
                ->label('')
                ->required(),
        ]);

    $missingRequiredFields = (new SnapshotReadinessService())->getMissingRequiredFields($form);

    // v4 generates a label from the field name ("name" -> "Name") rather than
    // leaving it empty, so the message names the field the way the form does.
    expect($missingRequiredFields[0]->label)
        ->toBe('Name');
});

it('summarises the remainder when many required fields are missing', function (): void {
    $livewire = LivewireTestHelper::createTestFormComponent();
    $livewire->data = [];

    $fields = [];
    for ($index = 1; $index <= 12; $index++) {
        $fields[] = TextInput::make(sprintf('field_%d', $index))
            ->label(sprintf('Veld %d', $index))
            ->required();
    }

    $form = DraftableForm::make($livewire)
        ->statePath('data')
        ->schema($fields);

    $snapshotReadinessService = new SnapshotReadinessService();
    $message = $snapshotReadinessService->buildMissingRequiredFieldsMessage(
        $snapshotReadinessService->getMissingRequiredFields($form),
    );

    // Only the first ten are listed; the rest are summarised so the message stays readable.
    expect($message)
        ->toContain('Veld 10')
        ->not->toContain('Veld 11')
        ->and($message)
        ->toContain(__('snapshot.incomplete_and_more', ['count' => 2]));
});

it('treats an empty relation selection as a missing required field', function (): void {
    $livewire = LivewireTestHelper::createTestFormComponent();
    $livewire->data = ['responsibles' => []];

    $form = DraftableForm::make($livewire)
        ->statePath('data')
        ->schema([
            CheckboxList::make('responsibles')
                ->label('Verantwoordelijken')
                ->options(['a' => 'A'])
                ->required(),
        ]);

    $missingRequiredFields = (new SnapshotReadinessService())->getMissingRequiredFields($form);

    expect(count($missingRequiredFields))
        ->toBe(1)
        ->and($missingRequiredFields[0]->label)
        ->toBe('Verantwoordelijken');
});

it('reports the section as the step in the one page layout', function (): void {
    $livewire = LivewireTestHelper::createTestFormComponent();
    $livewire->data = [];

    $form = DraftableForm::make($livewire)
        ->statePath('data')
        ->schema([
            Section::make('Verwerkingsdoel')
                ->schema([
                    TextInput::make('goal')
                        ->label('Doel')
                        ->required(),
                ]),
        ]);

    $missingRequiredFields = (new SnapshotReadinessService())->getMissingRequiredFields($form);

    expect(count($missingRequiredFields))
        ->toBe(1)
        ->and($missingRequiredFields[0]->stepLabel)
        ->toBe('Verwerkingsdoel');
});
