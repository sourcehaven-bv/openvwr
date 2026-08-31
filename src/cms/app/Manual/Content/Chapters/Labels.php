<?php

declare(strict_types=1);

namespace App\Manual\Content\Chapters;

use App\Enums\Authorization\Role;
use App\Manual\Chapter;
use App\Manual\Topic;

/**
 * One chapter of the manual's reference layer.
 */
final class Labels
{
    public static function chapter(): Chapter
    {
        return new Chapter(
            id: 'labels',
            title: 'Labels',
            summary: 'De registratie indelen naar afdeling, locatie of werkterrein.',
            topics: [
                self::waaromLabels(),
                self::labelsToekennen(),
                self::filterenOpLabels(),
                self::labelsBeheren(),
            ],
        );
    }

    private static function waaromLabels(): Topic
    {
        return new Topic(
            id: 'waarom-labels',
            title: 'Waarom labels',
            body: <<<'MARKDOWN'
                Met labels richt u de registratie in op de manier waarop uw organisatie werkt.
                Naast de inhoudelijke vastlegging van iedere verwerking legt u met een label
                vast wáár die verwerking thuishoort: bij welke afdeling, op welke locatie,
                binnen welk werkterrein. Zo vindt u alles in het register snel terug op basis
                van uw eigen organisatiekenmerken, of op een tijdelijk criterium zoals een
                lopend project of een audit.

                Een label is een trefwoord dat u zelf bedenkt en toekent aan onderdelen van de
                registratie: aan verwerkingen, maar net zo goed aan algoritmes, datalekken,
                systemen, verwerkers, contactpersonen en documenten. U bepaalt zelf welke
                labels er zijn en wat ze betekenen, en u kunt ze op elk moment uitbreiden als
                uw organisatie verandert.

                Labels geven u daarmee:

                - *een indeling op maat*: u ordent de registratie naar de indeling die binnen
                  uw organisatie gangbaar is. Vaak is dat de afdeling - het RIVM gebruikt
                  bijvoorbeeld "Infectiebestrijding" of "Milieu & Veiligheid", een ziekenhuis
                  eerder "Oncologie" of "HR", een gemeente "Burgerzaken" of "Sociaal Domein".
                  Ook de locatie ("Hoftoren", "Terminal Noord", "Locatie Bilthoven") en het
                  domein waarop het werk plaatsvindt ("Administratie", "Onderzoek",
                  "Beveiliging") zijn veelgebruikte indelingen;
                - *snel overzicht*: één label brengt in één handeling alles bij elkaar wat bij
                  bijvoorbeeld een afdeling hoort, ook als die onderdelen inhoudelijk sterk
                  verschillen. Handig bij een periodieke review, omdat u zo de afdeling kunt
                  aanspreken die de verwerking daadwerkelijk uitvoert;
                - *gerichte rapportages*: u filtert op een label en exporteert het resultaat,
                  en heeft zo direct een overzicht per afdeling of vestiging;
                - *samenhang tussen de registers*: hetzelfde label kan op een verwerking staan
                  én op het systeem en het document eromheen, zodat u in één beeld ziet wat er
                  bij een afdeling hoort. Zo vindt u ook een systeem dat maar op één locatie
                  draait, of de contactpersoon van die vestiging, meteen terug;
                - *ruimte voor tijdelijke indelingen*: een label hoeft niet blijvend te zijn.
                  Met bijvoorbeeld "Herziening 2026" zet u bij elkaar wat in een ronde moet
                  worden nagelopen; is die ronde klaar, dan verwijdert u het label weer.

                Een onderdeel kan meerdere labels tegelijk hebben en een label kan aan
                onbeperkt veel onderdelen worden toegekend. De indelingen sluiten elkaar dus
                niet uit: u kunt dezelfde verwerking tegelijk op afdeling, locatie én
                werkterrein terugvinden.

                > **Let op**: Labels horen bij één organisatie. Labels die in de ene
                > organisatie zijn aangemaakt, zijn niet zichtbaar of bruikbaar in een andere.

                > **Hint**: Spreek binnen de organisatie af welke indelingen u aanhoudt en hoe
                > u ze schrijft: "HR" en "hr" zijn twee verschillende labels.
                MARKDOWN,
        );
    }

