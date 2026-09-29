<?php

declare(strict_types=1);

use App\Enums\Import\MappingConfidence;
use App\Enums\Import\MappingTransform;
use App\Import\Mapping\DateFormatDetector;
use App\Import\Mapping\FieldOptions;
use App\Import\Mapping\MappingEngine;
use App\Import\Mapping\MappingField;
use App\Import\Mapping\MappingProfile;
use App\Models\DataBreachRecord;
use Carbon\CarbonImmutable;

function engine(): MappingEngine
{
    return new MappingEngine(new DateFormatDetector(), new FieldOptions());
}

function profileWith(MappingField ...$fields): MappingProfile
{
    return new MappingProfile(DataBreachRecord::class, $fields);
}

function field(string $source, string $target, MappingTransform $transform): MappingField
{
    return new MappingField($source, $target, $transform, MappingConfidence::Manual);
}

it('renames a source column to the target attribute', function (): void {
    $profile = profileWith(field('Samenvatting', 'summary', MappingTransform::Text));

    expect(engine()->apply($profile, ['Samenvatting' => 'Mail verkeerd verstuurd']))
        ->toBe(['summary' => 'Mail verkeerd verstuurd']);
});

it('reads dutch booleans', function (string $input, bool $expected): void {
    $profile = profileWith(field('Gemeld', 'ap_reported', MappingTransform::Boolean));

    expect(engine()->apply($profile, ['Gemeld' => $input]))
        ->toBe(['ap_reported' => $expected]);
})->with([
    ['ja', true],
    ['Ja', true],
    ['nee', false],
    ['NEE', false],
    ['J', true],
    ['n', false],
    ['true', true],
    ['false', false],
]);

it('leaves an unrecognised boolean empty rather than guessing no', function (): void {
    $profile = profileWith(field('Gemeld', 'ap_reported', MappingTransform::Boolean));

    expect(engine()->apply($profile, ['Gemeld' => 'misschien']))
        ->toBe(['ap_reported' => null]);
});

it('splits a multi-value cell', function (): void {
    $profile = profileWith(field('Categorieen', 'personal_data_categories', MappingTransform::StringList));

    expect(engine()->apply($profile, ['Categorieen' => "Naam\nE-mailadres"]))
        ->toBe(['personal_data_categories' => ['Naam', 'E-mailadres']]);
});

it('splits a comma-separated list when the cell has no line breaks', function (): void {
    // OpenVWR's own export writes lists this way.
    $profile = profileWith(field('Categorieen', 'personal_data_categories', MappingTransform::StringList));

    expect(engine()->apply($profile, ['Categorieen' => 'Naam, E-mailadres, Adres en woonplaats']))
        ->toBe(['personal_data_categories' => ['Naam', 'E-mailadres', 'Adres en woonplaats']]);
});

it('keeps a comma inside the lines of a line-separated list', function (): void {
    $profile = profileWith(field('Categorieen', 'personal_data_categories', MappingTransform::StringList));

    expect(engine()->apply($profile, ['Categorieen' => "Adres, woonplaats\nNaam"]))
        ->toBe(['personal_data_categories' => ['Adres, woonplaats', 'Naam']]);
});

it('keeps an already structured list', function (): void {
    $profile = profileWith(field('Categorieen', 'personal_data_categories', MappingTransform::StringList));

    expect(engine()->apply($profile, ['Categorieen' => ['Naam', 'Adres']]))
        ->toBe(['personal_data_categories' => ['Naam', 'Adres']]);
});

it('converts integers and rejects non-numeric text', function (): void {
    $profile = profileWith(field('Versie', 'version', MappingTransform::Integer));
    $engine = engine();

    expect($engine->apply($profile, ['Versie' => '7']))->toBe(['version' => 7])
        ->and($engine->apply($profile, ['Versie' => 'zeven']))->toBe(['version' => null]);
});

it('treats a blank cell as no value', function (): void {
    $profile = profileWith(field('Samenvatting', 'summary', MappingTransform::Text));

    expect(engine()->apply($profile, ['Samenvatting' => '   ']))
        ->toBe(['summary' => null]);
});

it('reads a dot-notation source path', function (): void {
    $profile = profileWith(field('Melding.Samenvatting', 'summary', MappingTransform::Text));

    expect(engine()->apply($profile, ['Melding' => ['Samenvatting' => 'Datalek']]))
        ->toBe(['summary' => 'Datalek']);
});

