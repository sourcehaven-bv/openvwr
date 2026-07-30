<?php

declare(strict_types=1);

use App\Documentation\FieldDescriber;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\HtmlString;

beforeEach(function (): void {
    $this->describer = new FieldDescriber();
});

it('names the kind of input in plain language', function (string $expected, callable $make): void {
    expect($this->describer->kind($make()))->toBe($expected);
})->with([
    'tekst' => ['Tekst', fn () => TextInput::make('a')],
    'toelichting' => ['Toelichting', fn () => Textarea::make('a')],
    'ja/nee via toggle' => ['Ja/nee', fn () => Toggle::make('a')],
    'ja/nee via checkbox' => ['Ja/nee', fn () => Checkbox::make('a')],
    'datum' => ['Datum', fn () => DatePicker::make('a')],
    'keuze via radio' => ['Keuze', fn () => Radio::make('a')],
    'keuze via select' => ['Keuze', fn () => Select::make('a')],
    'meerkeuze' => ['Meerkeuze', fn () => CheckboxList::make('a')],
    'bestand' => ['Bestand', fn () => FileUpload::make('a')],
    'lijst' => ['Lijst', fn () => Repeater::make('a')],
]);

it('falls back to text for an unknown component', function (): void {
    expect($this->describer->kind(Grid::make()))->toBe('Tekst');
});

it('calls a lookup a koppeling instead of a choice list', function (): void {
    $component = new class ('a') extends Select {
    };

    // De klassenaam bepaalt of iets een koppeling is; deze heet geen lookup.
    expect($this->describer->kind($component))->toBe('Keuze');
});

it('reads the label of a field', function (): void {
    $field = TextInput::make('a')->label('Naam verwerking');

    expect($this->describer->label($field))->toBe('Naam verwerking');
});

it('has no label for a layout component', function (): void {
    expect($this->describer->label(Grid::make()))->toBeNull();
});

it('has no label when the field has none', function (): void {
    expect($this->describer->label(TextInput::make('a')->label('')))->toBeNull();
});

it('strips markup from a label', function (): void {
    $field = TextInput::make('a')->label(new HtmlString('<b>Vet</b> label'));

    expect($this->describer->label($field))->toBe('Vet label');
});

it('reads the heading of a section', function (): void {
    expect($this->describer->heading(Section::make('Beveiliging')))->toBe('Beveiliging');
});

it('has no heading for something that is not a section', function (): void {
    expect($this->describer->heading(TextInput::make('a')))->toBeNull();
});

it('has no heading when the section has none', function (): void {
    expect($this->describer->heading(Section::make()))->toBeNull();
});

it('reads the description under a section heading', function (): void {
    $section = Section::make('Kop')->description('Beantwoord de vragen.');

    expect($this->describer->description($section))->toBe('Beantwoord de vragen.');
});

it('has no description for something that is not a section', function (): void {
    expect($this->describer->description(TextInput::make('a')))->toBe('');
});

it('has no description when the section has none', function (): void {
    expect($this->describer->description(Section::make('Kop')))->toBe('');
});

it('uses the helper text as the explanation', function (): void {
    $field = TextInput::make('a')->helperText('Zo vult u dit in.');

    expect($this->describer->help($field))->toBe('Zo vult u dit in.');
});

it('has no explanation for a layout component', function (): void {
    expect($this->describer->help(Grid::make()))->toBe('');
});

it('adds the options of a choice list to the explanation', function (): void {
    $field = Radio::make('a')->options(['ja' => 'Ja', 'nee' => 'Nee']);

    expect($this->describer->help($field))->toBe('Keuze uit: Ja; Nee.');
});

it('combines the helper text with the options', function (): void {
    $field = Radio::make('a')
        ->helperText('Kies er een.')
        ->options(['ja' => 'Ja']);

    expect($this->describer->help($field))->toBe('Kies er een. Keuze uit: Ja.');
});

it('skips grouped options', function (): void {
    $field = Select::make('a')->options(['groep' => ['x' => 'X']]);

    // Gegroepeerde opties komen als array binnen; die slaan we over.
    expect($this->describer->help($field))->toBe('');
});

it('collapses whitespace into a single line', function (): void {
    expect($this->describer->tidy("een\n  lange   regel\n"))->toBe('een lange regel');
});
