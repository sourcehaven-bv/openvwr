<?php

declare(strict_types=1);

use App\Documentation\FieldDescriber;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;

/**
 * Sommige labels en hulpteksten worden pas tijdens het invullen bepaald: ze
 * zitten in een closure die de huidige registratie nodig heeft. Buiten een
 * request bestaat die niet en werpt Filament een fout.
 *
 * Voor een overzichtsdocument is dat geen bezwaar - dat veld krijgt dan geen
 * omschrijving - maar het mag de generator niet laten struikelen. Deze tests
 * zorgen dat dat zo blijft.
 */
beforeEach(function (): void {
    $this->describer = new FieldDescriber();
});

/**
 * Een closure die alleen buiten een formuliercontext werkt, faalt hier altijd.
 */
function docsExplodingClosure(): Closure
{
    return static function (): string {
        throw new RuntimeException('alleen bekend tijdens het invullen');
    };
}

it('has no label when determining it fails', function (): void {
    $field = TextInput::make('a')->label(docsExplodingClosure());

    expect($this->describer->label($field))->toBeNull();
});

it('has no heading when determining it fails', function (): void {
    $section = Section::make(docsExplodingClosure());

    expect($this->describer->heading($section))->toBeNull();
});

it('has no description when determining it fails', function (): void {
    $section = Section::make('Kop')->description(docsExplodingClosure());

    expect($this->describer->description($section))->toBe('');
});

it('skips a helper text that cannot be determined', function (): void {
    $field = TextInput::make('a')->helperText(docsExplodingClosure());

    expect($this->describer->help($field))->toBe('');
});

it('skips options that cannot be determined', function (): void {
    $field = Radio::make('a')->options(docsExplodingClosure());

    expect($this->describer->help($field))->toBe('');
});
