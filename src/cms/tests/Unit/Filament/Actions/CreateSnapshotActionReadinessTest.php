<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Actions;

use App\Filament\Actions\CreateSnapshotAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use ReflectionMethod;
use Tests\Helpers\LivewireTestHelper;
use Tests\TestCase;

use function __;
use function expect;
use function it;
use function uses;

uses(TestCase::class);

$describeReadiness = static function (Component $livewire): ?string {
    $method = new ReflectionMethod(CreateSnapshotAction::class, 'describeReadiness');

    /** @var string|null $description */
    $description = $method->invoke(null, $livewire);

    return $description;
};

/**
 * A component exposing a wizard as its `form`, mirroring a register edit page.
 *
 * @param array<string, mixed> $state
 */
$componentWithForm = static function (array $state): Component&HasForms {
    $livewire = new class extends Component implements HasForms
    {
        use InteractsWithForms;

        /** @var array<string, mixed> */
        public array $data = [];

        /**
         * @return array<int, mixed>
         */
        protected function getFormSchema(): array
        {
            return [
                Wizard::make()
                    ->schema([
                        Step::make('Naam verwerking')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Naam')
                                    ->required(),
                            ]),
                    ])
                    ->skippable(),
            ];
        }

        protected function getFormStatePath(): ?string
        {
            return 'data';
        }
    };

    $livewire->data = $state;

    return $livewire;
};

it('warns up front which required fields are missing', function () use ($describeReadiness, $componentWithForm): void {
    $description = $describeReadiness($componentWithForm([]));

    expect($description)
        ->toContain(__('snapshot.not_ready_help'))
        ->toContain('Naam');
});

it('does not warn when every required field is filled', function () use ($describeReadiness, $componentWithForm): void {
    expect($describeReadiness($componentWithForm(['name' => 'Verwerking A'])))
        ->toBeNull();
});

/**
 * A component without a `form` cannot be inspected for readiness, so the action must
 * simply carry on rather than fail.
 */
it('does not warn on a component that has no forms at all', function () use ($describeReadiness): void {
    $livewire = new class extends Component
    {
    };

    expect($describeReadiness($livewire))
        ->toBeNull();
});

it('does not warn when the page has no form named form', function () use ($describeReadiness): void {
    $livewire = new class extends Component implements HasForms
    {
        use InteractsWithForms;

        /**
         * @return array<int|string, string|Form>
         */
        protected function getForms(): array
        {
            return [];
        }
    };

    expect($describeReadiness($livewire))
        ->toBeNull();
});

it('resolves the form of a page that has one', function () use ($componentWithForm): void {
    $method = new ReflectionMethod(CreateSnapshotAction::class, 'resolveForm');

    expect($method->invoke(null, $componentWithForm([])))
        ->toBeInstanceOf(Form::class);
});

it('resolves no form for a component that is not a form host', function (): void {
    $livewire = new class extends Component
    {
    };

    $method = new ReflectionMethod(CreateSnapshotAction::class, 'resolveForm');

    expect($method->invoke(null, $livewire))
        ->toBeNull();
});

it('reports an empty form as ready', function () use ($describeReadiness): void {
    expect($describeReadiness(LivewireTestHelper::createTestFormComponent()))
        ->toBeNull();
});

/**
 * The view page carries no form, so there is nothing to check and the version creation
 * must proceed rather than be halted.
 */
it('does not halt version creation on a component without a form', function (): void {
    $livewire = new class extends Component
    {
    };

    $method = new ReflectionMethod(CreateSnapshotAction::class, 'haltOnIncompleteRecord');
    $method->invoke(null, CreateSnapshotAction::make(), $livewire);

    // Reaching this point means the action was not halted.
    expect(true)
        ->toBeTrue();
});
