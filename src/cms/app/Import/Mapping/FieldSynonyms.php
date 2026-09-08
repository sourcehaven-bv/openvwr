<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use Illuminate\Support\Str;

use function array_key_exists;
use function ctype_digit;
use function explode;
use function implode;
use function preg_replace;
use function sort;
use function trim;

/**
 * Words that mean the same thing across source systems.
 *
 * Edit distance alone cannot see that "melding" and "rapportage" are the same
 * concept, or that "soort" and "type" are; this closes that gap without
 * resorting to a wider (and more error-prone) fuzzy match.
 */
class FieldSynonyms
{
    /**
     * Each group maps onto a single canonical word. Order within a group does
     * not matter; the first entry is the canonical form.
     */
    private const GROUPS = [
        // --- Soort / classificatie ---
        ['type', 'soort', 'categorie', 'classificatie', 'klasse', 'aard', 'kind'],
        ['status', 'stand', 'voortgang', 'fase'],

        // --- Aanduiding / titel ---
        ['naam', 'titel', 'onderwerp', 'aanduiding', 'benaming', 'kop'],
        ['nummer', 'nr', 'kenmerk', 'referentie', 'id', 'code', 'volgnummer', 'dossiernummer', 'zaaknummer'],

        // --- Melden ---
        ['gemeld', 'melding', 'gemeldt', 'doorgegeven', 'gerapporteerd', 'aangemeld', 'kennisgeving'],
        ['melder', 'indiener', 'aangever', 'rapporteur'],
        ['rapportage', 'registratie', 'notificatie', 'verslag'],

        // --- Tijd ---
        ['datum', 'dat', 'dd', 'dag', 'moment', 'tijdstip', 'wanneer'],
        ['ontdekking', 'constatering', 'signalering', 'ontdekt', 'geconstateerd', 'bekend'],
        ['start', 'begin', 'aanvang', 'startdatum', 'vanaf'],
        ['eind', 'einde', 'afronding', 'afsluiting', 'afgerond', 'afgesloten', 'tot'],

        // --- Beschrijving ---
        ['samenvatting', 'toelichting', 'omschrijving', 'beschrijving', 'details', 'uitleg', 'context', 'situatie'],
        ['opmerking', 'opmerkingen', 'notitie', 'notities', 'aantekening', 'aantekeningen', 'commentaar', 'tekst'],

        // --- Personen ---
        ['betrokkene', 'client', 'patient', 'clint', 'patint', 'bewoner', 'deelnemer'],
        ['medewerker', 'personeel', 'werknemer', 'collega'],
        ['persoonsgegevens', 'persoonsgegeven', 'gegevens', 'data', 'informatie'],

        // --- Gevolg / opvolging ---
        ['maatregel', 'maatregelen', 'actie', 'acties', 'vervolgactie', 'vervolgacties', 'oplossing', 'herstel', 'genomen'],
        ['risico', 'impact', 'ernst', 'gevolg', 'gevolgen', 'inschatting'],
        ['oorzaak', 'reden', 'aanleiding'],

        // --- Gebeurtenis ---
        ['incident', 'gebeurtenis', 'voorval', 'calamiteit', 'inbreuk', 'lek', 'datalek'],

        // --- Organisatie ---
        ['afdeling', 'eenheid', 'team', 'locatie', 'organisatieonderdeel'],
        ['verwerker', 'leverancier', 'partij', 'dienstverlener', 'bewerker'],
        ['systeem', 'applicatie', 'programma', 'software', 'pakket'],

        // --- Instanties ---
        ['ap', 'autoriteit', 'autoriteitpersoonsgegevens', 'toezichthouder'],
        ['fg', 'functionaris', 'functionarisgegevensbescherming', 'privacyofficer'],

        // --- Communicatie ---
        ['communicatiemiddel', 'kanaal', 'wijze', 'medium'],
        ['bijzondere', 'gevoelige', 'speciale'],
        // --- Register-begrippen ---
        ['doel', 'doelen', 'doeleinde', 'doeleinden', 'verwerkingsdoel', 'verwerkingsdoelen', 'purpose'],
        ['grondslag', 'grondslagen', 'rechtsgrond', 'rechtsgronden', 'wettelijkegrondslag'],
        ['bewaartermijn', 'bewaartermijnen', 'termijn', 'retentie', 'bewaarduur'],
        ['contactpersoon', 'contactpersonen', 'contact', 'aanspreekpunt', 'eigenaar', 'proceseigenaar'],
        ['ontvanger', 'ontvangers', 'afnemer', 'afnemers'],
        ['dpia', 'pia', 'geb', 'gegevensbeschermingseffectbeoordeling'],
        ['algoritme', 'algoritmes', 'algoritmen', 'ai', 'kunstmatige', 'intelligentie'],
        // Not 'verwerkingsverantwoordelijke': that is the legal entity, which
        // OpenVWR keeps at organisation level, not a responsible unit.
        ['verantwoordelijke', 'verantwoordelijken'],
        ['eer', 'eu', 'europa', 'buitenland', 'doorgifte'],
    ];

    /** @var array<string, string>|null */
    private static ?array $lookup = null;

    /**
     * Reduces a heading to comparable words: lowercased, punctuation removed and
     * every known synonym replaced by its canonical form.
     */
    public function canonicalise(string $value): string
    {
        // "RasOfEtniciteit" and "Omschrijving23" are one word to a computer and
        // several to a person; the capitals and digits mark the seams, so they
        // are cut before lowercasing hides them.
        $value = preg_replace('/(\p{Ll})(\p{Lu})/u', '$1 $2', trim($value)) ?? $value;
        $value = preg_replace('/(\p{L})(\p{N})|(\p{N})(\p{L})/u', '$1$3 $2$4', $value) ?? $value;
        $value = Str::lower($value);
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?? $value;
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $lookup = self::lookup();
        $words = [];

        foreach (explode(' ', $value) as $word) {
            // A bare number is a sequence number ("Doel 2"), not a meaning.
            if (ctype_digit($word)) {
                continue;
            }

            $words[] = $lookup[$word] ?? $word;
        }

        sort($words);

        return implode(' ', $words);
    }

    /**
     * @return array<string, string>
     */
    private static function lookup(): array
    {
        if (self::$lookup !== null) {
            return self::$lookup;
        }

        $lookup = [];
        foreach (self::GROUPS as $group) {
            $canonical = $group[0];

            foreach ($group as $word) {
                if (!array_key_exists($word, $lookup)) {
                    $lookup[$word] = $canonical;
                }
            }
        }

        return self::$lookup = $lookup;
    }
}