it('turns a yes into the chosen date and a no into nothing', function (): void {
    $profile = profileWith(new MappingField(
        'Melding AP',
        'ap_reported_at',
        MappingTransform::BooleanToDate,
        MappingConfidence::Manual,
        '2026-06-02T00:00:00',
    ));
    $engine = engine();

    expect($engine->apply($profile, ['Melding AP' => 'ja']))
        ->toBe(['ap_reported_at' => '2026-06-02T00:00:00'])
        ->and($engine->apply($profile, ['Melding AP' => 'nee']))
        ->toBe(['ap_reported_at' => null]);
});

it('falls back to the import date when no fixed date is chosen', function (): void {
    $profile = profileWith(new MappingField(
        'Melding AP',
        'ap_reported_at',
        MappingTransform::BooleanToDate,
        MappingConfidence::Manual,
    ));

    $result = engine()->apply($profile, ['Melding AP' => 'ja']);

    expect($result['ap_reported_at'])->toStartWith(CarbonImmutable::now()->format('Y-m-d'));
});

it('ignores source columns that are not in the profile', function (): void {
    $profile = profileWith(field('Samenvatting', 'summary', MappingTransform::Text));

    expect(engine()->apply($profile, ['Samenvatting' => 'X', 'Melder' => 'Jan Jansen']))
        ->toBe(['summary' => 'X']);
});

it('parses dates day-first against the configured formats', function (string $input, string $expected): void {
    $profile = profileWith(field('Datum', 'reported_at', MappingTransform::Date));

    expect(engine()->apply($profile, ['Datum' => $input]))
        ->toBe(['reported_at' => $expected]);
})->with([
    'iso with time' => ['2026-03-04T00:00:00', '2026-03-04T00:00:00'],
    'iso date' => ['2026-03-04', '2026-03-04T00:00:00'],
    'dutch dashes' => ['04-03-2026', '2026-03-04T00:00:00'],
    'dutch dashes without leading zeros' => ['4-3-2026', '2026-03-04T00:00:00'],
    'dutch slashes' => ['01/02/2026', '2026-02-01T00:00:00'],
    'dutch with time' => ['04-03-2026 13:45', '2026-03-04T13:45:00'],
]);

it('leaves a value that is not a date empty instead of guessing', function (string $input): void {
    $profile = profileWith(field('Datum', 'reported_at', MappingTransform::Date));

    expect(engine()->apply($profile, ['Datum' => $input]))
        ->toBe(['reported_at' => null]);
})->with([
    'text' => ['n.v.t.'],
    'impossible day' => ['31-02-2026'],
    'american order is not accepted silently' => ['13/01/2026x'],
]);

it('leaves a value it cannot read empty', function (MappingTransform $transform, mixed $input): void {
    $profile = profileWith(field('Kolom', 'summary', $transform));

    expect(engine()->apply($profile, ['Kolom' => $input]))
        ->toBe(['summary' => null]);
})->with([
    'date from blank' => [MappingTransform::Date, '   '],
    'text from a nested array' => [MappingTransform::Text, ['a' => 'b']],
    'text from an object' => [MappingTransform::Text, new stdClass()],
    'integer from blank' => [MappingTransform::Integer, ' '],
    'boolean from blank' => [MappingTransform::Boolean, ' '],
    'list from blank lines' => [MappingTransform::StringList, "\n \n"],
]);

it('passes a real boolean through', function (): void {
    $profile = profileWith(field('Gemeld', 'ap_reported', MappingTransform::Boolean));

    expect(engine()->apply($profile, ['Gemeld' => true]))
        ->toBe(['ap_reported' => true]);
});

it('reads a list that already is an array', function (): void {
    $profile = profileWith(field('Lijst', 'personal_data_categories', MappingTransform::StringList));

    expect(engine()->apply($profile, ['Lijst' => ['Naam', '', ' Adres ']]))
        ->toBe(['personal_data_categories' => ['Naam', 'Adres']]);
});

it('reads every date of a column in the format decided for that column', function (): void {
    $field = new MappingField('Datum', 'reported_at', MappingTransform::Date, MappingConfidence::Manual, dateFormat: 'm-d-Y');

    expect(engine()->apply(profileWith($field), ['Datum' => '04-03-2026']))
        ->toBe(['reported_at' => '2026-04-03T00:00:00'])
        ->and(engine()->apply(profileWith($field), ['Datum' => '13-03-2026']))
        ->toBe(['reported_at' => null]);
});

it('reads whole numbers', function (): void {
    $profile = profileWith(field('Aantal', 'affected_count', MappingTransform::Integer));

    expect(engine()->apply($profile, ['Aantal' => '42']))->toBe(['affected_count' => 42])
        ->and(engine()->apply($profile, ['Aantal' => 'veel']))->toBe(['affected_count' => null]);
});
