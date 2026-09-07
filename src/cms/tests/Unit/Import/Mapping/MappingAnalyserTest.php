<?php

declare(strict_types=1);

use App\Enums\Import\ImportTarget;
use App\Enums\Import\MappingConfidence;
use App\Enums\Import\MappingTransform;
use App\Import\Mapping\MappingAnalyser;
use App\Import\Mapping\MappingField;
use App\Import\Mapping\MappingProfile;
use Tests\TestCase;

uses(TestCase::class);

/**
 * @param array<int, MappingField> $fields
 */
function targetFor(array $fields, string $source): ?MappingField
{
    foreach ($fields as $field) {
        if ($field->source === $source) {
            return $field;
        }
    }

    return null;
}

it('matches a column on the dutch field label', function (): void {
    // 'Datum melding' is the label of reported_at in resources/lang/nl.
    $profile = $this->app->get(MappingAnalyser::class)->analyse(ImportTarget::DataBreachRecord, ['Datum melding']);

    expect(targetFor($profile->fields, 'Datum melding')?->target)->toBe('reported_at');
});

it('matches a column on the attribute name', function (): void {
    $profile = $this->app->get(MappingAnalyser::class)->analyse(ImportTarget::DataBreachRecord, ['summary']);

    $field = targetFor($profile->fields, 'summary');

    expect($field?->target)->toBe('summary')
        ->and($field?->confidence)->toBe(MappingConfidence::Exact);
});

it('ignores case and punctuation when matching labels', function (): void {
    $profile = $this->app->get(MappingAnalyser::class)->analyse(ImportTarget::DataBreachRecord, ['DATUM  MELDING']);

    expect(targetFor($profile->fields, 'DATUM  MELDING')?->target)->toBe('reported_at');
});

it('derives the transform from the model cast', function (): void {
    $profile = $this->app->get(MappingAnalyser::class)->analyse(ImportTarget::DataBreachRecord, [
        'Datum melding',
        'Gemeld aan de autoriteit persoonsgegevens (AP)',
        'Categorieën van persoonsgegevens',
        'Samenvatting incident',
    ]);

    expect(targetFor($profile->fields, 'Datum melding')?->transform)->toBe(MappingTransform::Date)
        ->and(targetFor($profile->fields, 'Gemeld aan de autoriteit persoonsgegevens (AP)')?->transform)
        ->toBe(MappingTransform::Boolean)
        ->and(targetFor($profile->fields, 'Categorieën van persoonsgegevens')?->transform)
        ->toBe(MappingTransform::StringList)
        ->and(targetFor($profile->fields, 'Samenvatting incident')?->transform)
        ->toBe(MappingTransform::Text);
});

it('puts unrecognised columns on the unmapped list instead of guessing', function (): void {
    $profile = $this->app->get(MappingAnalyser::class)->analyse(ImportTarget::DataBreachRecord, ['Melder', 'Afdeling']);

    expect($profile->unmapped)->toBe(['Melder', 'Afdeling'])
        ->and($profile->fields)->toBeEmpty();
});

it('does not map two columns onto the same attribute', function (): void {
    $profile = $this->app->get(MappingAnalyser::class)->analyse(ImportTarget::DataBreachRecord, ['Samenvatting incident', 'summary']);

    $targets = array_map(static fn (MappingField $field): string => $field->target, $profile->fields);

    expect($targets)->toBe(array_unique($targets));
});

it('uses the values to pick between similarly named fields', function (): void {
    // "Melding AP" resembles both ap_reported and ap_reported_at; the yes/no
    // values decide it belongs to the boolean one.
    $rows = [
        ['Melding AP' => 'ja'],
        ['Melding AP' => 'nee'],
    ];

    $profile = $this->app->get(MappingAnalyser::class)
        ->analyse(ImportTarget::DataBreachRecord, ['Melding AP'], $rows);

    expect(targetFor($profile->fields, 'Melding AP')?->target)->toBe('ap_reported');
});

it('matches through synonyms', function (): void {
    $rows = [['Soort melding' => 'Definitief'], ['Soort melding' => 'Voorlopig']];

    $profile = $this->app->get(MappingAnalyser::class)
        ->analyse(ImportTarget::DataBreachRecord, ['Soort melding'], $rows);

    expect(targetFor($profile->fields, 'Soort melding')?->target)->toBe('type');
});

