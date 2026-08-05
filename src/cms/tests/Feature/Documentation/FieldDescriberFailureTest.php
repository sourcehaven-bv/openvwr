<?php

/**
 * Some labels and helper texts are only resolved while the form is being filled
 * in: they live in a closure that needs the current record. Outside a request
 * that record does not exist and Filament throws.
 *
 * For an overview document that is fine - the field simply gets no description -
 * but it must not derail the generator. These tests keep it that way.
 */

declare(strict_types=1);

use App\Documentation\FieldDescriber;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;

beforeEach(function (): void {
    $this->describer = new FieldDescriber();
});

/**
 * A closure that only works inside a form context always fails here.
 */
function docsExplodingClosure(): Closure
{
    return static function (): string {
        throw new RuntimeException('only known while filling in the form');
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
