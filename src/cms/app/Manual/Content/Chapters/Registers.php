<?php

declare(strict_types=1);

namespace App\Manual\Content\Chapters;

use App\Enums\Authorization\Role;
use App\Manual\Chapter;
use App\Manual\FeatureGate;
use App\Manual\Topic;

/**
 * One chapter of the manual's reference layer.
 */
final class Registers
{
    public static function chapter(): Chapter
    {
        return new Chapter(
            id: 'registers',
            title: 'Registers',
            summary: 'De verwerkingsregisters, algoritmes en datalekken.',
            topics: [
                self::verwerkingsregisters(),
                self::wpgRegister(),
                self::algoritmes(),
                self::datalekken(),
            ],
        );
    }

    private static function verwerkingsregisters(): Topic
    {
        return new Topic(
            id: 'verwerkingsregisters',
            title: 'Verwerkingsregisters',
            body: <<<'MARKDOWN'
                ![Registers](/handleiding/02_registers/01_avg-responsible-processing-records.png)

                Na het inloggen komt u in OpenVWR en ziet u links in het scherm het
                navigatiemenu. Bovenaan staan de registers: AVG Verantwoordelijke
                Verwerkingen en AVG Verwerker Verwerkingen.

                In deze registers kunnen verwerkingen toegevoegd worden. Na het klikken op een
                register verschijnt er een overzicht van de verwerkingen in het register met
                de volgende kolommen waarop u de tabel kunt sorteren:

                - *Nummer verwerking*: het unieke nummer van de verwerking;
                - *Naam verwerking*: de naam van de verwerking;
                - *Status*: de status van de versies van de verwerking;
                - *Periodieke review*: de datum waarop de verwerking opnieuw een review moet
                  ontvangen.

                Als u op een verwerking klikt, of op de knop "Verwerking aanmaken", komt u op
                het bijbehorende invoerformulier, ook wel de detailpagina genoemd.

                ![Verwerking detailpagina](/handleiding/02_registers/02_avg-responsible-processing-records_edit.png)

                Op deze pagina zijn alle gegevens van een verwerking in te voeren en kunnen
                relaties gelegd worden tussen andere verwerkingen. Aan de rechterkant is een
                navigatiemenu voor de verschillende domeinen van gegevensinvoer.

                > **Hint**: U kunt een verwerking opslaan zonder dat alle informatie is
                > ingevoerd. Elke keer dat u opslaat legt het portaal de verwerking vast als
                > versie met de status "Concept"; slaat u opnieuw op, dan wordt diezelfde
                > conceptversie bijgewerkt. Verplichte velden worden pas gecontroleerd op het
                > moment dat u op "Start vaststellen" drukt; ontbreekt er dan nog iets, dan
                > ziet u dat bij het veld zelf en in de stappenlijst. Als een verwerking
                > eenmaal klaar is voor het goedkeuringsproces drukt u op "Start
                > vaststellen", waarna deze via dat proces vastgesteld kan worden,
                > afhankelijk van de inrichting bij uw organisatie eventueel nadat
                > Mandaathouders akkoord hebben gegeven. Voor meer informatie: zie
                > [Versie indienen](#versie-indienen).

                Eenmaal opgeslagen in het systeem zullen relaties met andere entiteiten
                zichtbaar zijn in de tabellen onderaan in het scherm. Hiermee kunt u snel
                navigeren naar de gerelateerde entiteiten.

                > **Hint**: Het is mogelijk om verwerkingen te dupliceren met de knop
                > rechtsbovenin: er wordt een nieuwe verwerking aangemaakt met precies
                > dezelfde waardes voor alle velden. Dit maakt het makkelijk om meerdere
                > verwerkingen in te voeren met grotendeels dezelfde eigenschappen: u hoeft
                > alleen maar de velden te wijzigen waar de verwerkingen niet overeenkomen.
                MARKDOWN,
            roles: [
                Role::INPUT_PROCESSOR,
                Role::CHIEF_PRIVACY_OFFICER,
                Role::PRIVACY_OFFICER,
                Role::COUNSELOR,
                Role::DATA_PROTECTION_OFFICIAL,
                Role::MANDATE_HOLDER,
            ],
            availability: 'Invoerder, (Chief) Privacy Officer, Raadpleger (lezen), '
                . 'Functionaris Gegevensbescherming (lezen), Mandaathouder (lezen)',
        );
    }

    private static function wpgRegister(): Topic
    {
        return new Topic(
            id: 'wpg-register',
            title: 'WPG Verantwoordelijke Verwerkingen',
            body: <<<'MARKDOWN'
                Naast de AVG-registers kent OpenVWR het register WPG Verantwoordelijke
                Verwerkingen, voor verwerkingen die onder de Wet politiegegevens vallen.

                Het register werkt op identieke wijze als de AVG-verwerkingsregisters: u ziet
                dezelfde kolommen in het overzicht, en op de detailpagina legt u de gegevens
                van de verwerking op dezelfde manier vast. Ook het goedkeuringsproces, labels,
                import en export werken er hetzelfde.

                Bij het WPG-register hoort een eigen opzoeklijst, die u beheert zoals de
                andere opzoeklijsten.
                MARKDOWN,
            roles: [
                Role::INPUT_PROCESSOR,
                Role::CHIEF_PRIVACY_OFFICER,
                Role::PRIVACY_OFFICER,
                Role::COUNSELOR,
                Role::DATA_PROTECTION_OFFICIAL,
                Role::MANDATE_HOLDER,
            ],
            gate: FeatureGate::WPG,
            availability: 'Invoerder, (Chief) Privacy Officer, Raadpleger (lezen), '
                . 'Functionaris Gegevensbescherming (lezen), Mandaathouder (lezen)',
        );
    }

    private static function algoritmes(): Topic
    {
        return new Topic(
            id: 'algoritmes',
            title: 'Algoritmes',
            body: <<<'MARKDOWN'
                Het algoritmeregister werkt op identieke wijze als de verwerkingregisters. In
                de overzichtstabel zijn dezelfde kolommen zichtbaar omdat ze deze
                eigenschappen delen met de verwerkingen.
                MARKDOWN,
            roles: [
                Role::INPUT_PROCESSOR,
                Role::CHIEF_PRIVACY_OFFICER,
                Role::PRIVACY_OFFICER,
                Role::COUNSELOR,
                Role::DATA_PROTECTION_OFFICIAL,
                Role::MANDATE_HOLDER,
            ],
            availability: 'Invoerder, (Chief) Privacy Officer, Raadpleger (lezen), '
                . 'Functionaris Gegevensbescherming (lezen), Mandaathouder (lezen)',
        );
    }

    private static function datalekken(): Topic
    {
        return new Topic(
            id: 'datalekken',
            title: 'Datalekken',
            body: <<<'MARKDOWN'
                In dit register kunnen datalekken toegevoegd worden. Na het klikken op het
                datalekregister verschijnt er een overzicht van alle datalekken in het
                register met de volgende kolommen waarop u de tabel kunt sorteren:

                - *Nummer datalek*: het unieke nummer van de datalek;
                - *Naam datalek*: de naam van de datalek;
                - *Datum melding*: de datum van melding;
                - *Gemeld aan de autoriteit persoonsgegevens (AP)*: een indicatie of het lek
                  al dan niet gemeld is bij de AP.

                Als u op een datalek klikt, of op de knop "Datalek aanmaken", komt u op het
                bijbehorende invoerformulier, ook wel de detailpagina genoemd.

                > **Hint**: U kunt op de detailpagina aangeven of een datalek gemeld is bij de
                > autoriteit persoonsgegevens (AP). Indien u aangeeft dat u dat gedaan heeft
                > en de datalek opslaat, dan krijgen de Chief Privacy Officer(s) en
                > Mandaathouder(s) van de organisatie automatisch een email met daarin een
                > link naar deze datalek in het portaal.
                MARKDOWN,
            roles: [
                Role::INPUT_PROCESSOR_DATABREACH,
                Role::CHIEF_PRIVACY_OFFICER,
                Role::PRIVACY_OFFICER,
                Role::COUNSELOR,
                Role::DATA_PROTECTION_OFFICIAL,
            ],
            availability: 'Invoerder Datalekken, (Chief) Privacy Officer, Raadpleger (lezen), '
                . 'Functionaris Gegevensbescherming (lezen)',
        );
    }
}