it('leaves a column unmapped rather than forcing a weak match', function (): void {
    // "Melder" must not be pulled onto "Maatregelen" on spelling alone: the
    // reporter's identity is exactly what should not travel along.
    $rows = [['Melder' => 'J. de Vries'], ['Melder' => 'A. Bakker']];

    $profile = $this->app->get(MappingAnalyser::class)
        ->analyse(ImportTarget::DataBreachRecord, ['Melder'], $rows);

    expect($profile->unmapped)->toContain('Melder')
        ->and($profile->fields)->toBeEmpty();
});

it('produces a stable fingerprint regardless of column order', function (): void {
    $one = MappingProfile::fingerprint(['Naam', 'Datum melding']);
    $two = App\Import\Mapping\MappingProfile::fingerprint(['Datum melding', 'Naam']);

    expect($one)->toBe($two);
});

it('ignores a heading that holds no letters or digits', function (): void {
    /** @var MappingAnalyser $analyser */
    $analyser = $this->app->get(MappingAnalyser::class);
    $profile = $analyser->analyse(ImportTarget::DataBreachRecord, ['???'], [['???' => 'ja']]);

    expect(targetFor($profile->fields, '???'))->toBeNull()
        ->and($profile->unmapped)->toContain('???');
});

it('judges a column by a limited sample and skips nested values', function (): void {
    $rows = [];
    for ($i = 0; $i < 8; $i++) {
        $rows[] = ['Samenvatting incident' => sprintf('Incident %d', $i), 'Adres' => ['straat' => 'Kerkstraat']];
    }

    /** @var MappingAnalyser $analyser */
    $analyser = $this->app->get(MappingAnalyser::class);
    $profile = $analyser->analyse(ImportTarget::DataBreachRecord, ['Samenvatting incident', 'Adres'], $rows);

    expect(targetFor($profile->fields, 'Samenvatting incident')?->target)->toBe('summary');
});

it('does not let blank cells count as evidence for a type', function (): void {
    /** @var MappingAnalyser $analyser */
    $analyser = $this->app->get(MappingAnalyser::class);
    $profile = $analyser->analyse(ImportTarget::DataBreachRecord, ['Naam'], [['Naam' => ' '], ['Naam' => 'Echt']]);

    expect(targetFor($profile->fields, 'Naam')?->target)->toBe('name');
});

it('offers nothing when a heading fits several fields about equally well', function (): void {
    /** @var MappingAnalyser $analyser */
    $analyser = $this->app->get(MappingAnalyser::class);
    // "Gegevens categorie" fits the type field and the nature-of-incident field
    // equally; a wrong suggestion is harder to spot than an empty one.
    $profile = $analyser->analyse(ImportTarget::DataBreachRecord, ['Gegevens categorie'], [['Gegevens categorie' => 'Een stuk tekst']]);

    expect(targetFor($profile->fields, 'Gegevens categorie'))->toBeNull()
        ->and($profile->unmapped)->toContain('Gegevens categorie');
});

it('records the date format of a column when the values leave no doubt', function (): void {
    /** @var MappingAnalyser $analyser */
    $analyser = $this->app->get(MappingAnalyser::class);
    $profile = $analyser->analyse(ImportTarget::DataBreachRecord, ['Datum melding'], [['Datum melding' => '13-03-2026']]);

    expect(targetFor($profile->fields, 'Datum melding')?->dateFormat)->toBe('d-m-Y');
});

it('leaves the date format open when the values could be read two ways', function (): void {
    /** @var MappingAnalyser $analyser */
    $analyser = $this->app->get(MappingAnalyser::class);
    $profile = $analyser->analyse(ImportTarget::DataBreachRecord, ['Datum melding'], [['Datum melding' => '04-03-2026']]);

    expect(targetFor($profile->fields, 'Datum melding')?->target)->toBe('reported_at')
        ->and(targetFor($profile->fields, 'Datum melding')?->dateFormat)->toBeNull();
});