    private static function labelsToekennen(): Topic
    {
        return new Topic(
            id: 'labels-toekennen',
            title: 'Labels toekennen',
            body: <<<'MARKDOWN'
                ![Labels op de detailpagina van een verwerking](/handleiding/06_labels/02_avg-responsible-processing-records_edit_labels.png)

                Op de detailpagina van een verwerking staat het veld "Labels" in het eerste
                blok, onder de naam van de verwerking. Klik op het veld om een lijst met
                bestaande labels te openen, of typ om te zoeken. Klik op het kruisje achter
                een label om het van de verwerking te verwijderen. Vergeet niet de verwerking
                op te slaan.

                Een (Chief) Privacy Officer ziet naast het veld ook een knop "+", waarmee een
                nieuw label kan worden aangemaakt zonder de verwerking te verlaten. Het label
                is daarna ook voor andere verwerkingen beschikbaar. Een Invoerder kan
                bestaande labels wel toekennen en weghalen, maar geen nieuwe labels aanmaken:
                die knop is voor deze rol niet zichtbaar.

                Labels zijn beschikbaar bij alle registers en bij de onderdelen onder
                "Beheer":

                - de verwerkingsregisters;
                - Algoritmes en Datalekken;
                - Verwerkingsverantwoordelijken, Verwerkers, Ontvangers,
                  Systemen/Applicaties, Contactpersonen en Documenten.

                Het veld werkt overal hetzelfde. Zo kunt u dezelfde indeling - bijvoorbeeld
                een afdeling of een locatie - door de hele registratie heen doorvoeren, en
                niet alleen bij de verwerkingen.

                ![Hetzelfde labelveld bij Systemen/Applicaties](/handleiding/06_labels/04_systems_labels.png)

                > **Hint**: Gebruikt u hetzelfde label bij een verwerking én bij het systeem
                > waarin die verwerking plaatsvindt, dan ziet u op de detailpagina van dat
                > label beide onderdelen bij elkaar staan. Dat maakt het makkelijk om te
                > overzien wat er allemaal bij een afdeling of locatie hoort.
                MARKDOWN,
            roles: [
                Role::INPUT_PROCESSOR,
                Role::INPUT_PROCESSOR_DATABREACH,
                Role::CHIEF_PRIVACY_OFFICER,
                Role::PRIVACY_OFFICER,
            ],
            availability: '(Chief) Privacy Officer (beheren), Invoerder en Invoerder '
                . 'Datalekken (toekennen), Raadpleger (lezen), Functionaris '
                . 'Gegevensbescherming (lezen), Mandaathouder (lezen)',
        );
    }

    private static function filterenOpLabels(): Topic
    {
        return new Topic(
            id: 'filteren-op-labels',
            title: 'Filteren op labels',
            body: <<<'MARKDOWN'
                ![Filteren op labels in het register](/handleiding/06_labels/03_avg-responsible-processing-records_filter_labels.png)

                In elk overzicht met labels zit rechtsboven de tabel een filterknop. Onder
                "Labels" selecteert u een of meer labels, waarna de tabel alleen nog de regels
                met die labels toont. Met "Resetten" haalt u het filter weer weg. Het filter
                werkt in alle overzichten die labels kennen, dus ook bij bijvoorbeeld
                Systemen/Applicaties of Documenten.

                > **Hint**: De export volgt de filters van de tabel. Filtert u eerst op een
                > label en exporteert u daarna, dan bevat het bestand alleen de regels van dat
                > label. De labels zelf staan als kolom "Labels" in de export. Zo maakt u
                > bijvoorbeeld een overzicht per afdeling. Voor meer informatie over
                > exporteren: zie [Export](#export).
                MARKDOWN,
        );
    }

    private static function labelsBeheren(): Topic
    {
        return new Topic(
            id: 'labels-beheren',
            title: 'Labels beheren',
            body: <<<'MARKDOWN'
                ![Labeloverzicht](/handleiding/06_labels/01_tags.png)

                In het navigatiemenu staat onder "Beheer" het onderdeel "Labels". Hier ziet u
                alle labels van de organisatie. Met de knop "Label aanmaken" rechtsboven
                voegt u een label toe; met het potloodje achter een regel wijzigt u de naam of
                de kleur van een bestaand label.

                Klik op een label om te zien waar het overal aan is toegekend. Per soort
                onderdeel is er een tabel - verwerkingen, algoritmes, datalekken, systemen,
                verwerkers, contactpersonen, documenten enzovoort - zodat u direct kunt
                doorklikken.

                ### De kleur van een label

                Elk label heeft een kleur, zodat u in een volle tabel in één oogopslag ziet
                welk label waar staat. Een nieuw label krijgt automatisch een kleur die binnen
                uw organisatie nog niet of het minst in gebruik is, zodat de kleuren vanzelf
                gespreid blijven. U hoeft er dus niets voor te doen.

                Wilt u zelf een kleur kiezen - bijvoorbeeld groen voor "Akkoord" of een vaste
                kleur per afdeling - dan doet u dat in het veld "Kleur" op het scherm van het
                label. Er zijn tien kleuren. Rood zit er bewust niet bij: die kleur is in de
                applicatie voorbehouden aan waarschuwingen en aan statussen, en een label mag
                daar niet mee worden verward.

                > **Let op**: De kleur is een hulpmiddel, geen betekenisdrager. De naam van
                > het label staat er altijd bij, ook voor wie kleuren niet of anders
                > waarneemt. Laat de betekenis van een label dus nooit alleen van de kleur
                > afhangen.

                > **Let op**: Het verwijderen van een label verwijdert het overal: de
                > onderdelen zelf blijven bestaan, maar het label is er overal af. Wilt u een
                > label maar bij één onderdeel weghalen, doe dat dan via het veld "Labels" op
                > de detailpagina van dat onderdeel.

                Het wijzigen van de naam van een label werkt wél overal door: de onderdelen
                houden het label, alleen de naam verandert. Dat is de aangewezen manier om een
                schrijfwijze recht te trekken zonder de koppelingen kwijt te raken.
                MARKDOWN,
            roles: [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
            availability: '(Chief) Privacy Officer',
        );
    }
}