it('proposes links and lookups by their label, not only plain fields', function (): void {
    /** @var MappingAnalyser $analyser */
    $analyser = $this->app->get(MappingAnalyser::class);
    $profile = $analyser->analyse(
        ImportTarget::AvgResponsibleProcessingRecord,
        ['Verwerkers', 'Contactpersoon', 'Dienst', 'Verwerkingsdoel'],
        [['Verwerkers' => 'Firma A', 'Contactpersoon' => 'J. de Vries', 'Dienst' => 'Zorg', 'Verwerkingsdoel' => 'Behandeling']],
    );

    expect(targetFor($profile->fields, 'Verwerkers')?->relation)->toBe('processors')
        ->and(targetFor($profile->fields, 'Contactpersoon')?->relation)->toBe('contactPersons')
        ->and(targetFor($profile->fields, 'Dienst')?->target)->toBe('service')
        ->and(targetFor($profile->fields, 'Verwerkingsdoel')?->relation)->toBe('avgGoals');
});

it('never proposes a field the screen does not offer', function (): void {
    /** @var MappingAnalyser $analyser */
    $analyser = $this->app->get(MappingAnalyser::class);
    // "public_from" is filled in by the application; its label used to attract
    // any column that looked like a date.
    $profile = $analyser->analyse(
        ImportTarget::AvgResponsibleProcessingRecord,
        ['Laatste controle'],
        [['Laatste controle' => '2026-03-12']],
    );

    expect(targetFor($profile->fields, 'Laatste controle'))->toBeNull();
});

it('only suggests, never fills in, a heading that is a fragment of a label', function (): void {
    /** @var MappingAnalyser $analyser */
    $analyser = $this->app->get(MappingAnalyser::class);
    // "Omschrijving" occurs in several labels ("Omschrijving beveiligingsmaatregelen",
    // "Toelichting doorgifte" via synonyms); in this template it was the name.
    $profile = $analyser->analyse(
        ImportTarget::AvgResponsibleProcessingRecord,
        ['Omschrijving'],
        [['Omschrijving' => 'Elektronisch patiëntendossier']],
    );
    $field = targetFor($profile->fields, 'Omschrijving');

    expect($field === null || $field->confidence === MappingConfidence::Label)->toBeTrue();
});

it('does not let yes/no values make a loose heading confident', function (): void {
    /** @var MappingAnalyser $analyser */
    $analyser = $this->app->get(MappingAnalyser::class);
    $profile = $analyser->analyse(
        ImportTarget::AvgResponsibleProcessingRecord,
        ['Verwerkers overeenkomst?'],
        [['Verwerkers overeenkomst?' => 'JA'], ['Verwerkers overeenkomst?' => 'NEE']],
    );

    expect(targetFor($profile->fields, 'Verwerkers overeenkomst?')?->confidence)->toBe(MappingConfidence::Label);
});

it('does not put a column of reference numbers in a yes/no field, however it is headed', function (): void {
    /** @var MappingAnalyser $analyser */
    $analyser = $this->app->get(MappingAnalyser::class);
    $rows = [['Gemeld aan AP' => 'AP-nummer 2026-0031'], ['Gemeld aan AP' => 'AP-nummer 2026-0044']];
    $profile = $analyser->analyse(ImportTarget::DataBreachRecord, ['Gemeld aan AP'], $rows);

    expect(targetFor($profile->fields, 'Gemeld aan AP'))->toBeNull();
});

it('recognises a field by its fixed choices and refuses values outside them', function (): void {
    /** @var MappingAnalyser $analyser */
    $analyser = $this->app->get(MappingAnalyser::class);

    $byValues = $analyser->analyse(ImportTarget::DataBreachRecord, ['Soort'], [['Soort' => 'Voorlopig'], ['Soort' => 'Definitief']]);
    $outside = $analyser->analyse(
        ImportTarget::DataBreachRecord,
        ['Categorie'],
        [['Categorie' => 'Gegevens gedeeld met verkeerde ontvanger.']],
    );

    expect(targetFor($byValues->fields, 'Soort')?->target)->toBe('type')
        ->and(targetFor($outside->fields, 'Categorie')?->target)->not->toBe('type');
});
